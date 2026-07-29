package com.software.store.data.remote;

import com.software.store.data.model.ApiResponse;
import com.software.store.data.model.Banner;
import com.software.store.data.model.Category;
import com.software.store.data.model.Notice;
import com.software.store.data.model.Software;
import com.software.store.data.model.User;

import java.util.List;

import retrofit2.Call;
import retrofit2.http.Body;
import retrofit2.http.GET;
import retrofit2.http.POST;
import retrofit2.http.Path;
import retrofit2.http.Query;

/**
 * Retrofit API 接口
 * 定义所有与服务端交互的接口端点
 */
public interface ApiService {

    // ==================== 用户相关 ====================

    /**
     * 用户登录
     *
     * @param username 用户名
     * @param password 密码
     */
    @POST("api/user/login")
    Call<ApiResponse<User>> login(@Query("username") String username,
                                  @Query("password") String password);

    /**
     * 用户注册
     *
     * @param body 注册参数（用户名、密码、手机号、邮箱）
     */
    @POST("api/user/register")
    Call<ApiResponse<User>> register(@Body RegisterRequest body);

    /**
     * 获取短信验证码
     *
     * @param phone 手机号
     */
    @POST("api/user/sendCode")
    Call<ApiResponse<Void>> sendCode(@Query("phone") String phone);

    /**
     * 退出登录
     */
    @POST("api/user/logout")
    Call<ApiResponse<Void>> logout();

    /**
     * 获取用户信息
     */
    @GET("api/user/info")
    Call<ApiResponse<User>> getUserInfo();

    // ==================== 首页相关 ====================

    /**
     * 获取轮播图列表
     */
    @GET("api/home/banners")
    Call<ApiResponse<List<Banner>>> getBanners();

    /**
     * 获取滚动公告
     */
    @GET("api/home/notice/scroll")
    Call<ApiResponse<List<Notice>>> getScrollNotices();

    /**
     * 获取公告列表
     */
    @GET("api/home/notices")
    Call<ApiResponse<List<Notice>>> getNotices();

    /**
     * 获取推荐软件列表
     */
    @GET("api/home/recommend")
    Call<ApiResponse<List<Software>>> getRecommendSoftware();

    // ==================== 软件相关 ====================

    /**
     * 获取分类列表
     */
    @GET("api/software/categories")
    Call<ApiResponse<List<Category>>> getCategories();

    /**
     * 获取软件排行列表
     *
     * @param categoryId 分类 ID，0 表示全部
     * @param page       页码
     * @param pageSize   每页数量
     */
    @GET("api/software/rank")
    Call<ApiResponse<List<Software>>> getSoftwareRank(@Query("categoryId") int categoryId,
                                                       @Query("page") int page,
                                                       @Query("pageSize") int pageSize);

    /**
     * 搜索软件
     *
     * @param keyword 关键词
     */
    @GET("api/software/search")
    Call<ApiResponse<List<Software>>> searchSoftware(@Query("keyword") String keyword);

    /**
     * 获取软件详情
     *
     * @param id 软件 ID
     */
    @GET("api/software/detail/{id}")
    Call<ApiResponse<Software>> getSoftwareDetail(@Path("id") int id);

    /**
     * 获取相关推荐软件
     *
     * @param id 软件 ID
     */
    @GET("api/software/recommend/{id}")
    Call<ApiResponse<List<Software>>> getRelatedSoftware(@Path("id") int id);

    // ==================== 注册请求体 ====================

    /**
     * 注册请求参数
     */
    class RegisterRequest {
        public String username;
        public String password;
        public String phone;
        public String email;

        public RegisterRequest(String username, String password, String phone, String email) {
            this.username = username;
            this.password = password;
            this.phone = phone;
            this.email = email;
        }
    }
}
