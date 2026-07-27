package com.software.store.ui.login;

import android.content.Intent;
import android.os.Bundle;
import android.text.TextUtils;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AppCompatActivity;
import androidx.fragment.app.Fragment;
import androidx.viewpager2.adapter.FragmentStateAdapter;

import com.google.android.material.tabs.TabLayout;
import com.google.android.material.tabs.TabLayoutMediator;
import com.google.android.material.textfield.TextInputEditText;
import com.google.android.material.textfield.TextInputLayout;
import com.software.store.R;
import com.software.store.data.model.Config;
import com.software.store.data.model.User;
import com.software.store.data.repository.HomeRepository;
import com.software.store.data.repository.UserRepository;
import com.software.store.databinding.ActivityLoginBinding;
import com.software.store.ui.common.MainActivity;
import com.software.store.util.SharedPrefsManager;
import com.software.store.util.ToastUtils;

import java.util.HashMap;
import java.util.Map;

public class LoginActivity extends AppCompatActivity {

    private ActivityLoginBinding binding;
    private Config appConfig;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivityLoginBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        setupViewPager();
        loadConfig();
    }

    private void setupViewPager() {
        LoginPagerAdapter adapter = new LoginPagerAdapter(this);
        binding.viewPager.setAdapter(adapter);
        binding.viewPager.setUserInputEnabled(false);

        new TabLayoutMediator(binding.tabLayout, binding.viewPager,
                (tab, position) -> {
                    if (position == 0) {
                        tab.setText(R.string.login);
                    } else {
                        tab.setText(R.string.register);
                    }
                }).attach();
    }

    private void loadConfig() {
        SharedPrefsManager prefsManager = SharedPrefsManager.getInstance(this);
        appConfig = prefsManager.getUserConfig();

        if (appConfig == null) {
            HomeRepository.getInstance().getConfig(new HomeRepository.Callback<Config>() {
                @Override
                public void onSuccess(Config result) {
                    appConfig = result;
                    prefsManager.saveConfig(result);
                }

                @Override
                public void onError(String message) {
                    runOnUiThread(() -> ToastUtils.getInstance().showShort(
                            LoginActivity.this, "无法获取配置信息"));
                }
            });
        }
    }

    public Config getAppConfig() {
        return appConfig;
    }

    private static class LoginPagerAdapter extends FragmentStateAdapter {

        public LoginPagerAdapter(@NonNull LoginActivity activity) {
            super(activity);
        }

        @NonNull
        @Override
        public Fragment createFragment(int position) {
            if (position == 0) {
                return new LoginFormFragment();
            } else {
                return new RegisterFormFragment();
            }
        }

        @Override
        public int getItemCount() {
            return 2;
        }
    }

    public static class LoginFormFragment extends Fragment {

        private TextInputLayout tilUsername;
        private TextInputLayout tilPassword;
        private TextInputEditText etUsername;
        private TextInputEditText etPassword;
        private Button btnLogin;

        @Nullable
        @Override
        public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container,
                                 @Nullable Bundle savedInstanceState) {
            View view = inflater.inflate(R.layout.fragment_login_form, container, false);

            tilUsername = view.findViewById(R.id.til_username);
            tilPassword = view.findViewById(R.id.til_password);
            etUsername = view.findViewById(R.id.et_username);
            etPassword = view.findViewById(R.id.et_password);
            btnLogin = view.findViewById(R.id.btn_login);

            btnLogin.setOnClickListener(v -> attemptLogin());

            return view;
        }

        private void attemptLogin() {
            String username = etUsername.getText() != null ? etUsername.getText().toString().trim() : "";
            String password = etPassword.getText() != null ? etPassword.getText().toString() : "";

            if (TextUtils.isEmpty(username)) {
                tilUsername.setError("请输入用户名");
                return;
            }
            tilUsername.setError(null);

            if (TextUtils.isEmpty(password)) {
                tilPassword.setError("请输入密码");
                return;
            }
            if (password.length() < 6) {
                tilPassword.setError("密码至少6位");
                return;
            }
            tilPassword.setError(null);

            btnLogin.setEnabled(false);
            btnLogin.setText("登录中...");

            UserRepository.getInstance(requireContext()).login(username, password,
                    new UserRepository.Callback<User>() {
                        @Override
                        public void onSuccess(User result) {
                            if (getActivity() == null) return;
                            SharedPrefsManager prefsManager = SharedPrefsManager.getInstance(requireContext());
                            prefsManager.saveUser(result);
                            prefsManager.saveToken(String.valueOf(result.getId()));

                            requireActivity().runOnUiThread(() -> {
                                ToastUtils.getInstance().showShort(requireContext(), "登录成功");
                                Intent intent = new Intent(requireActivity(), MainActivity.class);
                                intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                                startActivity(intent);
                                requireActivity().finish();
                            });
                        }

                        @Override
                        public void onError(String message) {
                            if (getActivity() == null) return;
                            requireActivity().runOnUiThread(() -> {
                                btnLogin.setEnabled(true);
                                btnLogin.setText(R.string.login);
                                ToastUtils.getInstance().showError(requireContext(),
                                        message != null ? message : "登录失败");
                            });
                        }
                    });
        }
    }

    public static class RegisterFormFragment extends Fragment {

        private TextInputLayout tilUsername;
        private TextInputLayout tilPassword;
        private TextInputLayout tilConfirmPassword;
        private TextInputLayout tilPhone;
        private TextInputLayout tilEmail;
        private TextInputLayout tilVerifyCode;
        private TextInputEditText etUsername;
        private TextInputEditText etPassword;
        private TextInputEditText etConfirmPassword;
        private TextInputEditText etPhone;
        private TextInputEditText etEmail;
        private TextInputEditText etVerifyCode;
        private TextView tvSendCode;
        private Button btnRegister;

        private Config config;
        private boolean isPhoneVerifyEnabled;
        private boolean isEmailVerifyEnabled;
        private long codeTimestamp = 0;

        @Nullable
        @Override
        public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container,
                                 @Nullable Bundle savedInstanceState) {
            View view = inflater.inflate(R.layout.fragment_register_form, container, false);

            tilUsername = view.findViewById(R.id.til_username);
            tilPassword = view.findViewById(R.id.til_password);
            tilConfirmPassword = view.findViewById(R.id.til_confirm_password);
            tilPhone = view.findViewById(R.id.til_phone);
            tilEmail = view.findViewById(R.id.til_email);
            tilVerifyCode = view.findViewById(R.id.til_verify_code);
            etUsername = view.findViewById(R.id.et_username);
            etPassword = view.findViewById(R.id.et_password);
            etConfirmPassword = view.findViewById(R.id.et_confirm_password);
            etPhone = view.findViewById(R.id.et_phone);
            etEmail = view.findViewById(R.id.et_email);
            etVerifyCode = view.findViewById(R.id.et_verify_code);
            tvSendCode = view.findViewById(R.id.tv_send_code);
            btnRegister = view.findViewById(R.id.btn_register);

            LoginActivity activity = (LoginActivity) getActivity();
            if (activity != null) {
                config = activity.getAppConfig();
            }

            isPhoneVerifyEnabled = config != null && config.isEnablePhoneVerify();
            isEmailVerifyEnabled = config != null && config.isEnableEmailVerify();

            tilPhone.setVisibility(isPhoneVerifyEnabled ? View.VISIBLE : View.GONE);
            tilEmail.setVisibility(isEmailVerifyEnabled ? View.VISIBLE : View.GONE);

            tvSendCode.setOnClickListener(v -> sendVerificationCode());
            btnRegister.setOnClickListener(v -> attemptRegister());

            return view;
        }

        private void sendVerificationCode() {
            if (System.currentTimeMillis() - codeTimestamp < 60000) {
                long remaining = 60 - (System.currentTimeMillis() - codeTimestamp) / 1000;
                ToastUtils.getInstance().showShort(requireContext(),
                        "请 " + remaining + " 秒后重试");
                return;
            }

            String phone = etPhone.getText() != null ? etPhone.getText().toString().trim() : "";
            String email = etEmail.getText() != null ? etEmail.getText().toString().trim() : "";

            String target = null;
            String type = null;

            if (isPhoneVerifyEnabled && !TextUtils.isEmpty(phone)) {
                if (!isValidPhone(phone)) {
                    tilPhone.setError("手机号格式错误");
                    return;
                }
                target = phone;
                type = "phone";
                tilPhone.setError(null);
            } else if (isEmailVerifyEnabled && !TextUtils.isEmpty(email)) {
                if (!isValidEmail(email)) {
                    tilEmail.setError("邮箱格式错误");
                    return;
                }
                target = email;
                type = "email";
                tilEmail.setError(null);
            } else {
                ToastUtils.getInstance().showShort(requireContext(), "请先填写手机号或邮箱");
                return;
            }

            UserRepository.getInstance(requireContext()).sendCode(target, type,
                    new UserRepository.Callback<Void>() {
                        @Override
                        public void onSuccess(Void result) {
                            if (getActivity() == null) return;
                            codeTimestamp = System.currentTimeMillis();
                            requireActivity().runOnUiThread(() -> {
                                ToastUtils.getInstance().showSuccess(requireContext(),
                                        "验证码已发送");
                                startCountdown();
                            });
                        }

                        @Override
                        public void onError(String message) {
                            if (getActivity() == null) return;
                            requireActivity().runOnUiThread(() -> {
                                ToastUtils.getInstance().showError(requireContext(),
                                        message != null ? message : "验证码发送失败");
                            });
                        }
                    });
        }

        private void startCountdown() {
            if (!isAdded()) return;
            new Thread(() -> {
                for (int i = 60; i >= 0; i--) {
                    if (!isAdded()) return;
                    int finalI = i;
                    requireActivity().runOnUiThread(() -> {
                        if (tvSendCode != null) {
                            if (finalI > 0) {
                                tvSendCode.setEnabled(false);
                                tvSendCode.setText(finalI + "s 后重发");
                            } else {
                                tvSendCode.setEnabled(true);
                                tvSendCode.setText("获取验证码");
                            }
                        }
                    });
                    try {
                        Thread.sleep(1000);
                    } catch (InterruptedException e) {
                        break;
                    }
                }
            }).start();
        }

        private void attemptRegister() {
            String username = etUsername.getText() != null ? etUsername.getText().toString().trim() : "";
            String password = etPassword.getText() != null ? etPassword.getText().toString() : "";
            String confirmPassword = etConfirmPassword.getText() != null ? etConfirmPassword.getText().toString() : "";
            String phone = etPhone.getText() != null ? etPhone.getText().toString().trim() : "";
            String email = etEmail.getText() != null ? etEmail.getText().toString().trim() : "";
            String code = etVerifyCode.getText() != null ? etVerifyCode.getText().toString().trim() : "";

            if (TextUtils.isEmpty(username)) {
                tilUsername.setError("请输入用户名");
                return;
            }
            tilUsername.setError(null);

            if (TextUtils.isEmpty(password)) {
                tilPassword.setError("请输入密码");
                return;
            }
            if (password.length() < 6) {
                tilPassword.setError("密码至少6位");
                return;
            }
            tilPassword.setError(null);

            if (!password.equals(confirmPassword)) {
                tilConfirmPassword.setError("两次密码不一致");
                return;
            }
            tilConfirmPassword.setError(null);

            if (isPhoneVerifyEnabled) {
                if (TextUtils.isEmpty(phone)) {
                    tilPhone.setError("请输入手机号");
                    return;
                }
                if (!isValidPhone(phone)) {
                    tilPhone.setError("手机号格式错误");
                    return;
                }
                if (TextUtils.isEmpty(code)) {
                    tilVerifyCode.setError("请输入验证码");
                    return;
                }
                tilPhone.setError(null);
            }

            if (isEmailVerifyEnabled) {
                if (TextUtils.isEmpty(email)) {
                    tilEmail.setError("请输入邮箱");
                    return;
                }
                if (!isValidEmail(email)) {
                    tilEmail.setError("邮箱格式错误");
                    return;
                }
                tilEmail.setError(null);
            }

            btnRegister.setEnabled(false);
            btnRegister.setText("注册中...");

            String phoneParam = isPhoneVerifyEnabled ? phone : null;
            String emailParam = isEmailVerifyEnabled ? email : null;
            String codeParam = (!TextUtils.isEmpty(code) && isPhoneVerifyEnabled) ? code : null;

            UserRepository.getInstance(requireContext())
                    .register(username, password, emailParam, phoneParam, codeParam,
                            new UserRepository.Callback<User>() {
                                @Override
                                public void onSuccess(User result) {
                                    if (getActivity() == null) return;
                                    SharedPrefsManager prefsManager = SharedPrefsManager.getInstance(requireContext());
                                    prefsManager.saveUser(result);
                                    prefsManager.saveToken(String.valueOf(result.getId()));

                                    requireActivity().runOnUiThread(() -> {
                                        ToastUtils.getInstance().showSuccess(
                                                requireContext(), "注册成功");
                                        Intent intent = new Intent(requireActivity(), MainActivity.class);
                                        intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK
                                                | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                                        startActivity(intent);
                                        requireActivity().finish();
                                    });
                                }

                                @Override
                                public void onError(String message) {
                                    if (getActivity() == null) return;
                                    requireActivity().runOnUiThread(() -> {
                                        btnRegister.setEnabled(true);
                                        btnRegister.setText(R.string.register);
                                        ToastUtils.getInstance().showError(requireContext(),
                                                message != null ? message : "注册失败");
                                    });
                                }
                            });
        }

        private boolean isValidPhone(String phone) {
            return phone != null && phone.matches("^1[3-9]\\d{9}$");
        }

        private boolean isValidEmail(String email) {
            return email != null && android.util.Patterns.EMAIL_ADDRESS.matcher(email).matches();
        }
    }
}