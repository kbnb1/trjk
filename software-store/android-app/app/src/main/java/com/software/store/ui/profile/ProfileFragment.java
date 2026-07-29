package com.software.store.ui.profile;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;

import com.software.store.R;
import com.software.store.util.ToastUtils;

/**
 * 个人中心 Fragment
 * 用户信息区 + 快捷入口 + 更多选项 + 退出登录
 */
public class ProfileFragment extends Fragment {

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container,
                             @Nullable Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_profile, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        initViews(view);
    }

    /**
     * 初始化视图与点击事件
     */
    private void initViews(View view) {
        // 快捷入口
        view.findViewById(R.id.quick_address).setOnClickListener(v ->
                ToastUtils.showShort(R.string.profile_address));
        view.findViewById(R.id.quick_business).setOnClickListener(v ->
                ToastUtils.showShort(R.string.profile_business));
        view.findViewById(R.id.quick_feedback).setOnClickListener(v ->
                ToastUtils.showShort(R.string.profile_feedback));
        view.findViewById(R.id.quick_group).setOnClickListener(v ->
                ToastUtils.showShort(R.string.profile_group));

        // 更多选项
        view.findViewById(R.id.menu_download).setOnClickListener(v ->
                ToastUtils.showShort(R.string.profile_download));
        view.findViewById(R.id.menu_share).setOnClickListener(v ->
                ToastUtils.showShort(R.string.profile_share));
        view.findViewById(R.id.menu_cache).setOnClickListener(v ->
                ToastUtils.showShort(R.string.profile_cache));
        view.findViewById(R.id.menu_about).setOnClickListener(v ->
                ToastUtils.showShort(R.string.profile_about));

        // 退出登录
        view.findViewById(R.id.btn_logout).setOnClickListener(v -> {
            ToastUtils.showShort(R.string.logout_success);
            // TODO: 清除登录状态并跳转登录页
        });
    }
}
