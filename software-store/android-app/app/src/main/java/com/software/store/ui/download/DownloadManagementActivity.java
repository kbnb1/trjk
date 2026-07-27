package com.software.store.ui.download;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;

import com.software.store.R;
import com.software.store.adapter.DownloadRecordAdapter;
import com.software.store.data.model.DownloadRecord;
import com.software.store.data.repository.SoftwareRepository;
import com.software.store.databinding.ActivityDownloadManagementBinding;
import com.software.store.ui.software.SoftwareDetailActivity;
import com.software.store.util.ToastUtils;

import java.util.ArrayList;
import java.util.List;

public class DownloadManagementActivity extends AppCompatActivity {

    private ActivityDownloadManagementBinding binding;
    private DownloadRecordAdapter adapter;
    private List<DownloadRecord> downloadList = new ArrayList<>();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivityDownloadManagementBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        setupToolbar();
        setupRecyclerView();
        setupSwipeRefresh();
        loadDownloads();
    }

    private void setupToolbar() {
        setSupportActionBar(binding.toolbar);
        if (getSupportActionBar() != null) {
            getSupportActionBar().setDisplayHomeAsUpEnabled(true);
            getSupportActionBar().setDisplayShowHomeEnabled(true);
            getSupportActionBar().setTitle(R.string.download_manage);
        }
        binding.toolbar.setNavigationOnClickListener(v -> finish());
    }

    private void setupRecyclerView() {
        adapter = new DownloadRecordAdapter();
        adapter.setOnItemClickListener((record, position) -> {
            Intent intent = new Intent(this, SoftwareDetailActivity.class);
            intent.putExtra(SoftwareDetailActivity.EXTRA_SOFTWARE_ID, record.getSoftwareId());
            startActivity(intent);
        });
        adapter.setOnActionClickListener((record, position) -> {
            handleActionClick(record, position);
        });

        binding.rvDownloads.setLayoutManager(new LinearLayoutManager(this));
        binding.rvDownloads.setAdapter(adapter);
    }

    private void setupSwipeRefresh() {
        binding.swipeRefresh.setColorSchemeResources(
                R.color.primary,
                R.color.accent_orange);
        binding.swipeRefresh.setOnRefreshListener(this::loadDownloads);
    }

    private void loadDownloads() {
        binding.swipeRefresh.setRefreshing(true);

        SoftwareRepository.getInstance().getDownloads(
                new SoftwareRepository.Callback<List<DownloadRecord>>() {
                    @Override
                    public void onSuccess(List<DownloadRecord> result) {
                        runOnUiThread(() -> {
                            binding.swipeRefresh.setRefreshing(false);
                            downloadList = result != null ? result : new ArrayList<>();
                            adapter.setData(downloadList);
                            updateEmptyState();
                        });
                    }

                    @Override
                    public void onError(String message) {
                        runOnUiThread(() -> {
                            binding.swipeRefresh.setRefreshing(false);
                            adapter.setData(null);
                            updateEmptyState();
                            ToastUtils.getInstance().showError(
                                    DownloadManagementActivity.this,
                                    message != null ? message : "加载下载列表失败");
                        });
                    }
                });
    }

    private void updateEmptyState() {
        if (downloadList.isEmpty()) {
            binding.rvDownloads.setVisibility(View.GONE);
            binding.layoutEmpty.setVisibility(View.VISIBLE);
        } else {
            binding.rvDownloads.setVisibility(View.VISIBLE);
            binding.layoutEmpty.setVisibility(View.GONE);
        }
    }

    private void handleActionClick(DownloadRecord record, int position) {
        int status = record.getStatus();
        switch (status) {
            case DownloadRecord.STATUS_DOWNLOADING:
                ToastUtils.getInstance().showShort(this, "已暂停下载");
                break;
            case DownloadRecord.STATUS_COMPLETED:
                ToastUtils.getInstance().showShort(this, "开始安装 " + record.getSoftwareName());
                break;
            case DownloadRecord.STATUS_FAILED:
                retryDownload(record, position);
                break;
            default:
                ToastUtils.getInstance().showShort(this, "查看详情");
                break;
        }
    }

    private void retryDownload(DownloadRecord record, int position) {
        ToastUtils.getInstance().showShort(this, "正在重试下载...");

        SoftwareRepository.getInstance().startDownload(record.getSoftwareId(),
                new SoftwareRepository.Callback<DownloadRecord>() {
                    @Override
                    public void onSuccess(DownloadRecord result) {
                        runOnUiThread(() -> {
                            if (position >= 0 && position < downloadList.size()) {
                                downloadList.set(position, result);
                                adapter.setData(downloadList);
                            }
                            ToastUtils.getInstance().showSuccess(
                                    DownloadManagementActivity.this, "下载已重新开始");
                        });
                    }

                    @Override
                    public void onError(String message) {
                        runOnUiThread(() -> ToastUtils.getInstance().showError(
                                DownloadManagementActivity.this,
                                message != null ? message : "重试失败"));
                    }
                });
    }

    @Override
    protected void onResume() {
        super.onResume();
        loadDownloads();
    }

    @Override
    protected void onSaveInstanceState(@NonNull Bundle outState) {
        super.onSaveInstanceState(outState);
    }

    @Override
    protected void onRestoreInstanceState(@NonNull Bundle savedInstanceState) {
        super.onRestoreInstanceState(savedInstanceState);
    }
}