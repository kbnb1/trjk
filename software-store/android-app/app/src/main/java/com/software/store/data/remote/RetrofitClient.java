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
     * 初始化 Retrofit（在 Application 中调用）
     */
    public void init() {
        // 日志拦截器：打印请求与响应
        HttpLoggingInterceptor loggingInterceptor = new HttpLoggingInterceptor(message -> {
            // 实际项目中可替换为日志框架
            android.util.Log.d("OkHttp", message);
        });
        loggingInterceptor.setLevel(HttpLoggingInterceptor.Level.BODY);

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

        OkHttpClient okHttpClient = new OkHttpClient.Builder()
                .connectTimeout(CONNECT_TIMEOUT, TimeUnit.SECONDS)
                .readTimeout(READ_TIMEOUT, TimeUnit.SECONDS)
                .writeTimeout(WRITE_TIMEOUT, TimeUnit.SECONDS)
                .addInterceptor(tokenInterceptor)
                .addInterceptor(loggingInterceptor)
                .retryOnConnectionFailure(true)
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
            init();
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
}
