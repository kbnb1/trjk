package com.software.store.ui.common;

import android.os.Bundle;
import android.text.TextUtils;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

import com.software.store.R;
import com.software.store.databinding.ActivityNoticeDetailBinding;

public class NoticeDetailActivity extends AppCompatActivity {

    public static final String EXTRA_NOTICE_TYPE = "notice_type";
    public static final String EXTRA_NOTICE_CONTENT = "notice_content";
    public static final String EXTRA_NOTICE_TITLE = "notice_title";

    private ActivityNoticeDetailBinding binding;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivityNoticeDetailBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        setupToolbar();
        displayNotice();
    }

    private void setupToolbar() {
        setSupportActionBar(binding.toolbar);
        if (getSupportActionBar() != null) {
            getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        }
        binding.toolbar.setNavigationOnClickListener(v -> finish());
    }

    private void displayNotice() {
        String type = getIntent().getStringExtra(EXTRA_NOTICE_TYPE);
        String content = getIntent().getStringExtra(EXTRA_NOTICE_CONTENT);
        String title = getIntent().getStringExtra(EXTRA_NOTICE_TITLE);

        if (getSupportActionBar() != null) {
            getSupportActionBar().setTitle(
                    !TextUtils.isEmpty(title) ? title : getString(R.string.notice));
        }

        if (!TextUtils.isEmpty(type)) {
            binding.tvNoticeType.setText(type);
            binding.tvNoticeType.setVisibility(TextView.VISIBLE);
        } else {
            binding.tvNoticeType.setVisibility(TextView.GONE);
        }

        if (!TextUtils.isEmpty(content)) {
            binding.tvNoticeContent.setText(content);
        } else {
            binding.tvNoticeContent.setText(R.string.no_data);
        }
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        binding = null;
    }
}