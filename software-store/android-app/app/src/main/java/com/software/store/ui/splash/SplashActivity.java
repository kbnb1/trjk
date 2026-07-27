package com.software.store.ui.splash;

import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.view.View;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;
import android.widget.FrameLayout;
import android.view.Gravity;

import androidx.appcompat.app.AppCompatActivity;

import com.bumptech.glide.Glide;
import com.software.store.R;
import com.software.store.data.model.Advertisement;
import com.software.store.data.repository.HomeRepository;
import com.software.store.databinding.ActivitySplashBinding;
import com.software.store.ui.common.LoginActivity;
import com.software.store.ui.common.MainActivity;
import com.software.store.ui.common.WebViewActivity;
import com.software.store.util.NetworkUtils;
import com.software.store.util.SharedPrefsManager;
import com.software.store.util.ToastUtils;

public class SplashActivity extends AppCompatActivity {

    private static final long COUNTDOWN_MS = 3000;

    private ActivitySplashBinding binding;
    private Handler handler;
    private Advertisement splashAd;
    private boolean adClicked = false;
    private boolean skipClicked = false;
    private int remainingSeconds = 3;

    private FrameLayout adOverlay;
    private ImageView ivAdImage;
    private Button btnSkip;
    private TextView tvCountdown;

    private final Runnable countdownRunnable = new Runnable() {
        @Override
        public void run() {
            remainingSeconds--;
            if (tvCountdown != null) {
                tvCountdown.setText(remainingSeconds + "s 跳过");
            }
            if (remainingSeconds <= 0) {
                proceedToNextScreen();
            } else {
                handler.postDelayed(this, 1000);
            }
        }
    };

    private final Runnable fallbackRunnable = new Runnable() {
        @Override
        public void run() {
            if (splashAd == null) {
                proceedToNextScreen();
            }
        }
    };

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivitySplashBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        handler = new Handler(Looper.getMainLooper());

        setupAdOverlay();
        checkNetworkAndFetchAd();
    }

    private void setupAdOverlay() {
        View rootView = binding.getRoot();
        adOverlay = new FrameLayout(this);
        adOverlay.setLayoutParams(new FrameLayout.LayoutParams(
                FrameLayout.LayoutParams.MATCH_PARENT,
                FrameLayout.LayoutParams.MATCH_PARENT));

        ivAdImage = new ImageView(this);
        FrameLayout.LayoutParams imageParams = new FrameLayout.LayoutParams(
                FrameLayout.LayoutParams.MATCH_PARENT,
                FrameLayout.LayoutParams.MATCH_PARENT);
        ivAdImage.setScaleType(ImageView.ScaleType.CENTER_CROP);
        ivAdImage.setLayoutParams(imageParams);
        adOverlay.addView(ivAdImage);

        FrameLayout topRightContainer = new FrameLayout(this);
        FrameLayout.LayoutParams topRightParams = new FrameLayout.LayoutParams(
                FrameLayout.LayoutParams.WRAP_CONTENT,
                FrameLayout.LayoutParams.WRAP_CONTENT);
        topRightParams.gravity = Gravity.TOP | Gravity.END;
        topRightParams.setMargins(
                (int) (16 * getResources().getDisplayMetrics().density),
                (int) (48 * getResources().getDisplayMetrics().density),
                (int) (16 * getResources().getDisplayMetrics().density),
                0);
        topRightContainer.setLayoutParams(topRightParams);

        btnSkip = new Button(this);
        btnSkip.setText("跳过");
        btnSkip.setTextColor(getResources().getColor(R.color.white));
        btnSkip.setBackgroundResource(R.drawable.bg_rounded);
        btnSkip.setPadding(
                (int) (16 * getResources().getDisplayMetrics().density),
                (int) (8 * getResources().getDisplayMetrics().density),
                (int) (16 * getResources().getDisplayMetrics().density),
                (int) (8 * getResources().getDisplayMetrics().density));

        FrameLayout.LayoutParams btnParams = new FrameLayout.LayoutParams(
                FrameLayout.LayoutParams.WRAP_CONTENT,
                FrameLayout.LayoutParams.WRAP_CONTENT);
        btnParams.gravity = Gravity.CENTER;
        btnSkip.setLayoutParams(btnParams);

        tvCountdown = new TextView(this);
        tvCountdown.setTextColor(getResources().getColor(R.color.white));
        tvCountdown.setTextSize(14);
        FrameLayout.LayoutParams countdownParams = new FrameLayout.LayoutParams(
                FrameLayout.LayoutParams.WRAP_CONTENT,
                FrameLayout.LayoutParams.WRAP_CONTENT);
        countdownParams.gravity = Gravity.CENTER;
        countdownParams.setMargins(0, 0, (int) (8 * getResources().getDisplayMetrics().density), 0);
        tvCountdown.setLayoutParams(countdownParams);

        topRightContainer.addView(tvCountdown);
        topRightContainer.addView(btnSkip);
        adOverlay.addView(topRightContainer);

        adOverlay.setVisibility(View.GONE);
        adOverlay.setOnClickListener(v -> {
            if (splashAd != null && splashAd.getLink() != null && !splashAd.getLink().isEmpty()) {
                adClicked = true;
                skipClicked = true;
                handler.removeCallbacksAndMessages(null);
                openAdLink();
            }
        });

        btnSkip.setOnClickListener(v -> {
            skipClicked = true;
            handler.removeCallbacksAndMessages(null);
            proceedToNextScreen();
        });

        if (rootView instanceof android.widget.FrameLayout) {
            ((android.widget.FrameLayout) rootView).addView(adOverlay);
        } else if (rootView instanceof androidx.constraintlayout.widget.ConstraintLayout) {
            androidx.constraintlayout.widget.ConstraintLayout cl =
                    (androidx.constraintlayout.widget.ConstraintLayout) rootView;
            androidx.constraintlayout.widget.ConstraintSet cs =
                    new androidx.constraintlayout.widget.ConstraintSet();
            cs.clone(cl);
            int overlayId = View.generateViewId();
            adOverlay.setId(overlayId);
            cl.addView(adOverlay);
            cs.connect(overlayId, androidx.constraintlayout.widget.ConstraintSet.LEFT,
                    androidx.constraintlayout.widget.ConstraintSet.PARENT_ID,
                    androidx.constraintlayout.widget.ConstraintSet.LEFT);
            cs.connect(overlayId, androidx.constraintlayout.widget.ConstraintSet.RIGHT,
                    androidx.constraintlayout.widget.ConstraintSet.PARENT_ID,
                    androidx.constraintlayout.widget.ConstraintSet.RIGHT);
            cs.connect(overlayId, androidx.constraintlayout.widget.ConstraintSet.TOP,
                    androidx.constraintlayout.widget.ConstraintSet.PARENT_ID,
                    androidx.constraintlayout.widget.ConstraintSet.TOP);
            cs.connect(overlayId, androidx.constraintlayout.widget.ConstraintSet.BOTTOM,
                    androidx.constraintlayout.widget.ConstraintSet.PARENT_ID,
                    androidx.constraintlayout.widget.ConstraintSet.BOTTOM);
            cs.applyTo(cl);
        }
    }

    private void checkNetworkAndFetchAd() {
        if (!NetworkUtils.isNetworkAvailable(this)) {
            ToastUtils.getInstance().showShort(this, "网络不可用，请检查网络连接");
            handler.postDelayed(fallbackRunnable, COUNTDOWN_MS);
            return;
        }

        fetchSplashAd();
    }

    private void fetchSplashAd() {
        HomeRepository.getInstance().getSplashAd(new HomeRepository.Callback<Advertisement>() {
            @Override
            public void onSuccess(Advertisement result) {
                splashAd = result;
                runOnUiThread(() -> showSplashAd());
            }

            @Override
            public void onError(String message) {
                runOnUiThread(() -> {
                    handler.postDelayed(fallbackRunnable, COUNTDOWN_MS);
                });
            }
        });
    }

    private void showSplashAd() {
        if (splashAd == null || splashAd.getStatus() != 1
                || splashAd.getImage() == null || splashAd.getImage().isEmpty()) {
            handler.postDelayed(fallbackRunnable, COUNTDOWN_MS);
            return;
        }

        binding.progressIndicator.setVisibility(View.GONE);
        binding.tvAppName.setVisibility(View.GONE);
        binding.tvSlogan.setVisibility(View.GONE);

        adOverlay.setVisibility(View.VISIBLE);

        Glide.with(this)
                .load(splashAd.getImage())
                .centerCrop()
                .into(ivAdImage);

        int duration = splashAd.getDuration() > 0 ? splashAd.getDuration() : 3;
        remainingSeconds = duration;
        tvCountdown.setText(remainingSeconds + "s 跳过");
        handler.postDelayed(countdownRunnable, 1000);
    }

    private void openAdLink() {
        if (splashAd != null && splashAd.getLink() != null) {
            Intent intent = new Intent(this, WebViewActivity.class);
            intent.putExtra(WebViewActivity.EXTRA_URL, splashAd.getLink());
            startActivity(intent);
        }
        proceedToNextScreen();
    }

    private void proceedToNextScreen() {
        if (isFinishing()) return;

        SharedPrefsManager prefsManager = SharedPrefsManager.getInstance(this);
        Intent intent;
        if (prefsManager.isLogin()) {
            intent = new Intent(this, MainActivity.class);
        } else {
            intent = new Intent(this, LoginActivity.class);
        }
        intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
        startActivity(intent);
        finish();
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        if (handler != null) {
            handler.removeCallbacksAndMessages(null);
        }
    }

    @Override
    protected void onSaveInstanceState(Bundle outState) {
        super.onSaveInstanceState(outState);
        outState.putBoolean("ad_clicked", adClicked);
        outState.putBoolean("skip_clicked", skipClicked);
        outState.putInt("remaining_seconds", remainingSeconds);
    }

    @Override
    protected void onRestoreInstanceState(Bundle savedInstanceState) {
        super.onRestoreInstanceState(savedInstanceState);
        if (savedInstanceState != null) {
            adClicked = savedInstanceState.getBoolean("ad_clicked", false);
            skipClicked = savedInstanceState.getBoolean("skip_clicked", false);
            remainingSeconds = savedInstanceState.getInt("remaining_seconds", 3);
        }
    }
}