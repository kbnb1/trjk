package com.software.store.data.repository;

import com.software.store.data.model.ApiResponse;
import com.software.store.data.model.DownloadRecord;
import com.software.store.data.model.Software;
import com.software.store.data.remote.ApiService;
import com.software.store.data.remote.RetrofitClient;

import java.util.HashMap;
import java.util.List;
import java.util.Map;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class SoftwareRepository {

    private static SoftwareRepository instance;
    private final ApiService apiService;

    public interface Callback<T> {
        void onSuccess(T result);
        void onError(String message);
    }

    private SoftwareRepository() {
        apiService = RetrofitClient.getInstance().getApiService();
    }

    public static synchronized SoftwareRepository getInstance() {
        if (instance == null) {
            instance = new SoftwareRepository();
        }
        return instance;
    }

    public void getSoftwareList(Integer categoryId, int page, int perPage, Callback<List<Software>> callback) {
        apiService.getSoftwareList(categoryId, page, perPage).enqueue(new Callback<ApiResponse<List<Software>>>() {
            @Override
            public void onResponse(Call<ApiResponse<List<Software>>> call, Response<ApiResponse<List<Software>>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    callback.onSuccess(response.body().getData());
                } else {
                    callback.onError(response.body() != null ? response.body().getMessage() : "请求失败");
                }
            }

            @Override
            public void onFailure(Call<ApiResponse<List<Software>>> call, Throwable t) {
                callback.onError(t.getMessage());
            }
        });
    }

    public void getSoftwareDetail(int id, Callback<Software> callback) {
        apiService.getSoftwareDetail(id).enqueue(new Callback<ApiResponse<Software>>() {
            @Override
            public void onResponse(Call<ApiResponse<Software>> call, Response<ApiResponse<Software>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    callback.onSuccess(response.body().getData());
                } else {
                    callback.onError(response.body() != null ? response.body().getMessage() : "请求失败");
                }
            }

            @Override
            public void onFailure(Call<ApiResponse<Software>> call, Throwable t) {
                callback.onError(t.getMessage());
            }
        });
    }

    public void getRelatedSoftware(int id, Callback<List<Software>> callback) {
        apiService.getRelatedSoftware(id).enqueue(new Callback<ApiResponse<List<Software>>>() {
            @Override
            public void onResponse(Call<ApiResponse<List<Software>>> call, Response<ApiResponse<List<Software>>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    callback.onSuccess(response.body().getData());
                } else {
                    callback.onError(response.body() != null ? response.body().getMessage() : "请求失败");
                }
            }

            @Override
            public void onFailure(Call<ApiResponse<List<Software>>> call, Throwable t) {
                callback.onError(t.getMessage());
            }
        });
    }

    public void searchSoftware(String keyword, int page, Callback<List<Software>> callback) {
        apiService.searchSoftware(keyword, page).enqueue(new Callback<ApiResponse<List<Software>>>() {
            @Override
            public void onResponse(Call<ApiResponse<List<Software>>> call, Response<ApiResponse<List<Software>>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    callback.onSuccess(response.body().getData());
                } else {
                    callback.onError(response.body() != null ? response.body().getMessage() : "请求失败");
                }
            }

            @Override
            public void onFailure(Call<ApiResponse<List<Software>>> call, Throwable t) {
                callback.onError(t.getMessage());
            }
        });
    }

    public void startDownload(int softwareId, Callback<DownloadRecord> callback) {
        Map<String, Integer> params = new HashMap<>();
        params.put("software_id", softwareId);

        apiService.startDownload(params).enqueue(new Callback<ApiResponse<DownloadRecord>>() {
            @Override
            public void onResponse(Call<ApiResponse<DownloadRecord>> call, Response<ApiResponse<DownloadRecord>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    callback.onSuccess(response.body().getData());
                } else {
                    callback.onError(response.body() != null ? response.body().getMessage() : "请求失败");
                }
            }

            @Override
            public void onFailure(Call<ApiResponse<DownloadRecord>> call, Throwable t) {
                callback.onError(t.getMessage());
            }
        });
    }

    public void getDownloads(Callback<List<DownloadRecord>> callback) {
        apiService.getDownloads().enqueue(new Callback<ApiResponse<List<DownloadRecord>>>() {
            @Override
            public void onResponse(Call<ApiResponse<List<DownloadRecord>>> call, Response<ApiResponse<List<DownloadRecord>>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    callback.onSuccess(response.body().getData());
                } else {
                    callback.onError(response.body() != null ? response.body().getMessage() : "请求失败");
                }
            }

            @Override
            public void onFailure(Call<ApiResponse<List<DownloadRecord>>> call, Throwable t) {
                callback.onError(t.getMessage());
            }
        });
    }

    public void updateDownloadProgress(int downloadId, int progress, int status, Callback<Void> callback) {
        Map<String, Integer> params = new HashMap<>();
        params.put("progress", progress);
        params.put("status", status);

        apiService.updateDownloadProgress(downloadId, params).enqueue(new Callback<ApiResponse<Void>>() {
            @Override
            public void onResponse(Call<ApiResponse<Void>> call, Response<ApiResponse<Void>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    callback.onSuccess(null);
                } else {
                    callback.onError(response.body() != null ? response.body().getMessage() : "请求失败");
                }
            }

            @Override
            public void onFailure(Call<ApiResponse<Void>> call, Throwable t) {
                callback.onError(t.getMessage());
            }
        });
    }

    public void submitFeedback(String content, String contact, String images, Callback<Void> callback) {
        Map<String, String> params = new HashMap<>();
        params.put("content", content);
        if (contact != null) {
            params.put("contact", contact);
        }
        if (images != null) {
            params.put("images", images);
        }

        apiService.submitFeedback(params).enqueue(new Callback<ApiResponse<Void>>() {
            @Override
            public void onResponse(Call<ApiResponse<Void>> call, Response<ApiResponse<Void>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    callback.onSuccess(null);
                } else {
                    callback.onError(response.body() != null ? response.body().getMessage() : "请求失败");
                }
            }

            @Override
            public void onFailure(Call<ApiResponse<Void>> call, Throwable t) {
                callback.onError(t.getMessage());
            }
        });
    }
}