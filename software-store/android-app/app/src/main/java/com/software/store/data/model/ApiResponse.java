package com.software.store.data.model;

import com.google.gson.annotations.SerializedName;

/**
 * 泛型 API 响应类
 * 统一封装服务端返回数据结构 {code, message, data, time}
 *
 * @param <T> 业务数据类型
 */
public class ApiResponse<T> {

    /** 状态码，200 表示成功 */
    @SerializedName("code")
    private int code;

    /** 提示消息 */
    @SerializedName("message")
    private String message;

    /** 业务数据 */
    @SerializedName("data")
    private T data;

    /** 服务器时间戳 */
    @SerializedName("time")
    private long time;

    public int getCode() {
        return code;
    }

    public void setCode(int code) {
        this.code = code;
    }

    public String getMessage() {
        return message;
    }

    public void setMessage(String message) {
        this.message = message;
    }

    public T getData() {
        return data;
    }

    public void setData(T data) {
        this.data = data;
    }

    public long getTime() {
        return time;
    }

    public void setTime(long time) {
        this.time = time;
    }

    /**
     * 判断请求是否成功
     *
     * @return code == 200 返回 true
     */
    public boolean isSuccess() {
        return code == 200;
    }
}
