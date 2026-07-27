package com.software.store.ui.software;

import android.content.Intent;
import android.os.Bundle;
import android.os.Parcelable;
import android.text.TextUtils;
import android.view.View;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.LinearLayoutManager;

import com.bumptech.glide.Glide;
import com.software.store.R;
import com.software.store.adapter.SoftwareRelatedAdapter;
import com.software.store.data.model.Software;
import com.software.store.data.repository.SoftwareRepository;
import com.software.store.data.repository.UserRepository;
import com.software.store.databinding.ActivitySoftwareDetailBinding;
import com.software.store.util.FileUtils;
import com.software.store.util.SharedPrefsManager;
import com.software.store.util.ToastUtils;

import java.util.List;

public class SoftwareDetailActivity extends AppCompatActivity {

    public static final String EXTRA_SOFTWARE_ID = "software_id";
    public static final String EXTRA_SOFTWARE = "software";

    private ActivitySoftwareDetailBinding binding;
    private Software currentSoftware;
    private int softwareId;
    private SoftwareRelatedAdapter relatedAdapter;
    private boolean isFavorite = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivitySoftwareDetailBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        setSupportActionBar(binding.toolbar);
        if (getSupportActionBar() != null) {
            getSupportActionBar().setDisplayHomeAsUpEnabled(true);
            getSupportActionBar().setDisplayShowHomeEnabled(true);
            getSupportActionBar().setTitle(R.string.software_detail);
        }

        binding.toolbar.setNavigationOnClickListener(v -> finish());

        setupRecyclerView();
        parseIntent();
        loadSoftwareDetail();

        binding.ivFavorite.setOnClickListener(v -> toggleFavorite());
        binding.ivShare.setOnClickListener(v -> shareSoftware());
        binding.btnDownload.setOnClickListener(v -> startDownload());
    }

    private void setupRecyclerView() {
        relatedAdapter = new SoftwareRelatedAdapter();
        relatedAdapter.setOnItemClickListener((software, position) -> {
            Intent intent = new Intent(this, SoftwareDetailActivity.class);
            intent.putExtra(EXTRA_SOFTWARE_ID, software.getId());
            startActivity(intent);
        });
        binding.rvRelated.setLayoutManager(new LinearLayoutManager(this));
        binding.rvRelated.setAdapter(relatedAdapter);
    }

    private void parseIntent() {
        softwareId = getIntent().getIntExtra(EXTRA_SOFTWARE_ID, -1);
        if (softwareId == -1) {
            Software software = getIntent().getParcelableExtra(EXTRA_SOFTWARE);
            if (software != null) {
                currentSoftware = software;
                softwareId = software.getId();
                bindSoftwareData();
            } else {
                ToastUtils.getInstance().showShort(this, "参数错误");
                finish();
            }
        }
    }

    private void loadSoftwareDetail() {
        if (softwareId == -1) return;

        if (currentSoftware != null) {
            bindSoftwareData();
        }

        SoftwareRepository.getInstance().getSoftwareDetail(softwareId,
                new SoftwareRepository.Callback<Software>() {
                    @Override
                    public void onSuccess(Software result) {
                        currentSoftware = result;
                        runOnUiThread(() -> {
                            bindSoftwareData();
                            loadRelatedSoftware();
                        });
                    }

                    @Override
                    public void onError(String message) {
                        runOnUiThread(() -> ToastUtils.getInstance().showError(
                                SoftwareDetailActivity.this,
                                message != null ? message : "加载失败"));
                    }
                });
    }

    private void bindSoftwareData() {
        if (currentSoftware == null) return;

        Glide.with(this)
                .load(currentSoftware.getIcon())
                .placeholder(R.drawable.ic_software)
                .error(R.drawable.ic_software)
                .centerCrop()
                .into(binding.ivAppIcon);

        binding.tvAppName.setText(currentSoftware.getName());

        String version = getString(R.string.version_name) + " " + currentSoftware.getVersion();
        binding.tvVersion.setText(version);

        String size = currentSoftware.getSize();
        String sizeStr;
        if (size == null || size.isEmpty()) {
            sizeStr = getString(R.string.file_size) + " " + FileUtils.formatFileSize(0);
        } else {
            try {
                long sizeBytes = Long.parseLong(size);
                sizeStr = getString(R.string.file_size) + " " + FileUtils.formatFileSize(sizeBytes);
            } catch (NumberFormatException e) {
                sizeStr = getString(R.string.file_size) + " " + size;
            }
        }
        binding.tvSize.setText(sizeStr);

        binding.tvDownloadCount.setText(String.valueOf(currentSoftware.getDownloadCount()));
        binding.tvUpdateDate.setText(FileUtils.formatDate(currentSoftware.getUpdatedAt()));
        binding.tvDeveloper.setText(currentSoftware.getCategoryName() != null
                ? currentSoftware.getCategoryName() : "官方");

        if (!TextUtils.isEmpty(currentSoftware.getDescription())) {
            binding.tvIntroContent.setText(currentSoftware.getDescription());
        } else {
            binding.tvIntroContent.setText("暂无介绍");
        }

        isFavorite = currentSoftware.isFavorite();
        updateFavoriteIcon();
    }

    private void loadRelatedSoftware() {
        SoftwareRepository.getInstance().getRelatedSoftware(softwareId,
                new SoftwareRepository.Callback<List<Software>>() {
                    @Override
                    public void onSuccess(List<Software> result) {
                        runOnUiThread(() -> relatedAdapter.setData(result));
                    }

                    @Override
                    public void onError(String message) {
                        runOnUiThread(() -> relatedAdapter.setData(null));
                    }
                });
    }

    private void updateFavoriteIcon() {
        if (isFavorite) {
            binding.ivFavorite.setImageResource(R.drawable.ic_star);
            binding.ivFavorite.setColorFilter(
                    ContextCompat.getColor(this, R.color.accent_orange));
        } else {
            binding.ivFavorite.setImageResource(R.drawable.ic_star_border);
            binding.ivFavorite.setColorFilter(
                    ContextCompat.getColor(this, R.color.text_hint));
        }
    }

    private void toggleFavorite() {
        if (!SharedPrefsManager.getInstance(this).isLogin()) {
            ToastUtils.getInstance().showShort(this, "请先登录");
            return;
        }

        UserRepository.getInstance(this).toggleFavorite(softwareId,
                new UserRepository.Callback<Void>() {
                    @Override
                    public void onSuccess(Void result) {
                        isFavorite = !isFavorite;
                        if (currentSoftware != null) {
                            currentSoftware.setFavorite(isFavorite);
                        }
                        runOnUiThread(() -> {
                            updateFavoriteIcon();
                            ToastUtils.getInstance().showShort(SoftwareDetailActivity.this,
                                    isFavorite ? getString(R.string.favorite_success)
                                            : getString(R.string.cancel_favorite));
                        });
                    }

                    @Override
                    public void onError(String message) {
                        runOnUiThread(() -> ToastUtils.getInstance().showError(
                                SoftwareDetailActivity.this,
                                message != null ? message : "操作失败"));
                    }
                });
    }

    private void shareSoftware() {
        if (currentSoftware == null) return;

        Intent shareIntent = new Intent(Intent.ACTION_SEND);
        shareIntent.setType("text/plain");
        String shareText = getString(R.string.app_name) + " - " + currentSoftware.getName()
                + "\n版本: " + currentSoftware.getVersion()
                + "\n下载体验吧！";
        shareIntent.putExtra(Intent.EXTRA_SUBJECT, currentSoftware.getName());
        shareIntent.putExtra(Intent.EXTRA_TEXT, shareText);
        startActivity(Intent.createChooser(shareIntent, getString(R.string.share)));
    }

    private void startDownload() {
        if (currentSoftware == null) return;

        if (!SharedPrefsManager.getInstance(this).isLogin()) {
            ToastUtils.getInstance().showShort(this, "请先登录后再下载");
            return;
        }

        ToastUtils.getInstance().showShort(this, "开始下载 " + currentSoftware.getName());

        SoftwareRepository.getInstance().startDownload(softwareId,
                new SoftwareRepository.Callback<com.software.store.data.model.DownloadRecord>() {
                    @Override
                    public void onSuccess(com.software.store.data.model.DownloadRecord result) {
                        runOnUiThread(() -> ToastUtils.getInstance().showSuccess(
                                SoftwareDetailActivity.this, "下载已开始"));
                    }

                    @Override
                    public void onError(String message) {
                        runOnUiThread(() -> ToastUtils.getInstance().showError(
                                SoftwareDetailActivity.this,
                                message != null ? message : "下载失败"));
                    }
                });
    }

    @Override
    protected void onSaveInstanceState(@NonNull Bundle outState) {
        super.onSaveInstanceState(outState);
        outState.putInt("software_id", softwareId);
        outState.putParcelable("software", currentSoftware);
    }

    @Override
    protected void onRestoreInstanceState(@NonNull Bundle savedInstanceState) {
        super.onRestoreInstanceState(savedInstanceState);
        if (savedInstanceState != null) {
            softwareId = savedInstanceState.getInt("software_id", -1);
            currentSoftware = savedInstanceState.getParcelable("software");
            if (currentSoftware != null) {
                bindSoftwareData();
            }
        }
    }
}