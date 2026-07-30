package com.software.store.ui.software;

import android.os.Bundle;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;

import com.google.android.material.chip.Chip;
import com.google.android.material.chip.ChipGroup;
import com.google.android.material.textfield.TextInputEditText;
import com.software.store.R;
import com.software.store.adapter.SoftwareAdapter;
import com.software.store.data.model.Software;
import com.software.store.util.ToastUtils;

import java.util.ArrayList;
import java.util.List;

/**
 * 软件 Fragment
 * 顶部分类标签 + 软件排行列表
 */
public class SoftwareFragment extends Fragment {

    private SwipeRefreshLayout refreshLayout;
    private ChipGroup chipGroup;
    private RecyclerView rvRank;
    private TextInputEditText etSearch;

    private SoftwareAdapter softwareAdapter;

    /** 原始数据列表，保存所有软件，用于搜索过滤 */
    private final List<Software> fullList = new ArrayList<>();

    /** 当前选中的分类 ID */
    private int currentCategoryId = 0;

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container,
                             @Nullable Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_software, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        initViews(view);
        loadRankList();
    }

    /**
     * 初始化视图
     */
    private void initViews(View view) {
        refreshLayout = view.findViewById(R.id.refresh_layout);
        chipGroup = view.findViewById(R.id.chip_group);
        rvRank = view.findViewById(R.id.rv_rank);
        etSearch = view.findViewById(R.id.et_search);

        // 搜索框文字变化监听
        etSearch.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {
                // 无需处理
            }

            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {
                // 无需处理
            }

            @Override
            public void afterTextChanged(Editable s) {
                filterList(s != null ? s.toString() : "");
            }
        });

        // 分类 Chip
        String[] categories = {"全部", "热门", "工具", "游戏", "影音", "教育"};
        for (int i = 0; i < categories.length; i++) {
            Chip chip = new Chip(getContext());
            chip.setText(categories[i]);
            chip.setCheckable(true);
            int finalI = i;
            chip.setOnClickListener(v -> {
                currentCategoryId = finalI;
                loadRankList();
                ToastUtils.showShort("切换到 " + categories[finalI]);
            });
            chipGroup.addView(chip);
        }
        // 默认选中第一个
        chipGroup.check(chipGroup.getChildAt(0).getId());

        // 排行列表
        softwareAdapter = new SoftwareAdapter(SoftwareAdapter.STYLE_RANK);
        rvRank.setLayoutManager(new LinearLayoutManager(getContext()));
        rvRank.setAdapter(softwareAdapter);
        rvRank.setNestedScrollingEnabled(false);

        softwareAdapter.setOnDownloadClickListener((software, position) ->
                ToastUtils.showShort(R.string.download_start));

        refreshLayout.setOnRefreshListener(this::loadRankList);
        refreshLayout.setColorSchemeResources(R.color.primary, R.color.secondary);
    }

    /**
     * 加载排行列表（示例：本地模拟数据）
     */
    private void loadRankList() {
        List<Software> list = new ArrayList<>();
        list.add(createSoftware(1, "影视大全", "全网 VIP 影视免费看，更新快",
                "38.6", "🎬", 5280000, "影音"));
        list.add(createSoftware(2, "极速浏览器", "无广告、极速浏览，安全省流",
                "18.2", "🚀", 4120000, "工具"));
        list.add(createSoftware(3, "休闲游戏合集", "100+ 经典小游戏，离线畅玩",
                "56.4", "🎮", 3050000, "游戏"));
        list.add(createSoftware(4, "学习宝典", "从小学到大学，全覆盖学习资料",
                "42.1", "📚", 1980000, "教育"));
        list.add(createSoftware(5, "清理大师", "深度清理垃圾，加速手机",
                "15.3", "🧹", 1760000, "工具"));

        // 设置排名序号
        for (int i = 0; i < list.size(); i++) {
            list.get(i).setRank(i + 1);
        }

        // 保存原始数据，用于搜索过滤
        fullList.clear();
        fullList.addAll(list);

        // 应用当前搜索关键字
        String keyword = etSearch != null && etSearch.getText() != null
                ? etSearch.getText().toString() : "";
        filterList(keyword);
        refreshLayout.setRefreshing(false);
    }

    /**
     * 根据关键字过滤列表
     * 匹配软件名称和描述，关键字为空时显示全部
     *
     * @param keyword 搜索关键字
     */
    private void filterList(String keyword) {
        List<Software> filtered = new ArrayList<>();
        if (keyword == null || keyword.trim().isEmpty()) {
            // 关键字为空，显示全部
            filtered.addAll(fullList);
        } else {
            String key = keyword.trim().toLowerCase();
            for (Software software : fullList) {
                String name = software.getName() != null
                        ? software.getName().toLowerCase() : "";
                String desc = software.getDescription() != null
                        ? software.getDescription().toLowerCase() : "";
                if (name.contains(key) || desc.contains(key)) {
                    filtered.add(software);
                }
            }
            // 重新设置过滤后的排名序号
            for (int i = 0; i < filtered.size(); i++) {
                filtered.get(i).setRank(i + 1);
            }
        }
        softwareAdapter.setList(filtered);
    }

    /**
     * 构造模拟软件数据
     */
    private Software createSoftware(int id, String name, String desc, String size,
                                    String icon, long downloadCount, String category) {
        Software software = new Software();
        software.setId(id);
        software.setName(name);
        software.setDescription(desc);
        software.setSize(size);
        software.setDownloadCount(downloadCount);
        software.setCategoryName(category);
        software.setVersion("v3.2.0");
        return software;
    }
}
