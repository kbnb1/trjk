package com.software.store;

import android.app.Application;
import android.os.Handler;
import android.os.Looper;
import android.widget.Toast;

import com.software.store.data.remote.RetrofitClient;
import com.software.store.util.SecurityUtils;
import com.software.store.util.ToastUtils;

import java.lang.ref.WeakReference;

/**
 * Application 应用入口类
 * 负责全局初始化：异常捕获兜底、Toast 工具、Retrofit 客户端、安全检测
 */
public class App extends Application {

    private static App instance;

    @Override
    public void onCreate() {
        super.onCreate();
        instance = this;

        // 1) 全局未捕获异常兜底：避免"点开就闪退看不到Log"的现象
        installGlobalExceptionHandler();

        try {
            // 2) 安全检测：策略已改为"只打日志不杀进程"，保证可启动
            SecurityUtils.performSecurityCheck(this);
            // 3) Toast 工具
            ToastUtils.init(this);
            // 4) Retrofit 客户端（失败不影响启动）
            RetrofitClient.getInstance().init();
        } catch (Throwable t) {
            android.util.Log.e("App", "init-failed, but keep app alive", t);
            toastSafe("初始化异常：" + t.getMessage());
        }
    }

    /**
     * 获取全局 Application 实例
     */
    public static App getInstance() {
        return instance;
    }

    // ---------------------------------------------------
    // 全局异常捕获 —— 把未捕获异常以 Toast + Log 暴露出来，
    // 防止用户只看到"闪退"却找不到任何线索。
    // 注意：这里不吞异常，仍然回调原始处理器（方便系统/崩溃采集工具工作）。
    // ---------------------------------------------------
    private void installGlobalExceptionHandler() {
        try {
            final WeakReference<App> appRef = new WeakReference<>(this);
            final Thread.UncaughtExceptionHandler original =
                    Thread.getDefaultUncaughtExceptionHandler();

            Thread.setDefaultUncaughtExceptionHandler((t, e) -> {
                try {
                    android.util.Log.e("App",
                            "FATAL in thread=" + t.getName(), e);
                    App app = appRef.get();
                    if (app != null) {
                        app.toastSafe("启动异常：" + e.getClass().getSimpleName()
                                + " - " + e.getMessage());
                    }
                } catch (Throwable ignored) {
                }
                if (original != null) {
                    original.uncaughtException(t, e);
                }
            });
        } catch (Throwable ignored) {
        }
    }

    private void toastSafe(final String msg) {
        try {
            new Handler(Looper.getMainLooper()).post(() -> {
                try {
                    Toast.makeText(App.this, msg, Toast.LENGTH_LONG).show();
                } catch (Throwable ignored) {
                }
            });
        } catch (Throwable ignored) {
        }
    }
}
