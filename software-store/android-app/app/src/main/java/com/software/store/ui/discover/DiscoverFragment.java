package com.software.store.ui.discover;

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
 * 发现 Fragment
 * 宫格式功能入口：VIP解析器、全能播放器、全网VIP影视、直播解析、每日早报、怀旧游戏
 */
public class DiscoverFragment extends Fragment {

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container,
                             @Nullable Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_discover, container, false);
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
        view.findViewById(R.id.cell_vip_parser).setOnClickListener(v ->
                ToastUtils.showShort(R.string.discover_vip_parser));
        view.findViewById(R.id.cell_player).setOnClickListener(v ->
                ToastUtils.showShort(R.string.discover_player));
        view.findViewById(R.id.cell_vip_video).setOnClickListener(v ->
                ToastUtils.showShort(R.string.discover_vip_video));
        view.findViewById(R.id.cell_live).setOnClickListener(v ->
                ToastUtils.showShort(R.string.discover_live));
        view.findViewById(R.id.cell_news).setOnClickListener(v ->
                ToastUtils.showShort(R.string.discover_news));
        view.findViewById(R.id.cell_game).setOnClickListener(v ->
                ToastUtils.showShort(R.string.discover_game));

        // VIP 会员横幅
        view.findViewById(R.id.vip_banner).setOnClickListener(v ->
                ToastUtils.showShort("开通 VIP 会员"));

        // 热门活动
        view.findViewById(R.id.card_gift).setOnClickListener(v ->
                ToastUtils.showShort(R.string.btn_get));
        view.findViewById(R.id.card_sign).setOnClickListener(v ->
                ToastUtils.showShort(R.string.btn_sign));
    }
}
