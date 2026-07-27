package com.software.store;

import android.app.Application;

import com.software.store.data.remote.RetrofitClient;
import com.software.store.util.ToastUtils;

public class App extends Application {

    private static App instance;

    public static App getInstance() {
        return instance;
    }

    @Override
    public void onCreate() {
        super.onCreate();
        instance = this;
        ToastUtils.getInstance().init(this);
        RetrofitClient.getInstance(this);
    }

    public String getBaseUrl() {
        return BuildConfig.BASE_URL;
    }
}