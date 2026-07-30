package com.software.store;

import android.app.Application;

import com.software.store.data.remote.RetrofitClient;
import com.software.store.util.SecurityUtils;
import com.software.store.util.ToastUtils;

/**
 * Application 应用入口类
 * 负责全局初始化：Toast 工具、Retrofit 客户端等
 */
public class App extends Application {

    private static App instance;

    @Override
    public void onCreate() {
        super.onCreate();
        instance = this;
        // 安全检测：防破解，检测到风险时退出应用
        SecurityUtils.performSecurityCheck(this);
        // 初始化 Toast 工具
        ToastUtils.init(this);
        // 初始化 Retrofit 客户端
        RetrofitClient.getInstance().init();
    }

    /**
     * 获取全局 Application 实例
     */
    public static App getInstance() {
        return instance;
    }
}
