package com.software.store.ui.splash;

import android.content.Intent;
import android.os.Bundle;
import android.os.CountDownTimer;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

import com.software.store.R;
import com.software.store.ui.common.MainActivity;

/**
 * 启动页 Activity
 * 显示开屏广告与倒计时，倒计时结束后跳转主页面或登录页
 */
public class SplashActivity extends AppCompatActivity {

    /** 倒计时总时长（毫秒） */
    private static final long DURATION = 3000;
    /** 倒计时间隔（毫秒） */
    private static final long INTERVAL = 1000;

    private TextView tvCount;
    private CountDownTimer countDownTimer;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        // 保底策略：setContentView 一旦因为资源异常 / InflateException 崩掉，
        // 立刻回退到纯代码构造的最小布局，保证"至少能看到启动页"，不会闪一下就死。
        try {
            setContentView(R.layout.activity_splash);
            tvCount = findViewById(R.id.tv_count);
            // 跳过按钮点击
            findViewById(R.id.tv_skip).setOnClickListener(v -> goNext());
        } catch (Throwable t) {
            android.util.Log.e("Splash", "setContentView fallback triggered", t);
            // 代码构造兜底：一个全屏渐变背景 + 居中文字
            android.widget.FrameLayout root = new android.widget.FrameLayout(this);
            try {
                root.setBackgroundResource(R.drawable.bg_gradient_page);
            } catch (Throwable ignored) {
                root.setBackgroundColor(0xFFC7D2FE);
            }
            android.widget.TextView tip = new android.widget.TextView(this);
            tip.setText("软件库 - 启动中");
            tip.setTextColor(0xFF1F2330);
            tip.setTextSize(20f);
            tip.setTypeface(null, android.graphics.Typeface.BOLD);
            android.widget.FrameLayout.LayoutParams lp =
                    new android.widget.FrameLayout.LayoutParams(
                            android.view.ViewGroup.LayoutParams.WRAP_CONTENT,
                            android.view.ViewGroup.LayoutParams.WRAP_CONTENT);
            lp.gravity = android.view.Gravity.CENTER;
            root.addView(tip, lp);
            setContentView(root);
            // 3 秒后强制跳转，不依赖控件id
            tvCount = null;
            new android.os.Handler(android.os.Looper.getMainLooper())
                    .postDelayed(this::goNext, 2000);
            return;
        }

        startCountDown();
    }

    /**
     * 启动倒计时
     */
    private void startCountDown() {
        countDownTimer = new CountDownTimer(DURATION, INTERVAL) {
            @Override
            public void onTick(long millisUntilFinished) {
                int remain = (int) (millisUntilFinished / 1000);
                if (remain == 0) {
                    remain = 3;
                }
                if (tvCount != null) {
                    try {
                        tvCount.setText(String.valueOf(remain));
                    } catch (Throwable ignored) {
                    }
                }
            }

            @Override
            public void onFinish() {
                goNext();
            }
        }.start();
    }

    /**
     * 跳转下一页面
     * 已登录跳主页面，未登录跳登录页
     */
    private void goNext() {
        if (countDownTimer != null) {
            countDownTimer.cancel();
            countDownTimer = null;
        }
        // TODO: 根据本地登录状态判断跳转目标
        Intent intent = new Intent(this, MainActivity.class);
        startActivity(intent);
        finish();
        // 淡入淡出过渡动画
        overridePendingTransition(android.R.anim.fade_in, android.R.anim.fade_out);
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        if (countDownTimer != null) {
            countDownTimer.cancel();
            countDownTimer = null;
        }
    }

    @Override
    public void onBackPressed() {
        // 启动页屏蔽返回键
    }
}
