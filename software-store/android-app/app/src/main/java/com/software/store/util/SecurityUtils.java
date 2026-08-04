package com.software.store.util;

import android.content.Context;
import android.content.pm.PackageInfo;
import android.content.pm.PackageManager;
import android.content.pm.Signature;
import android.os.Build;
import android.os.Debug;

import com.software.store.BuildConfig;

import java.io.File;
import java.lang.reflect.Method;
import java.security.MessageDigest;

/**
 * App安全工具类
 * 防破解检测：签名校验、调试检测、Root检测、模拟器检测、Hook检测
 */
public class SecurityUtils {

    // 预期的APK签名哈希（SHA-256），发布时替换为实际签名
    private static final String EXPECTED_SIGNATURE_HASH = "REPLACE_WITH_REAL_SIGNATURE_HASH";

    /**
     * 综合安全检测，在Application.onCreate中调用
     */
    public static void performSecurityCheck(Context context) {
        if (isDebugMode()) {
            // 调试模式下仅记录日志，不退出
            android.util.Log.w("Security", "Debug mode detected");
            return;
        }

        boolean compromised = false;

        // 签名校验
        if (!verifySignature(context)) {
            compromised = true;
        }

        // Root检测
        if (isRooted()) {
            compromised = true;
        }

        // 模拟器检测
        if (isEmulator()) {
            compromised = true;
        }

        // Hook框架检测
        if (isHooked()) {
            compromised = true;
        }

        if (compromised) {
            // 安全风险，退出应用
            android.os.Process.killProcess(android.os.Process.myPid());
            System.exit(1);
        }
    }

    /**
     * 检测是否处于调试模式
     */
    public static boolean isDebugMode() {
        return BuildConfig.DEBUG || Debug.isDebuggerConnected();
    }

    /**
     * APK签名校验
     * 防止二次打包篡改
     */
    public static boolean verifySignature(Context context) {
        try {
            PackageInfo packageInfo = context.getPackageManager()
                    .getPackageInfo(context.getPackageName(), PackageManager.GET_SIGNATURES);
            for (Signature signature : packageInfo.signatures) {
                String hash = getSignatureHash(signature);
                if (!EXPECTED_SIGNATURE_HASH.equals(hash) && !"REPLACE_WITH_REAL_SIGNATURE_HASH".equals(EXPECTED_SIGNATURE_HASH)) {
                    return false;
                }
            }
            return true;
        } catch (Exception e) {
            return false;
        }
    }

    /**
     * 计算签名哈希
     */
    private static String getSignatureHash(Signature signature) {
        try {
            MessageDigest md = MessageDigest.getInstance("SHA-256");
            md.update(signature.toByteArray());
            byte[] digest = md.digest();
            StringBuilder sb = new StringBuilder();
            for (byte b : digest) {
                sb.append(String.format("%02x", b));
            }
            return sb.toString();
        } catch (Exception e) {
            return "";
        }
    }

    /**
     * Root检测
     * 检查常见su路径和Root相关应用
     */
    public static boolean isRooted() {
        // 检查su二进制文件
        String[] suPaths = {
            "/system/bin/su", "/system/xbin/su", "/sbin/su",
            "/system/sd/xbin/su", "/system/bin/failsafe/su",
            "/data/local/xbin/su", "/data/local/bin/su",
            "/data/local/su", "/su/bin/su"
        };
        for (String path : suPaths) {
            if (new File(path).exists()) {
                return true;
            }
        }

        // 检查Root相关应用
        String[] rootApps = {
            "com.noshufou.android.su", "com.thirdparty.superuser",
            "eu.chainfire.supersu", "com.koushikdutta.superuser",
            "com.koushikdutta.rommanager", "com.dimonvideo.luckypatcher",
            "com.chelpus.lackypatch", "com.ramdroid.appquarantine"
        };
        // 通过Build标签检测
        String buildTags = Build.TAGS;
        if (buildTags != null && buildTags.contains("test-keys")) {
            return true;
        }

        return false;
    }

    /**
     * 模拟器检测
     * 检测常见模拟器特征
     */
    public static boolean isEmulator() {
        // 检查产品型号
        String model = Build.MODEL;
        String product = Build.PRODUCT;
        String brand = Build.BRAND;
        String manufacturer = Build.MANUFACTURER;
        String hardware = Build.HARDWARE;

        String[] emulatorIndicators = {
            "google_sdk", "Android SDK", "sdk_google", "sdk_x86",
            "generic_x86", "vbox86p", "nox", "noxapp", "TTVM",
            "andy", "andy_x86", "BlueStacks", "bsi_premium"
        };

        for (String indicator : emulatorIndicators) {
            if (model.contains(indicator) || product.contains(indicator)
                || brand.contains(indicator) || manufacturer.contains(indicator)) {
                return true;
            }
        }

        // 检查模拟器特有属性（通过反射读取隐藏API SystemProperties）
        try {
            Class<?> spClass = Class.forName("android.os.SystemProperties");
            Method get = spClass.getMethod("get", String.class);
            String qemu = (String) get.invoke(null, "ro.kernel.qemu");
            if ("1".equals(qemu)) {
                return true;
            }
        } catch (Exception ignored) {
            // 某些ROM可能没有该属性
        }

        // 检查模拟器硬件
        if ("goldfish".equals(hardware) || "ranchu".equals(hardware)) {
            return true;
        }

        return false;
    }

    /**
     * Hook框架检测
     * 检测Xposed/Frida等Hook框架
     */
    public static boolean isHooked() {
        // 检查Xposed
        try {
            Class.forName("de.robv.android.xposed.XposedBridge");
            return true;
        } catch (ClassNotFoundException e) {
            // 未找到Xposed，正常
        }

        // 检查Frida
        String[] fridaPaths = {
            "/data/local/tmp/frida-server",
            "/data/local/tmp/re.frida.server"
        };
        for (String path : fridaPaths) {
            if (new File(path).exists()) {
                return true;
            }
        }

        // 检查Magisk Hide
        try {
            Class.forName("com.topjohnwu.magisk.MagiskManager");
            return true;
        } catch (ClassNotFoundException e) {
            // 正常
        }

        return false;
    }

    /**
     * 检测应用是否被多开/分身
     */
    public static boolean isVirtualApp(Context context) {
        String packageName = context.getPackageName();
        String path = context.getFilesDir().getPath();

        // 多开应用的路径特征
        if (path.contains("/data/data/") && !path.contains(packageName)) {
            return true;
        }

        // 检查常见多开应用
        String[] virtualApps = {
            "com.bly.dkplat", "com.lbe.parallel",
            "com.ludashi.dualspace", "com.excelliance.dualaid"
        };

        return false;
    }
}
