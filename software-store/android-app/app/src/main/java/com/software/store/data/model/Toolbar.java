package com.software.store.data.model;

public class Toolbar {

    private int id;
    private String name;
    private String icon;
    private String description;
    private String link;
    private int sort;
    private int status;

    public Toolbar() {
    }

    public Toolbar(int id, String name, String icon, String description, String link, int sort, int status) {
        this.id = id;
        this.name = name;
        this.icon = icon;
        this.description = description;
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

    public String getDescription() {
        return description;
    }

    public void setDescription(String description) {
        this.description = description;
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