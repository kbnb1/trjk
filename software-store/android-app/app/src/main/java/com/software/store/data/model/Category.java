package com.software.store.data.model;

import com.google.gson.annotations.SerializedName;

/**
 * 分类数据模型
 * 用于软件页顶部分类标签
 */
public class Category {

    /** 分类 ID */
    @SerializedName("id")
    private int id;

    /** 分类名称 */
    @SerializedName("name")
    private String name;

    /** 分类图标 */
    @SerializedName("icon")
    private String icon;

    /** 排序序号 */
    @SerializedName("sort")
    private int sort;

    public Category(int id, String name) {
        this.id = id;
        this.name = name;
    }

    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getIcon() {
        return icon;
    }

    public void setIcon(String icon) {
        this.icon = icon;
    }

    public int getSort() {
        return sort;
    }

    public void setSort(int sort) {
        this.sort = sort;
    }
}
