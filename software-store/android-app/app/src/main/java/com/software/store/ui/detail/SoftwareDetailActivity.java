package com.software.store.ui.detail;

import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.software.store.R;
import com.software.store.adapter.SoftwareAdapter;
import com.software.store.data.model.Software;
import com.software.store.util.ToastUtils;

import java.util.ArrayList;
import java.util.List;

/**
 * 软件详情 Activity
 * 展示软件信息、下载按钮、软件介绍、使用须知、应用推荐
 */
public class SoftwareDetailActivity extends AppCompatActivity {

    /** Intent extra 键：软件 ID */
    public static final String EXTRA_SOFTWARE_ID = "extra_software_id";

    private ImageView ivBack;
    private ImageView ivShare;
    private ImageView ivIcon;
    private TextView tvName;
    private TextView tvVersion;
    private TextView tvSize;
    private TextView tvDownloadCount;
    private TextView tvRating;
    private TextView tvUpdateTime;
    private TextView tvIntro;
    private TextView tvNotice;
    private Button btnDownload;
    private RecyclerView rvRecommend;

    private SoftwareAdapter recommendAdapter;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_software_detail);

        initViews();
        setupListeners();
        loadDetail();
    }

    /**
     * 初始化视图
     */
    private void initViews() {
        ivBack = findViewById(R.id.iv_back);
        ivShare = findViewById(R.id.iv_share);
        ivIcon = findViewById(R.id.iv_icon);
        tvName = findViewById(R.id.tv_name);
        tvVersion = findViewById(R.id.tv_version);
        tvSize = findViewById(R.id.tv_size);
        tvDownloadCount = findViewById(R.id.tv_download_count);
        tvRating = findViewById(R.id.tv_rating);
        tvUpdateTime = findViewById(R.id.tv_update_time);
        tvIntro = findViewById(R.id.tv_intro);
        tvNotice = findViewById(R.id.tv_notice);
        btnDownload = findViewById(R.id.btn_download);
        rvRecommend = findViewById(R.id.rv_recommend);

        // 应用推荐横向滚动
        recommendAdapter = new SoftwareAdapter(SoftwareAdapter.STYLE_RECOMMEND);
        rvRecommend.setLayoutManager(new LinearLayoutManager(this,
                LinearLayoutManager.HORIZONTAL, false));
        rvRecommend.setAdapter(recommendAdapter);
        recommendAdapter.setOnDownloadClickListener((software, position) ->
                ToastUtils.showShort(R.string.download_start));
    }

    /**
     * 设置监听事件
     */
    private void setupListeners() {
        ivBack.setOnClickListener(v -> finish());
        ivShare.setOnClickListener(v -> ToastUtils.showShort(R.string.detail_share));
        btnDownload.setOnClickListener(v -> ToastUtils.showShort(R.string.download_start));
    }

    /**
     * 加载软件详情（示例：本地模拟数据）
     */
    private void loadDetail() {
        // 模拟详情数据
        Software software = new Software();
        software.setId(1);
        software.setName("影视大全");
        software.setVersion("v3.2.0");
        software.setSize("38.6");
        software.setDownloadCount(5280000);
        software.setRating(4.8f);
        software.setUpdateTime("2026-03-12");
        software.setCategoryName("影音");
        software.setIntro("影视大全是一款汇聚全网优质影视资源的应用，涵盖电影、电视剧、综艺、动漫等内容。"
                + "支持 VIP 解析、离线缓存、多清晰度切换，让您随时随地畅享精彩影视。"
                + "无广告打扰，更新速度快，是您观影的不二之选。");
        software.setNotice("1. 本应用所有内容均来自互联网，仅供学习交流使用；\n"
                + "2. 请勿用于商业用途，下载后 24 小时内删除；\n"
                + "3. 如有侵权，请联系管理员删除。");

        // 填充视图
        tvName.setText(software.getName());
        tvVersion.setText(software.getVersion());
        tvSize.setText(software.getSize() + "MB · " + software.getCategoryName());
        tvDownloadCount.setText(software.getFormatDownloadCount());
        tvRating.setText(String.valueOf(software.getRating()));
        tvUpdateTime.setText(software.getUpdateTime());
        tvIntro.setText(software.getIntro());
        tvNotice.setText(software.getNotice());

        // 模拟推荐数据
        List<Software> recommendList = new ArrayList<>();
        recommendList.add(createSoftware("极速浏览器", "18.2", "🚀"));
        recommendList.add(createSoftware("全能播放器", "22.4", "▶️"));
        recommendList.add(createSoftware("直播TV", "15.8", "📺"));
        recommendList.add(createSoftware("音乐盒", "26.1", "🎵"));
        recommendAdapter.setList(recommendList);
    }

    /**
     * 构造模拟推荐软件
     */
    private Software createSoftware(String name, String size, String icon) {
        Software software = new Software();
        software.setName(name);
        software.setSize(size);
        return software;
    }
}
