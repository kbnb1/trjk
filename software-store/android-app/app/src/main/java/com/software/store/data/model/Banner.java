package com.software.store.data.model;

import com.google.gson.annotations.SerializedName;

/**
 * 轮播图数据模型
 * 用于首页顶部 Banner 展示
 */
public class Banner {

    /** 轮播图 ID */
    @SerializedName("id")
    private int id;

    /** 标题 */
    @SerializedName("title")
    private String title;

    /** 副标题 */
    @SerializedName("subtitle")
    private String subtitle;

    /** 图片 URL */
    @SerializedName("image")
    private String image;

    /** 跳转链接 */
    @SerializedName("link")
    private String link;

    /** 跳转类型：0 网页、1 软件、2 外部 App */
    @SerializedName("linkType")
    private int linkType;

    /** 标签文案 */
    @SerializedName("tag")
    private String tag;

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

    public String getSubtitle() {
        return subtitle;
    }

    public void setSubtitle(String subtitle) {
        this.subtitle = subtitle;
    }

    public String getImage() {
        return image;
    }

    public void setImage(String image) {
        this.image = image;
    }

    public String getLink() {
        return link;
    }

    public void setLink(String link) {
        this.link = link;
    }

    public int getLinkType() {
        return linkType;
    }

    public void setLinkType(int linkType) {
        this.linkType = linkType;
    }

    public String getTag() {
        return tag;
    }

    public void setTag(String tag) {
        this.tag = tag;
    }
}
