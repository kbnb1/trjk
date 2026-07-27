package com.software.store.util;

import android.content.Context;
import android.content.Intent;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.net.wifi.WifiManager;
import android.provider.Settings;

public class NetworkUtils {

    private NetworkUtils() {
    }

    public static boolean isNetworkAvailable(Context context) {
        ConnectivityManager cm = (ConnectivityManager) context.getApplicationContext()
                .getSystemService(Context.CONNECTIVITY_SERVICE);
        if (cm == null) return false;
        NetworkInfo activeNetwork = cm.getActiveNetworkInfo();
        return activeNetwork != null && activeNetwork.isConnectedOrConnecting();
    }

    public static boolean isWifiConnected(Context context) {
        ConnectivityManager cm = (ConnectivityManager) context.getApplicationContext()
                .getSystemService(Context.CONNECTIVITY_SERVICE);
        if (cm == null) return false;
        NetworkInfo wifiInfo = cm.getNetworkInfo(ConnectivityManager.TYPE_WIFI);
        return wifiInfo != null && wifiInfo.isConnected();
    }

    public static String getNetworkTypeName(Context context) {
        ConnectivityManager cm = (ConnectivityManager) context.getApplicationContext()
                .getSystemService(Context.CONNECTIVITY_SERVICE);
        if (cm == null) return "无网络";

        NetworkInfo activeNetwork = cm.getActiveNetworkInfo();
        if (activeNetwork == null) return "无网络";

        int type = activeNetwork.getType();
        switch (type) {
            case ConnectivityManager.TYPE_WIFI:
                return "Wi-Fi";
            case ConnectivityManager.TYPE_MOBILE:
                return getMobileNetworkTypeName(activeNetwork.getSubtype());
            case ConnectivityManager.TYPE_BLUETOOTH:
                return "蓝牙";
            case ConnectivityManager.TYPE_ETHERNET:
                return "以太网";
            default:
                return "未知网络";
        }
    }

    private static String getMobileNetworkTypeName(int subtype) {
        switch (subtype) {
            case ConnectivityManager.TYPE_GPRS:
            case ConnectivityManager.TYPE_EDGE:
            case ConnectivityManager.TYPE_CDMA:
                return "2G";
            case ConnectivityManager.TYPE_UMTS:
            case ConnectivityManager.TYPE_EVDO_0:
            case ConnectivityManager.TYPE_EVDO_A:
            case ConnectivityManager.TYPE_HSDPA:
            case ConnectivityManager.TYPE_HSUPA:
                return "3G";
            case ConnectivityManager.TYPE_LTE:
                return "4G";
            default:
                return "移动网络";
        }
    }

    public static void openWifiSettings(Context context) {
        Intent intent = new Intent(Settings.ACTION_WIFI_SETTINGS);
        intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
        context.startActivity(intent);
    }

    public static boolean isWifiEnabled(Context context) {
        WifiManager wifiManager = (WifiManager) context.getApplicationContext()
                .getSystemService(Context.WIFI_SERVICE);
        return wifiManager != null && wifiManager.isWifiEnabled();
    }
}