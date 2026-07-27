package com.software.store.data.repository;

import android.content.Context;
import android.content.SharedPreferences;

import com.software.store.data.model.ApiResponse;
import com.software.store.data.model.Software;
import com.software.store.data.model.User;
import com.software.store.data.remote.ApiService;
import com.software.store.data.remote.RetrofitClient;

import java.util.HashMap;
import java.util.List;
import java.util.Map;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class UserRepository {

    private static UserRepository instance;
    private final ApiService apiService;
    private final Context context;

    public interface Callback<T> {
        void onSuccess(T result);
        void onError(String message);
    }

    private UserRepository(Context context) {
        this.context = context.getApplicationContext();
        apiService = RetrofitClient.getInstance(this.context).getApiService();
    }

    public static synchronized UserRepository getInstance(Context context) {
        if (instance == null) {
            instance = new UserRepository(context);
        }
        return instance;
    }

    public void login(String account, String password, Callback<User> callback) {
        Map<String, String> params = new HashMap<>();
        params.put("account", account);
        params.put("password", password);

        apiService.login(params).enqueue(new Callback<ApiResponse<User>>() {
            @Override
            public void onResponse(Call<ApiResponse<User>> call, Response<ApiResponse<User>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    User user = response.body().getData();
                    callback.onSuccess(user);
                } else {
                    callback.onError(response.body() != null ? response.body().getMessage() : "登录失败");
                }
            }

            @Override
            public void onFailure(Call<ApiResponse<User>> call, Throwable t) {
                callback.onError(t.getMessage());
            }
        });
    }

    public void register(String username, String password, String email, String phone, String code, Callback<User> callback) {
        Map<String, String> params = new HashMap<>();
        params.put("username", username);
        params.put("password", password);
        if (email != null) {
            params.put("email", email);
        }
        if (phone != null) {
            params.put("phone", phone);
        }
        if (code != null) {
            params.put("code", code);
        }

        apiService.register(params).enqueue(new Callback<ApiResponse<User>>() {
            @Override
            public void onResponse(Call<ApiResponse<User>> call, Response<ApiResponse<User>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    callback.onSuccess(response.body().getData());
                } else {
                    callback.onError(response.body() != null ? response.body().getMessage() : "注册失败");
                }
            }

            @Override
            public void onFailure(Call<ApiResponse<User>> call, Throwable t) {
                callback.onError(t.getMessage());
            }
        });
    }

    public void sendCode(String target, String type, Callback<Void> callback) {
        Map<String, String> params = new HashMap<>();
        params.put("target", target);
        params.put("type", type);

        apiService.sendCode(params).enqueue(new Callback<ApiResponse<Void>>() {
            @Override
            public void onResponse(Call<ApiResponse<Void>> call, Response<ApiResponse<Void>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    callback.onSuccess(null);
                } else {
                    callback.onError(response.body() != null ? response.body().getMessage() : "发送失败");
                }
            }

            @Override
            public void onFailure(Call<ApiResponse<Void>> call, Throwable t) {
                callback.onError(t.getMessage());
            }
        });
    }

    public void getUserInfo(Callback<User> callback) {
        apiService.getUserInfo().enqueue(new Callback<ApiResponse<User>>() {
            @Override
            public void onResponse(Call<ApiResponse<User>> call, Response<ApiResponse<User>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    callback.onSuccess(response.body().getData());
                } else {
                    callback.onError(response.body() != null ? response.body().getMessage() : "获取用户信息失败");
                }
            }

            @Override
            public void onFailure(Call<ApiResponse<User>> call, Throwable t) {
                callback.onError(t.getMessage());
            }
        });
    }

    public void updateUser(Map<String, String> params, Callback<User> callback) {
        apiService.updateUser(params).enqueue(new Callback<ApiResponse<User>>() {
            @Override
            public void onResponse(Call<ApiResponse<User>> call, Response<ApiResponse<User>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    callback.onSuccess(response.body().getData());
                } else {
                    callback.onError(response.body() != null ? response.body().getMessage() : "更新失败");
                }
            }

            @Override
            public void onFailure(Call<ApiResponse<User>> call, Throwable t) {
                callback.onError(t.getMessage());
            }
        });
    }

    public void toggleFavorite(int softwareId, Callback<Void> callback) {
        Map<String, Integer> params = new HashMap<>();
        params.put("software_id", softwareId);

        apiService.toggleFavorite(params).enqueue(new Callback<ApiResponse<Void>>() {
            @Override
            public void onResponse(Call<ApiResponse<Void>> call, Response<ApiResponse<Void>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    callback.onSuccess(null);
                } else {
                    callback.onError(response.body() != null ? response.body().getMessage() : "操作失败");
                }
            }

            @Override
            public void onFailure(Call<ApiResponse<Void>> call, Throwable t) {
                callback.onError(t.getMessage());
            }
        });
    }

    public void getFavorites(Callback<List<Software>> callback) {
        apiService.getFavorites().enqueue(new Callback<ApiResponse<List<Software>>>() {
            @Override
            public void onResponse(Call<ApiResponse<List<Software>>> call, Response<ApiResponse<List<Software>>> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    callback.onSuccess(response.body().getData());
                } else {
                    callback.onError(response.body() != null ? response.body().getMessage() : "获取收藏失败");
                }
            }

            @Override
            public void onFailure(Call<ApiResponse<List<Software>>> call, Throwable t) {
                callback.onError(t.getMessage());
            }
        });
    }

    public void saveToken(String token) {
        RetrofitClient.getInstance(context).updateToken(token);
    }

    public String getToken() {
        SharedPreferences prefs = context.getSharedPreferences("auth", Context.MODE_PRIVATE);
        return prefs.getString("token", null);
    }

    public void clearToken() {
        RetrofitClient.getInstance(context).clearToken();
    }

    public boolean isLoggedIn() {
        return getToken() != null && !getToken().isEmpty();
    }
}