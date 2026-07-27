package com.software.store.data.model;

import java.util.List;

public class PageData {

    private List<Banner> banners;
    private Notice notice;
    private List<Category> categories;
    private List<Toolbar> tools;
    private List<Software> software;

    public PageData() {
    }

    public List<Banner> getBanners() {
        return banners;
    }

    public void setBanners(List<Banner> banners) {
        this.banners = banners;
    }

    public Notice getNotice() {
        return notice;
    }

    public void setNotice(Notice notice) {
        this.notice = notice;
    }

    public List<Category> getCategories() {
        return categories;
    }

    public void setCategories(List<Category> categories) {
        this.categories = categories;
    }

    public List<Toolbar> getTools() {
        return tools;
    }

    public void setTools(List<Toolbar> tools) {
        this.tools = tools;
    }

    public List<Software> getSoftware() {
        return software;
    }

    public void setSoftware(List<Software> software) {
        this.software = software;
    }
}