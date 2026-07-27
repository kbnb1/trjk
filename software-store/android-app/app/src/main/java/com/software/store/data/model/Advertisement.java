package com.software.store.data.model;

public class Advertisement {

    private int id;
    private String type;
    private String image;
    private String link;
    private int duration;
    private int status;

    public Advertisement() {
    }

    public Advertisement(int id, String type, String image, String link, int duration, int status) {
        this.id = id;
        this.type = type;
        this.image = image;
        this.link = link;
        this.duration = duration;
        this.status = status;
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

    public int getDuration() {
        return duration;
    }

    public void setDuration(int duration) {
        this.duration = duration;
    }

    public int getStatus() {
        return status;
    }

    public void setStatus(int status) {
        this.status = status;
    }
}