package com.software.store.data.remote;

import com.software.store.data.model.Advertisement;
import com.software.store.data.model.ApiResponse;
import com.software.store.data.model.Banner;
import com.software.store.data.model.Category;
import com.software.store.data.model.Config;
import com.software.store.data.model.DownloadRecord;
import com.software.store.data.model.Notice;
import com.software.store.data.model.PageData;
import com.software.store.data.model.Software;
import com.software.store.data.model.Toolbar;
import com.software.store.data.model.User;

import java.util.List;
import java.util.Map;

import retrofit2.Call;
import retrofit2.http.Body;
import retrofit2.http.GET;
import retrofit2.http.POST;
import retrofit2.http.Path;
import retrofit2.http.Query;

public interface ApiService {

    @GET("api/app/home")
    Call<ApiResponse<PageData>> getHomeData();

    @GET("api/app/software")
    Call<ApiResponse<List<Software>>> getSoftwareList(
            @Query("category_id") Integer categoryId,
            @Query("page") int page,
            @Query("per_page") int perPage
    );

    @GET("api/app/software/{id}")
    Call<ApiResponse<Software>> getSoftwareDetail(@Path("id") int id);

    @GET("api/app/software/{id}/recommend")
    Call<ApiResponse<List<Software>>> getRelatedSoftware(@Path("id") int id);

    @GET("api/app/category")
    Call<ApiResponse<List<Category>>> getCategories();

    @GET("api/app/toolbar")
    Call<ApiResponse<List<Toolbar>>> getToolbar();

    @GET("api/app/splash")
    Call<ApiResponse<Advertisement>> getSplashAd();

    @GET("api/app/config")
    Call<ApiResponse<Config>> getConfig();

    @GET("api/app/pages")
    Call<ApiResponse<List<Notice>>> getPages();

    @GET("api/app/search")
    Call<ApiResponse<List<Software>>> searchSoftware(
            @Query("keyword") String keyword,
            @Query("page") int page
    );

    @POST("api/app/login")
    Call<ApiResponse<User>> login(@Body Map<String, String> params);

    @POST("api/app/register")
    Call<ApiResponse<User>> register(@Body Map<String, String> params);

    @POST("api/app/send_code")
    Call<ApiResponse<Void>> sendCode(@Body Map<String, String> params);

    @GET("api/app/user/info")
    Call<ApiResponse<User>> getUserInfo();

    @POST("api/app/user/update")
    Call<ApiResponse<User>> updateUser(@Body Map<String, String> params);

    @POST("api/app/favorite")
    Call<ApiResponse<Void>> toggleFavorite(@Body Map<String, Integer> params);

    @GET("api/app/favorites")
    Call<ApiResponse<List<Software>>> getFavorites();

    @POST("api/app/download")
    Call<ApiResponse<DownloadRecord>> startDownload(@Body Map<String, Integer> params);

    @GET("api/app/downloads")
    Call<ApiResponse<List<DownloadRecord>>> getDownloads();

    @POST("api/app/download/{id}/progress")
    Call<ApiResponse<Void>> updateDownloadProgress(
            @Path("id") int id,
            @Body Map<String, Integer> params
    );

    @POST("api/app/feedback")
    Call<ApiResponse<Void>> submitFeedback(@Body Map<String, String> params);
}