package com.software.store.data.model;

public class Banner {

    private int id;
    private String title;
    private String image;
    private String link;
    private int sort;
    private int status;

    public Banner() {
    }

    public Banner(int id, String title, String image, String link, int sort, int status) {
        this.id = id;
        this.title = title;
        this.image = image;
        this.link = link;
        this.sort = sort;
        this.status = status;
    }

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