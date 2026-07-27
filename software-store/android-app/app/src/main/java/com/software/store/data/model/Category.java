package com.software.store.data.model;

public class Category {

    private int id;
    private String name;
    private String icon;
    private int sort;
    private int status;

    public Category() {
    }

    public Category(int id, String name, String icon, int sort, int status) {
        this.id = id;
        this.name = name;
        this.icon = icon;
        this.sort = sort;
        this.status = status;
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

    public int getStatus() {
        return status;
    }

    public void setStatus(int status) {
        this.status = status;
    }
}