package com.software.store.util;

import android.content.Context;
import android.widget.Toast;

/**
 * Toast 工具类
 * 统一管理 Toast 弹出，避免重复创建 Toast 对象
 */
public class ToastUtils {

    private static Context applicationContext;
    private static Toast toast;

    /**
     * 初始化，在 Application 中调用
     *
     * @param context 应用上下文
     */
    public static void init(Context context) {
        applicationContext = context.getApplicationContext();
    }

    /**
     * 显示短 Toast
     *
     * @param message 文本内容
     */
    public static void showShort(String message) {
        show(message, Toast.LENGTH_SHORT);
    }

    /**
     * 显示长 Toast
     *
     * @param message 文本内容
     */
    public static void showLong(String message) {
        show(message, Toast.LENGTH_LONG);
    }

    /**
     * 显示短 Toast（字符串资源）
     *
     * @param resId 字符串资源 ID
     */
    public static void showShort(int resId) {
        show(applicationContext.getString(resId), Toast.LENGTH_SHORT);
    }

    /**
     * 显示 Toast（复用同一 Toast 对象，避免连续弹出堆积）
     *
     * @param message  文本内容
     * @param duration 显示时长
     */
    private static void show(String message, int duration) {
        if (applicationContext == null) {
            return;
        }
        if (toast == null) {
            toast = Toast.makeText(applicationContext, message, duration);
        } else {
            toast.setText(message);
            toast.setDuration(duration);
        }
        toast.show();
    }
}
