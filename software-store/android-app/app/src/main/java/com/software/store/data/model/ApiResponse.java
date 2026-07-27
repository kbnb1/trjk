package com.software.store.data.model;

public class ApiResponse<T> {

    private int code;
    private String message;
    private T data;
    private long time;

    public ApiResponse() {
    }

    public ApiResponse(int code, String message, T data, long time) {
        this.code = code;
        this.message = message;
        this.data = data;
        this.time = time;
    }

    public boolean isSuccess() {
        return code == 200;
    }

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
}