package com.software.store.ui.home;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;
import androidx.viewpager2.widget.ViewPager2;

import com.software.store.R;
import com.software.store.adapter.BannerAdapter;
import com.software.store.adapter.SoftwareAdapter;
import com.software.store.data.model.Banner;
import com.software.store.data.model.Notice;
import com.software.store.data.model.Software;
import com.software.store.util.ToastUtils;

import java.util.ArrayList;
import java.util.List;

/**
 * 首页 Fragment
 * 展示搜索栏、轮播图、滚动公告、公告专区、推荐软件
 */
public class HomeFragment extends Fragment {

    private SwipeRefreshLayout refreshLayout;
    private ViewPager2 bannerPager;
    private TextView tvScrollNotice;
    private RecyclerView rvRecommend;

    private BannerAdapter bannerAdapter;
    private SoftwareAdapter softwareAdapter;

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container,
                             @Nullable Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_home, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        initViews(view);
        loadData();
    }

    /**
     * 初始化视图
     */
    private void initViews(View view) {
        refreshLayout = view.findViewById(R.id.refresh_layout);
        bannerPager = view.findViewById(R.id.vp_banner);
        tvScrollNotice = view.findViewById(R.id.tv_scroll_notice);
        rvRecommend = view.findViewById(R.id.rv_recommend);

        // 轮播图
        bannerAdapter = new BannerAdapter();
        bannerPager.setAdapter(bannerAdapter);

        // 推荐软件列表
        softwareAdapter = new SoftwareAdapter(SoftwareAdapter.STYLE_LIST);
        rvRecommend.setLayoutManager(new LinearLayoutManager(getContext()));
        rvRecommend.setAdapter(softwareAdapter);
        rvRecommend.setNestedScrollingEnabled(false);

        // 下载按钮点击
        softwareAdapter.setOnDownloadClickListener((software, position) ->
                ToastUtils.showShort(R.string.download_start));

        // 下拉刷新
        refreshLayout.setOnRefreshListener(this::loadData);
        refreshLayout.setColorSchemeResources(R.color.primary, R.color.secondary);
    }

    /**
     * 加载数据（示例：本地模拟数据）
     */
    private void loadData() {
        // 模拟轮播图数据
        List<Banner> banners = new ArrayList<>();
        for (int i = 0; i < 3; i++) {
            Banner banner = new Banner();
            banner.setId(i);
            banner.setTitle("效率办公神器合集");
            banner.setSubtitle("10+ 精选应用 · 立即体验");
            banner.setTag("🔥 热门推荐");
            banners.add(banner);
        }
        bannerAdapter.setList(banners);

        // 模拟滚动公告
        tvScrollNotice.setText("🎉 软件库 v3.2 全新上线，新增 VIP 解析功能，立即体验 →");

        // 模拟推荐软件数据
        List<Software> list = new ArrayList<>();
        list.add(createSoftware(1, "极速笔记", "轻量高效的笔记应用，支持云端同步",
                "12.5", "📝", 862000));
        list.add(createSoftware(2, "音乐播放器", "无损音质播放，支持多种音频格式",
                "25.8", "🎵", 1200000));
        list.add(createSoftware(3, "美图相机", "一键美颜，海量滤镜任你选",
                "48.2", "📷", 2300000));
        softwareAdapter.setList(list);

        refreshLayout.setRefreshing(false);
    }

    /**
     * 构造模拟软件数据
     */
    private Software createSoftware(int id, String name, String desc, String size,
                                    String icon, long downloadCount) {
        Software software = new Software();
        software.setId(id);
        software.setName(name);
        software.setDescription(desc);
        software.setSize(size);
        software.setDownloadCount(downloadCount);
        software.setVersion("v3.2.0");
        return software;
    }
}
