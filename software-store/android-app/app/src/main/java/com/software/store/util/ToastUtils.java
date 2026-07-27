package com.software.store.util;

import android.content.Context;
import android.graphics.Color;
import android.graphics.drawable.GradientDrawable;
import android.os.Handler;
import android.os.Looper;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.TextView;
import android.widget.Toast;

import com.software.store.R;

public class ToastUtils {

    private static volatile ToastUtils instance;
    private final Handler handler;
    private Context appContext;

    private ToastUtils() {
        handler = new Handler(Looper.getMainLooper());
    }

    public static ToastUtils getInstance() {
        if (instance == null) {
            synchronized (ToastUtils.class) {
                if (instance == null) {
                    instance = new ToastUtils();
                }
            }
        }
        return instance;
    }

    public void init(Context context) {
        if (context != null) {
            this.appContext = context.getApplicationContext();
        }
    }

    private Context getContext(Context context) {
        return (context != null) ? context.getApplicationContext() : appContext;
    }

    public void showShort(Context context, String msg) {
        final Context ctx = getContext(context);
        if (ctx == null) return;
        handler.post(() -> {
            Toast.makeText(ctx, msg, Toast.LENGTH_SHORT).show();
        });
    }

    public void showLong(Context context, String msg) {
        final Context ctx = getContext(context);
        if (ctx == null) return;
        handler.post(() -> {
            Toast.makeText(ctx, msg, Toast.LENGTH_LONG).show();
        });
    }

    public void showError(Context context, String msg) {
        final Context ctx = getContext(context);
        if (ctx == null) return;
        handler.post(() -> showCustomToast(ctx, msg, R.drawable.ic_error, "#E74C3C"));
    }

    public void showSuccess(Context context, String msg) {
        final Context ctx = getContext(context);
        if (ctx == null) return;
        handler.post(() -> showCustomToast(ctx, msg, R.drawable.ic_success, "#2ECC71"));
    }

    private void showCustomToast(Context context, String msg, int iconRes, String colorHex) {
        Context ctx = (context != null) ? context : appContext;
        if (ctx == null) return;
        Toast toast = new Toast(ctx);
        toast.setDuration(Toast.LENGTH_SHORT);
        toast.setGravity(Gravity.CENTER, 0, 0);

        View toastView = LayoutInflater.from(ctx).inflate(R.layout.item_empty_state, null);
        TextView textView = toastView.findViewById(R.id.tv_empty_text);
        if (textView != null) {
            textView.setText(msg);
            textView.setTextColor(Color.WHITE);
        }

        GradientDrawable drawable = new GradientDrawable();
        drawable.setColor(Color.parseColor(colorHex));
        drawable.setCornerRadius(dp2px(ctx, 8));
        toastView.setBackground(drawable);

        toast.setView(toastView);
        toast.show();
    }

    private int dp2px(Context context, int dp) {
        float density = context.getResources().getDisplayMetrics().density;
        return (int) (dp * density + 0.5f);
    }
}