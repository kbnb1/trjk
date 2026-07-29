package com.software.store.data.model;

import com.google.gson.annotations.SerializedName;

/**
 * 软件数据模型
 * 描述一款软件的完整信息
 */
public class Software {

    /** 软件 ID */
    @SerializedName("id")
    private int id;

    /** 软件名称 */
    @SerializedName("name")
    private String name;

    /** 软件描述 */
    @SerializedName("description")
    private String description;

    /** 软件图标 URL */
    @SerializedName("icon")
    private String icon;

    /** 软件下载 URL */
    @SerializedName("downloadUrl")
    private String downloadUrl;

    /** 版本号 */
    @SerializedName("version")
    private String version;

    /** 安装包大小（MB） */
    @SerializedName("size")
    private String size;

    /** 下载量 */
    @SerializedName("downloadCount")
    private long downloadCount;

    /** 评分 */
    @SerializedName("rating")
    private float rating;

    /** 所属分类 ID */
    @SerializedName("categoryId")
    private int categoryId;

    /** 所属分类名称 */
    @SerializedName("categoryName")
    private String categoryName;

    /** 软件介绍（详情） */
    @SerializedName("intro")
    private String intro;

    /** 使用须知 */
    @SerializedName("notice")
    private String notice;

    /** 更新时间 */
    @SerializedName("updateTime")
    private String updateTime;

    /** 是否为热门 */
    @SerializedName("isHot")
    private boolean hot;

    /** 排名序号（用于排行榜展示） */
    private transient int rank;

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

    public String getDescription() {
        return description;
    }

    public void setDescription(String description) {
        this.description = description;
    }

    public String getIcon() {
        return icon;
    }

    public void setIcon(String icon) {
        this.icon = icon;
    }

    public String getDownloadUrl() {
        return downloadUrl;
    }

    public void setDownloadUrl(String downloadUrl) {
        this.downloadUrl = downloadUrl;
    }

    public String getVersion() {
        return version;
    }

    public void setVersion(String version) {
        this.version = version;
    }

    public String getSize() {
        return size;
    }

    public void setSize(String size) {
        this.size = size;
    }

    public long getDownloadCount() {
        return downloadCount;
    }

    public void setDownloadCount(long downloadCount) {
        this.downloadCount = downloadCount;
    }

    public float getRating() {
        return rating;
    }

    public void setRating(float rating) {
        this.rating = rating;
    }

    public int getCategoryId() {
        return categoryId;
    }

    public void setCategoryId(int categoryId) {
        this.categoryId = categoryId;
    }

    public String getCategoryName() {
        return categoryName;
    }

    public void setCategoryName(String categoryName) {
        this.categoryName = categoryName;
    }

    public String getIntro() {
        return intro;
    }

    public void setIntro(String intro) {
        this.intro = intro;
    }

    public String getNotice() {
        return notice;
    }

    public void setNotice(String notice) {
        this.notice = notice;
    }

    public String getUpdateTime() {
        return updateTime;
    }

    public void setUpdateTime(String updateTime) {
        this.updateTime = updateTime;
    }

    public boolean isHot() {
        return hot;
    }

    public void setHot(boolean hot) {
        this.hot = hot;
    }

    public int getRank() {
        return rank;
    }

    public void setRank(int rank) {
        this.rank = rank;
    }

    /**
     * 格式化下载量展示（如 528万）
     *
     * @return 格式化后的下载量字符串
     */
    public String getFormatDownloadCount() {
        if (downloadCount >= 10000) {
            return String.format("%.1f万", downloadCount / 10000.0);
        }
        return String.valueOf(downloadCount);
    }
}
