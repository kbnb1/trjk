package com.software.store.data.model;

import com.google.gson.annotations.SerializedName;

/**
 * 用户数据模型
 * 描述用户的基本信息
 */
public class User {

    /** 用户 ID */
    @SerializedName("id")
    private int id;

    /** 用户名 */
    @SerializedName("username")
    private String username;

    /** 昵称 */
    @SerializedName("nickname")
    private String nickname;

    /** 头像 URL */
    @SerializedName("avatar")
    private String avatar;

    /** 手机号 */
    @SerializedName("phone")
    private String phone;

    /** 邮箱 */
    @SerializedName("email")
    private String email;

    /** 登录令牌 */
    @SerializedName("token")
    private String token;

    /** 是否为 VIP 会员 */
    @SerializedName("isVip")
    private boolean vip;

    /** VIP 到期时间 */
    @SerializedName("vipExpireTime")
    private String vipExpireTime;

    /** 注册时间 */
    @SerializedName("registerTime")
    private String registerTime;

    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public String getUsername() {
        return username;
    }

    public void setUsername(String username) {
        this.username = username;
    }

    public String getNickname() {
        return nickname;
    }

    public void setNickname(String nickname) {
        this.nickname = nickname;
    }

    public String getAvatar() {
        return avatar;
    }

    public void setAvatar(String avatar) {
        this.avatar = avatar;
    }

    public String getPhone() {
        return phone;
    }

    public void setPhone(String phone) {
        this.phone = phone;
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public String getToken() {
        return token;
    }

    public void setToken(String token) {
        this.token = token;
    }

    public boolean isVip() {
        return vip;
    }

    public void setVip(boolean vip) {
        this.vip = vip;
    }

    public String getVipExpireTime() {
        return vipExpireTime;
    }

    public void setVipExpireTime(String vipExpireTime) {
        this.vipExpireTime = vipExpireTime;
    }

    public String getRegisterTime() {
        return registerTime;
    }

    public void setRegisterTime(String registerTime) {
        this.registerTime = registerTime;
    }

    /**
     * 获取用户展示昵称（昵称为空时回退到用户名）
     */
    public String getDisplayNickname() {
        return nickname != null && !nickname.isEmpty() ? nickname : username;
    }
}
