package com.software.store.data.remote;

import java.util.concurrent.TimeUnit;

import okhttp3.OkHttpClient;
import okhttp3.logging.HttpLoggingInterceptor;
import retrofit2.Retrofit;
import retrofit2.converter.gson.GsonConverterFactory;

/**
 * Retrofit 客户端单例
 * 配置 OkHttp 拦截器（日志拦截器、Token 拦截器）并提供 ApiService
 */
public class RetrofitClient {

    /** 服务器基础地址 */
    private static final String BASE_URL = "https://api.softwarestore.com/";

    /** 连接超时时间（秒） */
    private static final int CONNECT_TIMEOUT = 15;

    /** 读取超时时间（秒） */
    private static final int READ_TIMEOUT = 15;

    /** 写入超时时间（秒） */
    private static final int WRITE_TIMEOUT = 15;

    private static volatile RetrofitClient instance;

    private Retrofit retrofit;
    private ApiService apiService;

    /** 用户登录令牌（登录后赋值） */
    private String token;

    private RetrofitClient() {
    }

    /**
     * 获取单例实例
     */
    public static RetrofitClient getInstance() {
        if (instance == null) {
            synchronized (RetrofitClient.class) {
                if (instance == null) {
                    instance = new RetrofitClient();
                }
            }
        }
        return instance;
    }

    /**
     * 初始化 Retrofit（在 Application 中调用）。
     * 注：本方法必须保证"永不抛出异常"——否则 Application.onCreate 会在启动早期
     * 直接崩溃，表现为"闪一下就没了"。任何失败都只打 Log，不抛。
     */
    public void init() {
        try {
            initInternal();
        } catch (Throwable t) {
            android.util.Log.e("RetrofitClient", "init-failed (network client disabled): " + t.getMessage());
            retrofit = null;
            apiService = null;
        }
    }

    private void initInternal() {
        // 日志拦截器：打印请求与响应
        HttpLoggingInterceptor loggingInterceptor = new HttpLoggingInterceptor(message -> {
            // 实际项目中可替换为日志框架
            try {
                android.util.Log.d("OkHttp", message);
            } catch (Throwable ignored) {
            }
        });
        try {
            loggingInterceptor.setLevel(HttpLoggingInterceptor.Level.BODY);
        } catch (Throwable ignored) {
        }

        // Token 拦截器：在请求头中追加 Authorization
        okhttp3.Interceptor tokenInterceptor = chain -> {
            okhttp3.Request original = chain.request();
            okhttp3.Request.Builder builder = original.newBuilder()
                    .header("Content-Type", "application/json");
            if (token != null && !token.isEmpty()) {
                builder.header("Authorization", "Bearer " + token);
            }
            return chain.proceed(builder.build());
        };

        // 请求签名拦截器：防止API被抓包篡改
        okhttp3.Interceptor signInterceptor = chain -> {
            okhttp3.Request original = chain.request();
            long timestamp = System.currentTimeMillis() / 1000;
            String sign = generateSignature(original.url().toString(), timestamp);

            okhttp3.Request signedRequest = original.newBuilder()
                    .header("X-Timestamp", String.valueOf(timestamp))
                    .header("X-Signature", sign)
                    .header("X-App-Version", String.valueOf(android.os.Build.VERSION.SDK_INT))
                    .build();
            return chain.proceed(signedRequest);
        };

        OkHttpClient okHttpClient = new OkHttpClient.Builder()
                .connectTimeout(CONNECT_TIMEOUT, TimeUnit.SECONDS)
                .readTimeout(READ_TIMEOUT, TimeUnit.SECONDS)
                .writeTimeout(WRITE_TIMEOUT, TimeUnit.SECONDS)
                .addInterceptor(tokenInterceptor)
                .addInterceptor(signInterceptor)
                .addInterceptor(loggingInterceptor)
                .retryOnConnectionFailure(true)
                // 证书锁定（Certificate Pinning）：防止中间人攻击。
                // 启用时取消下方注释并替换为服务器证书的 SHA-256 公钥哈希：
                // .certificatePinner(new okhttp3.CertificatePinner.Builder()
                //         .add("api.softwarestore.com",
                //                 "sha256/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=")
                //         .build())
                .build();

        retrofit = new Retrofit.Builder()
                .baseUrl(BASE_URL)
                .client(okHttpClient)
                .addConverterFactory(GsonConverterFactory.create())
                .build();

        apiService = retrofit.create(ApiService.class);
    }

    /**
     * 获取 ApiService 实例
     */
    public ApiService getApiService() {
        if (apiService == null) {
            try {
                init();
            } catch (Throwable ignored) {
            }
        }
        return apiService;
    }

    /**
     * 获取 Retrofit 实例
     */
    public Retrofit getRetrofit() {
        return retrofit;
    }

    /**
     * 保存登录令牌
     *
     * @param token 令牌字符串
     */
    public void setToken(String token) {
        this.token = token;
    }

    /**
     * 获取当前登录令牌
     */
    public String getToken() {
        return token;
    }

    /**
     * 清除登录令牌（退出登录时调用）
     */
    public void clearToken() {
        this.token = null;
    }

    /**
     * 生成请求签名
     * 使用HMAC-SHA256对URL和时间戳进行签名
     *
     * @param url       请求URL
     * @param timestamp 时间戳（秒）
     * @return 签名摘要的十六进制字符串
     */
    private String generateSignature(String url, long timestamp) {
        try {
            String data = url + timestamp;
            javax.crypto.Mac mac = javax.crypto.Mac.getInstance("HmacSHA256");
            // 密钥应从Native层获取或动态生成，此处使用固定值作为示例
            String secret = "sw_store_secret_key_2026";
            mac.init(new javax.crypto.spec.SecretKeySpec(secret.getBytes(), "HmacSHA256"));
            byte[] hash = mac.doFinal(data.getBytes());
            StringBuilder sb = new StringBuilder();
            for (byte b : hash) {
                sb.append(String.format("%02x", b));
            }
            return sb.toString();
        } catch (Exception e) {
            return "";
        }
    }
}
