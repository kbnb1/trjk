package com.software.store.ui.common;

import android.graphics.Bitmap;
import android.os.Bundle;
import android.view.KeyEvent;
import android.view.View;
import android.webkit.WebChromeClient;
import android.webkit.WebResourceError;
import android.webkit.WebResourceRequest;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;

import androidx.appcompat.app.AppCompatActivity;

import com.software.store.R;
import com.software.store.databinding.ActivityWebViewBinding;
import com.software.store.util.ToastUtils;

public class WebViewActivity extends AppCompatActivity {

    public static final String EXTRA_URL = "url";
    public static final String EXTRA_TITLE = "title";

    private ActivityWebViewBinding binding;
    private String url;
    private String title;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivityWebViewBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        setupToolbar();
        parseIntent();
        setupWebView();
        loadUrl();
    }

    private void setupToolbar() {
        setSupportActionBar(binding.toolbar);
        if (getSupportActionBar() != null) {
            getSupportActionBar().setDisplayHomeAsUpEnabled(true);
            getSupportActionBar().setDisplayShowHomeEnabled(true);
            getSupportActionBar().setTitle("");
        }
        binding.toolbar.setNavigationOnClickListener(v -> finish());
    }

    private void parseIntent() {
        url = getIntent().getStringExtra(EXTRA_URL);
        title = getIntent().getStringExtra(EXTRA_TITLE);

        if (url == null || url.isEmpty()) {
            ToastUtils.getInstance().showShort(this, "链接无效");
            finish();
            return;
        }

        if (getSupportActionBar() != null && title != null && !title.isEmpty()) {
            getSupportActionBar().setTitle(title);
        }
    }

    private void setupWebView() {
        WebSettings settings = binding.webView.getSettings();
        settings.setJavaScriptEnabled(true);
        settings.setDomStorageEnabled(true);
        settings.setLoadsImagesAutomatically(true);
        settings.setUseWideViewPort(true);
        settings.setLoadWithOverviewMode(true);
        settings.setBuiltInZoomControls(true);
        settings.setDisplayZoomControls(false);
        settings.setMixedContentMode(WebSettings.MIXED_CONTENT_ALWAYS_ALLOW);
        settings.setCacheMode(WebSettings.LOAD_DEFAULT);
        settings.setSupportMultipleWindows(false);

        binding.webView.setWebViewClient(new WebViewClient() {
            @Override
            public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
                view.loadUrl(request.getUrl().toString());
                return true;
            }

            @Override
            public void onPageStarted(WebView view, String url, Bitmap favicon) {
                super.onPageStarted(view, url, favicon);
                binding.progressWeb.setVisibility(View.VISIBLE);
                binding.progressWeb.setProgress(0);
            }

            @Override
            public void onPageFinished(WebView view, String url) {
                super.onPageFinished(view, url);
                binding.progressWeb.setVisibility(View.GONE);
                if (getSupportActionBar() != null
                        && (title == null || title.isEmpty())
                        && view.getTitle() != null) {
                    getSupportActionBar().setTitle(view.getTitle());
                }
            }

            @Override
            public void onReceivedError(WebView view, WebResourceRequest request,
                                        WebResourceError error) {
                super.onReceivedError(view, request, error);
                if (request.isForMainFrame()) {
                    binding.progressWeb.setVisibility(View.GONE);
                    ToastUtils.getInstance().showError(WebViewActivity.this, "网页加载失败");
                }
            }
        });

        binding.webView.setWebChromeClient(new WebChromeClient() {
            @Override
            public void onProgressChanged(WebView view, int newProgress) {
                super.onProgressChanged(view, newProgress);
                binding.progressWeb.setProgress(newProgress);
                if (newProgress >= 100) {
                    binding.progressWeb.setVisibility(View.GONE);
                }
            }
        });
    }

    private void loadUrl() {
        if (url != null && !url.isEmpty()) {
            binding.webView.loadUrl(url);
        }
    }

    @Override
    public boolean onKeyDown(int keyCode, KeyEvent event) {
        if (keyCode == KeyEvent.KEYCODE_BACK && binding.webView.canGoBack()) {
            binding.webView.goBack();
            return true;
        }
        return super.onKeyDown(keyCode, event);
    }

    @Override
    protected void onResume() {
        super.onResume();
        binding.webView.onResume();
        binding.webView.resumeTimers();
    }

    @Override
    protected void onPause() {
        super.onPause();
        binding.webView.onPause();
        binding.webView.pauseTimers();
    }

    @Override
    protected void onDestroy() {
        if (binding.webView != null) {
            binding.webView.stopLoading();
            binding.webView.clearHistory();
            binding.webView.removeAllViews();
            binding.webView.destroy();
        }
        super.onDestroy();
    }

    @Override
    protected void onSaveInstanceState(Bundle outState) {
        super.onSaveInstanceState(outState);
        outState.putString("url", url);
        outState.putString("title", title);
        binding.webView.saveState(outState);
    }

    @Override
    protected void onRestoreInstanceState(Bundle savedInstanceState) {
        super.onRestoreInstanceState(savedInstanceState);
        if (savedInstanceState != null) {
            url = savedInstanceState.getString("url");
            title = savedInstanceState.getString("title");
            binding.webView.restoreState(savedInstanceState);
        }
    }
}