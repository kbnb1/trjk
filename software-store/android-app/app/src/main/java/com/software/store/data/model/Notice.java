package com.software.store.data.model;

import com.google.gson.annotations.SerializedName;

/**
 * 公告数据模型
 * 用于首页公告专区与滚动公告展示
 */
public class Notice {

    /** 公告 ID */
    @SerializedName("id")
    private int id;

    /** 公告标题 */
    @SerializedName("title")
    private String title;

    /** 公告内容 */
    @SerializedName("content")
    private String content;

    /** 发布时间 */
    @SerializedName("createTime")
    private String createTime;

    /** 公告类型：0 滚动、1 列表 */
    @SerializedName("type")
    private int type;

    /** 是否置顶 */
    @SerializedName("isTop")
    private boolean top;

    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public String getTitle() {
        return title;
    }

    public void setTitle(String title) {
        this.title = title;
    }

    public String getContent() {
        return content;
    }

    public void setContent(String content) {
        this.content = content;
    }

    public String getCreateTime() {
        return createTime;
    }

    public void setCreateTime(String createTime) {
        this.createTime = createTime;
    }

    public int getType() {
        return type;
    }

    public void setType(int type) {
        this.type = type;
    }

    public boolean isTop() {
        return top;
    }

    public void setTop(boolean top) {
        this.top = top;
    }
}
