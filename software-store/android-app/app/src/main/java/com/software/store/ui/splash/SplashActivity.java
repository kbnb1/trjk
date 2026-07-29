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
        setContentView(R.layout.activity_splash);

        tvCount = findViewById(R.id.tv_count);

        // 跳过按钮点击
        findViewById(R.id.tv_skip).setOnClickListener(v -> goNext());

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
                tvCount.setText(String.valueOf(remain));
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
