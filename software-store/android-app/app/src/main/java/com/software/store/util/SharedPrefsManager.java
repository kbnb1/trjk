package com.software.store.util;

import android.content.Context;
import android.content.SharedPreferences;

import com.google.gson.Gson;
import com.software.store.data.model.Config;
import com.software.store.data.model.User;

public class SharedPrefsManager {

    private static final String PREF_NAME = "software_store_prefs";

    public static final String KEY_TOKEN = "token";
    public static final String KEY_USER_ID = "user_id";
    public static final String KEY_USER_INFO = "user_info";
    public static final String KEY_IS_LOGIN = "is_login";
    public static final String KEY_USER_CONFIG = "user_config";

    private static volatile SharedPrefsManager instance;
    private final SharedPreferences prefs;
    private final Gson gson;

    private SharedPrefsManager(Context context) {
        prefs = context.getApplicationContext().getSharedPreferences(PREF_NAME, Context.MODE_PRIVATE);
        gson = new Gson();
    }

    public static SharedPrefsManager getInstance(Context context) {
        if (instance == null) {
            synchronized (SharedPrefsManager.class) {
                if (instance == null) {
                    instance = new SharedPrefsManager(context);
                }
            }
        }
        return instance;
    }

    public void saveToken(String token) {
        prefs.edit().putString(KEY_TOKEN, token).apply();
    }

    public String getToken() {
        return prefs.getString(KEY_TOKEN, "");
    }

    public void clearToken() {
        prefs.edit().remove(KEY_TOKEN).apply();
    }

    public void saveUser(User user) {
        if (user != null) {
            prefs.edit()
                    .putString(KEY_USER_ID, String.valueOf(user.getId()))
                    .putString(KEY_USER_INFO, gson.toJson(user))
                    .putBoolean(KEY_IS_LOGIN, true)
                    .apply();
        }
    }

    public User getUser() {
        String json = prefs.getString(KEY_USER_INFO, null);
        if (json != null) {
            try {
                return gson.fromJson(json, User.class);
            } catch (Exception e) {
                return null;
            }
        }
        return null;
    }

    public void clearUser() {
        prefs.edit()
                .remove(KEY_USER_ID)
                .remove(KEY_USER_INFO)
                .putBoolean(KEY_IS_LOGIN, false)
                .apply();
    }

    public boolean isLogin() {
        return prefs.getBoolean(KEY_IS_LOGIN, false);
    }

    public String getUserId() {
        return prefs.getString(KEY_USER_ID, "");
    }

    public void saveConfig(Config config) {
        if (config != null) {
            prefs.edit().putString(KEY_USER_CONFIG, gson.toJson(config)).apply();
        }
    }

    public Config getUserConfig() {
        String json = prefs.getString(KEY_USER_CONFIG, null);
        if (json != null) {
            try {
                return gson.fromJson(json, Config.class);
            } catch (Exception e) {
                return null;
            }
        }
        return null;
    }

    public boolean isPhoneVerifyEnabled() {
        Config config = getUserConfig();
        return config != null && config.isEnablePhoneVerify();
    }

    public void setPhoneVerifyEnabled(boolean enabled) {
        Config config = getUserConfig();
        if (config != null) {
            config.setEnablePhoneVerify(enabled);
            saveConfig(config);
        }
    }

    public boolean isEmailVerifyEnabled() {
        Config config = getUserConfig();
        return config != null && config.isEnableEmailVerify();
    }

    public void setEmailVerifyEnabled(boolean enabled) {
        Config config = getUserConfig();
        if (config != null) {
            config.setEnableEmailVerify(enabled);
            saveConfig(config);
        }
    }

    public void clearAll() {
        prefs.edit().clear().apply();
        instance = null;
    }

    public void logout() {
        clearToken();
        clearUser();
    }
}