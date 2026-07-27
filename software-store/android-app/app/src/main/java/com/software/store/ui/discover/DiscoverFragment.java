package com.software.store.ui.discover;

import android.content.Intent;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.core.content.ContextCompat;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.GridLayoutManager;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;

import com.software.store.R;
import com.software.store.ui.software.SoftwareDetailActivity;
import com.software.store.ui.common.WebViewActivity;
import com.software.store.adapter.SoftwareAdapter;
import com.software.store.adapter.ToolbarAdapter;
import com.software.store.data.model.Software;
import com.software.store.data.model.Toolbar;
import com.software.store.data.repository.HomeRepository;
import com.software.store.databinding.FragmentDiscoverBinding;
import com.software.store.util.DisplayUtils;
import com.software.store.util.NetworkUtils;
import com.software.store.util.ToastUtils;

import java.util.List;

public class DiscoverFragment extends Fragment {

    private FragmentDiscoverBinding binding;
    private ToolbarAdapter toolbarAdapter;
    private SoftwareAdapter recommendAdapter;
    private HomeRepository repository;

    private RecyclerView toolbarRecyclerView;
    private SwipeRefreshLayout swipeRefresh;
    private ProgressBar progressCenter;
    private LinearLayout emptyLayout;
    private TextView tvEmpty;
    private LinearLayout errorLayout;

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container,
                             @Nullable Bundle savedInstanceState) {
        binding = FragmentDiscoverBinding.inflate(inflater, container, false);
        return binding.getRoot();
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        repository = HomeRepository.getInstance();
        initViews();
        setupToolbarGrid();
        setupRecommendList();
        setupSwipeRefresh();
        loadToolbar();
    }

    private void initViews() {
        binding.tvDiscoverTitle.setText(R.string.practical_tools);
        binding.tvDiscoverDesc.setText(R.string.slogan);

        toolbarRecyclerView = new RecyclerView(requireContext());
        toolbarRecyclerView.setLayoutParams(new LinearLayout.LayoutParams(
                LinearLayout.LayoutParams.MATCH_PARENT, LinearLayout.LayoutParams.WRAP_CONTENT));
        toolbarRecyclerView.setPadding(
                DisplayUtils.dp2px(requireContext(), 4),
                DisplayUtils.dp2px(requireContext(), 4),
                DisplayUtils.dp2px(requireContext(), 4),
                DisplayUtils.dp2px(requireContext(), 4));

        binding.layoutToolbarGrid.removeView(binding.gvToolbar);
        binding.layoutToolbarGrid.addView(toolbarRecyclerView);

        progressCenter = new ProgressBar(requireContext());
        progressCenter.setIndeterminateTintList(
                ContextCompat.getColorStateList(requireContext(), R.color.primary));
        progressCenter.setLayoutParams(new LinearLayout.LayoutParams(
                DisplayUtils.dp2px(requireContext(), 40), DisplayUtils.dp2px(requireContext(), 40)));
        progressCenter.setVisibility(View.GONE);

        emptyLayout = new LinearLayout(requireContext());
        emptyLayout.setOrientation(LinearLayout.VERTICAL);
        emptyLayout.setGravity(android.view.Gravity.CENTER);
        emptyLayout.setPadding(DisplayUtils.dp2px(requireContext(), 32),
                DisplayUtils.dp2px(requireContext(), 32),
                DisplayUtils.dp2px(requireContext(), 32),
                DisplayUtils.dp2px(requireContext(), 32));
        emptyLayout.setVisibility(View.GONE);
        ImageView ivEmpty = new ImageView(requireContext());
        ivEmpty.setImageResource(R.drawable.ic_info);
        LinearLayout.LayoutParams emptyImgParams = new LinearLayout.LayoutParams(
                DisplayUtils.dp2px(requireContext(), 80), DisplayUtils.dp2px(requireContext(), 80));
        ivEmpty.setLayoutParams(emptyImgParams);
        ivEmpty.setAlpha(0.4f);
        tvEmpty = new TextView(requireContext());
        tvEmpty.setText(R.string.no_data);
        tvEmpty.setTextColor(ContextCompat.getColor(requireContext(), R.color.text_hint));
        tvEmpty.setTextSize(14);
        tvEmpty.setPadding(0, DisplayUtils.dp2px(requireContext(), 12), 0, 0);
        emptyLayout.addView(ivEmpty);
        emptyLayout.addView(tvEmpty);

        errorLayout = new LinearLayout(requireContext());
        errorLayout.setOrientation(LinearLayout.VERTICAL);
        errorLayout.setGravity(android.view.Gravity.CENTER);
        errorLayout.setVisibility(View.GONE);
        ImageView ivError = new ImageView(requireContext());
        ivError.setImageResource(R.drawable.ic_error);
        LinearLayout.LayoutParams errImgParams = new LinearLayout.LayoutParams(
                DisplayUtils.dp2px(requireContext(), 60), DisplayUtils.dp2px(requireContext(), 60));
        ivError.setLayoutParams(errImgParams);
        TextView tvError = new TextView(requireContext());
        tvError.setText(R.string.network_error);
        tvError.setTextColor(ContextCompat.getColor(requireContext(), R.color.text_hint));
        tvError.setTextSize(14);
        tvError.setPadding(0, DisplayUtils.dp2px(requireContext(), 8), 0, 0);
        errorLayout.addView(ivError);
        errorLayout.addView(tvError);
        errorLayout.setOnClickListener(v -> loadToolbar());

        LinearLayout container = (LinearLayout) binding.getRoot();
        container.addView(progressCenter);
        container.addView(emptyLayout);
        container.addView(errorLayout);

        binding.tvViewAll.setOnClickListener(v -> {
            Intent intent = new Intent(requireContext(), SoftwareDetailActivity.class);
            startActivity(intent);
        });
    }

    private void setupToolbarGrid() {
        toolbarAdapter = new ToolbarAdapter();
        toolbarAdapter.setOnItemClickListener((item, position) -> {
            if (item.getLink() != null && !item.getLink().isEmpty()) {
                Intent intent = new Intent(requireContext(), WebViewActivity.class);
                intent.putExtra("url", item.getLink());
                intent.putExtra("title", item.getName());
                startActivity(intent);
            } else {
                ToastUtils.getInstance().showShort(requireContext(),
                        getString(R.string.no_data));
            }
        });

        GridLayoutManager layoutManager = new GridLayoutManager(requireContext(), 2);
        toolbarRecyclerView.setLayoutManager(layoutManager);
        toolbarRecyclerView.setAdapter(toolbarAdapter);
    }

    private void setupRecommendList() {
        recommendAdapter = new SoftwareAdapter();
        recommendAdapter.setOnItemClickListener((software, position) -> {
            Intent intent = new Intent(requireContext(), SoftwareDetailActivity.class);
            intent.putExtra("software_id", software.getId());
            startActivity(intent);
        });
        binding.rvRecommend.setLayoutManager(new LinearLayoutManager(requireContext()));
        binding.rvRecommend.setAdapter(recommendAdapter);
    }

    private void setupSwipeRefresh() {
        swipeRefresh = new SwipeRefreshLayout(requireContext());
        swipeRefresh.setLayoutParams(new LinearLayout.LayoutParams(
                LinearLayout.LayoutParams.MATCH_PARENT, LinearLayout.LayoutParams.MATCH_PARENT));
        swipeRefresh.setColorSchemeResources(R.color.primary);

        View originalRoot = binding.getRoot();
        if (originalRoot instanceof LinearLayout) {
            LinearLayout originalLayout = (LinearLayout) originalRoot;
            originalLayout.removeAllViews();
            swipeRefresh.addView(originalLayout);

            LinearLayout parent = (LinearLayout) originalLayout.getParent();
            if (parent != null) {
                int index = parent.indexOfChild(originalLayout);
                parent.removeView(originalLayout);
                parent.addView(swipeRefresh, index);
            }
        }

        swipeRefresh.setOnRefreshListener(this::loadToolbar);
    }

    private void loadToolbar() {
        showLoading();
        swipeRefresh.setRefreshing(false);

        if (!NetworkUtils.isNetworkAvailable(requireContext())) {
            showError(getString(R.string.network_error));
            return;
        }

        repository.getToolbar(new HomeRepository.Callback<List<Toolbar>>() {
            @Override
            public void onSuccess(List<Toolbar> result) {
                if (!isAdded()) return;
                hideLoading();
                if (result == null || result.isEmpty()) {
                    showEmpty();
                } else {
                    hideEmpty();
                    toolbarAdapter.setData(result);
                }
            }

            @Override
            public void onError(String message) {
                if (!isAdded()) return;
                hideLoading();
                showError(message != null ? message : getString(R.string.network_error));
            }
        });
    }

    private void showLoading() {
        if (progressCenter != null) {
            progressCenter.setVisibility(View.VISIBLE);
        }
        emptyLayout.setVisibility(View.GONE);
        errorLayout.setVisibility(View.GONE);
    }

    private void hideLoading() {
        if (progressCenter != null) {
            progressCenter.setVisibility(View.GONE);
        }
    }

    private void showEmpty() {
        hideLoading();
        emptyLayout.setVisibility(View.VISIBLE);
        errorLayout.setVisibility(View.GONE);
    }

    private void hideEmpty() {
        emptyLayout.setVisibility(View.GONE);
    }

    private void showError(String message) {
        hideLoading();
        errorLayout.setVisibility(View.VISIBLE);
        ToastUtils.getInstance().showError(requireContext(), message);
    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        binding = null;
    }
}