package com.software.store.data.repository;

import com.software.store.data.model.Advertisement;
import com.software.store.data.model.ApiResponse;
import com.software.store.data.model.Category;
import com.software.store.data.model.Config;
import com.software.store.data.model.Notice;
import com.software.store.data.model.PageData;
import com.software.store.data.model.Toolbar;
import com.software.store.data.remote.ApiService;
import com.software.store.data.remote.RetrofitClient;

import java.util.List;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class HomeRepository {

    private static HomeRepository instance;
    private final ApiService apiService;

    public interface Callback<T> {
        void onSuccess(T result);
        void onError(String message);
    }

    private HomeRepository() {
        apiService = RetrofitClient.getInstance().getApiService();
    }

    public static synchronized HomeRepository getInstance() {
        if (instance == null) {
            instance = new HomeRepository();
        }
        return instance;
    }

    public void getHomeData(Callback<PageData> callback) {
        apiService.getHomeData().enqueue(new Callback<ApiResponse<PageData>>() {
            @Override
            public void onResponse(Call<ApiResponse<PageData>> call, Response<ApiResponse<PageData>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    callback.onSuccess(response.body().getData());
                } else {
                    callback.onError(response.body() != null ? response.body().getMessage() : "获取首页数据失败");
                }
            }

            @Override
            public void onFailure(Call<ApiResponse<PageData>> call, Throwable t) {
                callback.onError(t.getMessage());
            }
        });
    }

    public void getToolbar(Callback<List<Toolbar>> callback) {
        apiService.getToolbar().enqueue(new Callback<ApiResponse<List<Toolbar>>>() {
            @Override
            public void onResponse(Call<ApiResponse<List<Toolbar>>> call, Response<ApiResponse<List<Toolbar>>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    callback.onSuccess(response.body().getData());
                } else {
                    callback.onError(response.body() != null ? response.body().getMessage() : "获取工具栏失败");
                }
            }

            @Override
            public void onFailure(Call<ApiResponse<List<Toolbar>>> call, Throwable t) {
                callback.onError(t.getMessage());
            }
        });
    }

    public void getCategories(Callback<List<Category>> callback) {
        apiService.getCategories().enqueue(new Callback<ApiResponse<List<Category>>>() {
            @Override
            public void onResponse(Call<ApiResponse<List<Category>>> call, Response<ApiResponse<List<Category>>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    callback.onSuccess(response.body().getData());
                } else {
                    callback.onError(response.body() != null ? response.body().getMessage() : "获取分类失败");
                }
            }

            @Override
            public void onFailure(Call<ApiResponse<List<Category>>> call, Throwable t) {
                callback.onError(t.getMessage());
            }
        });
    }

    public void getSplashAd(Callback<Advertisement> callback) {
        apiService.getSplashAd().enqueue(new Callback<ApiResponse<Advertisement>>() {
            @Override
            public void onResponse(Call<ApiResponse<Advertisement>> call, Response<ApiResponse<Advertisement>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    callback.onSuccess(response.body().getData());
                } else {
                    callback.onError(response.body() != null ? response.body().getMessage() : "获取开屏广告失败");
                }
            }

            @Override
            public void onFailure(Call<ApiResponse<Advertisement>> call, Throwable t) {
                callback.onError(t.getMessage());
            }
        });
    }

    public void getConfig(Callback<Config> callback) {
        apiService.getConfig().enqueue(new Callback<ApiResponse<Config>>() {
            @Override
            public void onResponse(Call<ApiResponse<Config>> call, Response<ApiResponse<Config>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    callback.onSuccess(response.body().getData());
                } else {
                    callback.onError(response.body() != null ? response.body().getMessage() : "获取配置失败");
                }
            }

            @Override
            public void onFailure(Call<ApiResponse<Config>> call, Throwable t) {
                callback.onError(t.getMessage());
            }
        });
    }

    public void getPages(Callback<List<Notice>> callback) {
        apiService.getPages().enqueue(new Callback<ApiResponse<List<Notice>>>() {
            @Override
            public void onResponse(Call<ApiResponse<List<Notice>>> call, Response<ApiResponse<List<Notice>>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    callback.onSuccess(response.body().getData());
                } else {
                    callback.onError(response.body() != null ? response.body().getMessage() : "获取页面失败");
                }
            }

            @Override
            public void onFailure(Call<ApiResponse<List<Notice>>> call, Throwable t) {
                callback.onError(t.getMessage());
            }
        });
    }
}