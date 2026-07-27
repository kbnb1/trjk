package com.software.store.data.model;

public class Notice {

    private int id;
    private String type;
    private String content;
    private String updatedAt;

    public Notice() {
    }

    public Notice(int id, String type, String content, String updatedAt) {
        this.id = id;
        this.type = type;
        this.content = content;
        this.updatedAt = updatedAt;
    }

    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public String getType() {
        return type;
    }

    public void setType(String type) {
        this.type = type;
    }

    public String getContent() {
        return content;
    }

    public void setContent(String content) {
        this.content = content;
    }

    public String getUpdatedAt() {
        return updatedAt;
    }

    public void setUpdatedAt(String updatedAt) {
        this.updatedAt = updatedAt;
    }
}