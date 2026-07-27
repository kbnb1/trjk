package com.software.store.ui.login;

import android.os.Bundle;
import android.text.TextUtils;
import android.view.View;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import com.software.store.R;
import com.software.store.data.repository.SoftwareRepository;
import com.software.store.databinding.ActivityFeedbackBinding;
import com.software.store.util.NetworkUtils;
import com.software.store.util.ToastUtils;

public class FeedbackActivity extends AppCompatActivity {

    private ActivityFeedbackBinding binding;
    private SoftwareRepository repository;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivityFeedbackBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        repository = SoftwareRepository.getInstance();

        setupToolbar();
        setupSubmit();
    }

    private void setupToolbar() {
        setSupportActionBar(binding.toolbar);
        if (getSupportActionBar() != null) {
            getSupportActionBar().setDisplayHomeAsUpEnabled(true);
            getSupportActionBar().setTitle(R.string.software_feedback);
        }
        binding.toolbar.setNavigationOnClickListener(v -> finish());
    }

    private void setupSubmit() {
        binding.btnSubmit.setOnClickListener(v -> submitFeedback());
    }

    private void submitFeedback() {
        String content = binding.etContent.getText().toString().trim();
        String contact = binding.etContact.getText().toString().trim();

        if (TextUtils.isEmpty(content)) {
            ToastUtils.getInstance().showShort(this, getString(R.string.input_feedback_hint));
            return;
        }

        if (!NetworkUtils.isNetworkAvailable(this)) {
            ToastUtils.getInstance().showError(this, getString(R.string.network_error));
            return;
        }

        setSubmitting(true);

        repository.submitFeedback(content, contact, null, new SoftwareRepository.Callback<Void>() {
            @Override
            public void onSuccess(Void result) {
                runOnUiThread(() -> {
                    setSubmitting(false);
                    ToastUtils.getInstance().showSuccess(FeedbackActivity.this,
                            getString(R.string.feedback_success));
                    finish();
                });
            }

            @Override
            public void onError(String message) {
                runOnUiThread(() -> {
                    setSubmitting(false);
                    ToastUtils.getInstance().showError(FeedbackActivity.this,
                            message != null ? message : getString(R.string.network_error));
                });
            }
        });
    }

    private void setSubmitting(boolean submitting) {
        binding.btnSubmit.setEnabled(!submitting);
        binding.etContent.setEnabled(!submitting);
        binding.etContact.setEnabled(!submitting);
        binding.btnSubmit.setText(submitting ? R.string.submitting : R.string.save);
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        binding = null;
    }
}