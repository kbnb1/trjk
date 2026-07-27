package com.software.store.data.model;

public class Config {

    private boolean enablePhoneVerify;
    private boolean enableEmailVerify;
    private String siteName;
    private String contactInfo;
    private String appVersion;

    public Config() {
    }

    public boolean isEnablePhoneVerify() {
        return enablePhoneVerify;
    }

    public void setEnablePhoneVerify(boolean enablePhoneVerify) {
        this.enablePhoneVerify = enablePhoneVerify;
    }

    public boolean isEnableEmailVerify() {
        return enableEmailVerify;
    }

    public void setEnableEmailVerify(boolean enableEmailVerify) {
        this.enableEmailVerify = enableEmailVerify;
    }

    public String getSiteName() {
        return siteName;
    }

    public void setSiteName(String siteName) {
        this.siteName = siteName;
    }

    public String getContactInfo() {
        return contactInfo;
    }

    public void setContactInfo(String contactInfo) {
        this.contactInfo = contactInfo;
    }

    public String getAppVersion() {
        return appVersion;
    }

    public void setAppVersion(String appVersion) {
        this.appVersion = appVersion;
    }
}