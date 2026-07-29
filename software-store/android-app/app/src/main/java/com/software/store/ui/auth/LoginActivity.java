package com.software.store.ui.auth;

import android.os.Bundle;
import android.text.TextUtils;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

import com.google.android.material.tabs.TabLayout;
import com.software.store.R;
import com.software.store.data.remote.RetrofitClient;
import com.software.store.ui.common.MainActivity;
import com.software.store.util.ToastUtils;

import java.util.concurrent.atomic.AtomicInteger;

/**
 * 登录注册 Activity
 * 通过 Tab 切换登录/注册表单
 */
public class LoginActivity extends AppCompatActivity {

    private TabLayout tabLayout;
    private View loginForm;
    private View registerForm;

    // 登录表单控件
    private EditText etLoginUsername;
    private EditText etLoginPassword;
    private Button btnLogin;

    // 注册表单控件
    private EditText etRegUsername;
    private EditText etRegPassword;
    private EditText etRegPhone;
    private EditText etRegEmail;
    private TextView tvGetCode;
    private Button btnRegister;

    /** 验证码倒计时（秒） */
    private static final int CODE_COUNTDOWN = 60;
    private final AtomicInteger codeCount = new AtomicInteger(0);

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        initViews();
        setupListeners();
    }

    /**
     * 初始化视图
     */
    private void initViews() {
        tabLayout = findViewById(R.id.tab_layout);
        loginForm = findViewById(R.id.login_form);
        registerForm = findViewById(R.id.register_form);

        etLoginUsername = findViewById(R.id.et_login_username);
        etLoginPassword = findViewById(R.id.et_login_password);
        btnLogin = findViewById(R.id.btn_login);

        etRegUsername = findViewById(R.id.et_reg_username);
        etRegPassword = findViewById(R.id.et_reg_password);
        etRegPhone = findViewById(R.id.et_reg_phone);
        etRegEmail = findViewById(R.id.et_reg_email);
        tvGetCode = findViewById(R.id.tv_get_code);
        btnRegister = findViewById(R.id.btn_register);

        // 默认显示登录表单
        showLoginForm();

        // Tab 切换
        tabLayout.addTab(tabLayout.newTab().setText(R.string.login_tab));
        tabLayout.addTab(tabLayout.newTab().setText(R.string.register_tab));
        tabLayout.addOnTabSelectedListener(new TabLayout.OnTabSelectedListener() {
            @Override
            public void onTabSelected(TabLayout.Tab tab) {
                if (tab.getPosition() == 0) {
                    showLoginForm();
                } else {
                    showRegisterForm();
                }
            }

            @Override
            public void onTabUnselected(TabLayout.Tab tab) {
            }

            @Override
            public void onTabReselected(TabLayout.Tab tab) {
            }
        });
    }

    /**
     * 设置监听事件
     */
    private void setupListeners() {
        // 登录按钮
        btnLogin.setOnClickListener(v -> doLogin());

        // 注册按钮
        btnRegister.setOnClickListener(v -> doRegister());

        // 获取验证码
        tvGetCode.setOnClickListener(v -> {
            String phone = etRegPhone.getText().toString().trim();
            if (TextUtils.isEmpty(phone)) {
                ToastUtils.showShort("请先输入手机号");
                return;
            }
            startCodeCountdown();
        });
    }

    /**
     * 显示登录表单
     */
    private void showLoginForm() {
        loginForm.setVisibility(View.VISIBLE);
        registerForm.setVisibility(View.GONE);
    }

    /**
     * 显示注册表单
     */
    private void showRegisterForm() {
        loginForm.setVisibility(View.GONE);
        registerForm.setVisibility(View.VISIBLE);
    }

    /**
     * 执行登录
     */
    private void doLogin() {
        String username = etLoginUsername.getText().toString().trim();
        String password = etLoginPassword.getText().toString().trim();

        if (TextUtils.isEmpty(username)) {
            ToastUtils.showShort("请输入用户名");
            return;
        }
        if (TextUtils.isEmpty(password)) {
            ToastUtils.showShort("请输入密码");
            return;
        }

        // TODO: 调用 RetrofitClient.getInstance().getApiService().login(...) 进行网络请求
        // 模拟登录成功
        RetrofitClient.getInstance().setToken("mock_token_" + System.currentTimeMillis());
        ToastUtils.showShort(R.string.login_success);
        startActivity(new android.content.Intent(this, MainActivity.class));
        finish();
    }

    /**
     * 执行注册
     */
    private void doRegister() {
        String username = etRegUsername.getText().toString().trim();
        String password = etRegPassword.getText().toString().trim();
        String phone = etRegPhone.getText().toString().trim();
        String email = etRegEmail.getText().toString().trim();

        if (TextUtils.isEmpty(username)) {
            ToastUtils.showShort("请设置用户名");
            return;
        }
        if (TextUtils.isEmpty(password)) {
            ToastUtils.showShort("请设置密码");
            return;
        }
        if (TextUtils.isEmpty(phone)) {
            ToastUtils.showShort("请输入手机号");
            return;
        }
        if (TextUtils.isEmpty(email)) {
            ToastUtils.showShort("请输入邮箱");
            return;
        }

        // TODO: 调用 ApiService.register() 进行网络请求
        ToastUtils.showShort(R.string.register_success);
        tabLayout.getTabAt(0).select();
    }

    /**
     * 启动验证码倒计时
     */
    private void startCodeCountdown() {
        codeCount.set(CODE_COUNTDOWN);
        tvGetCode.setEnabled(false);
        new Thread(() -> {
            while (codeCount.get() > 0) {
                int remain = codeCount.decrementAndGet();
                runOnUiThread(() -> tvGetCode.setText(remain + "s 后重发"));
                try {
                    Thread.sleep(1000);
                } catch (InterruptedException e) {
                    break;
                }
            }
            runOnUiThread(() -> {
                tvGetCode.setText(R.string.btn_get_code);
                tvGetCode.setEnabled(true);
            });
        }).start();
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        codeCount.set(0);
    }
}
