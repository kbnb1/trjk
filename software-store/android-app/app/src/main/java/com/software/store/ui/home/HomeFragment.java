package com.software.store.ui.home;

import android.content.Intent;
import android.graphics.drawable.GradientDrawable;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.text.TextUtils;
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
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import androidx.viewpager2.widget.ViewPager2;

import com.software.store.R;
import com.software.store.ui.common.WebViewActivity;
import com.software.store.ui.software.SoftwareDetailActivity;
import com.software.store.adapter.BannerAdapter;
import com.software.store.adapter.SoftwareAdapter;
import com.software.store.data.model.Banner;
import com.software.store.data.model.Notice;
import com.software.store.data.model.PageData;
import com.software.store.data.model.Software;
import com.software.store.data.repository.HomeRepository;
import com.software.store.databinding.FragmentHomeBinding;
import com.software.store.ui.common.NoticeDetailActivity;
import com.software.store.util.DisplayUtils;
import com.software.store.util.NetworkUtils;
import com.software.store.util.ToastUtils;

import java.util.ArrayList;
import java.util.List;

public class HomeFragment extends Fragment {

    private FragmentHomeBinding binding;
    private BannerAdapter bannerAdapter;
    private HomeRepository repository;
    private final Handler bannerHandler = new Handler(Looper.getMainLooper());
    private Runnable bannerRunnable;
    private List<Banner> bannerList = new ArrayList<>();
    private List<Software> softwareList = new ArrayList<>();
    private Notice currentNotice;

    private LinearLayout indicatorContainer;
    private TextView tvEmpty;
    private ProgressBar progressBar;
    private View errorLayout;

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container,
                             @Nullable Bundle savedInstanceState) {
        binding = FragmentHomeBinding.inflate(inflater, container, false);
        return binding.getRoot();
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        repository = HomeRepository.getInstance();
        initViews();
        setupBanner();
        setupNoticeTicker();
        setupSoftwareList();
        setupSwipeRefresh();
        setupSearch();
        loadData();
    }

    private void initViews() {
        indicatorContainer = new LinearLayout(requireContext());
        indicatorContainer.setOrientation(LinearLayout.HORIZONTAL);
        indicatorContainer.setGravity(android.view.Gravity.CENTER);
        LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(
                LinearLayout.LayoutParams.WRAP_CONTENT, LinearLayout.LayoutParams.WRAP_CONTENT);
        params.setMargins(0, DisplayUtils.dp2px(requireContext(), 8), 0, 0);
        indicatorContainer.setLayoutParams(params);

        if (binding.getRoot() instanceof LinearLayout) {
            ((LinearLayout) binding.getRoot()).addView(indicatorContainer);
        }

        progressBar = new ProgressBar(requireContext());
        progressBar.setIndeterminateTintList(
                ContextCompat.getColorStateList(requireContext(), R.color.primary));
        progressBar.setVisibility(View.GONE);

        tvEmpty = new TextView(requireContext());
        tvEmpty.setText(R.string.no_data);
        tvEmpty.setTextColor(ContextCompat.getColor(requireContext(), R.color.text_hint));
        tvEmpty.setTextSize(14);
        tvEmpty.setGravity(android.view.Gravity.CENTER);
        tvEmpty.setVisibility(View.GONE);

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
        errorLayout.setOnClickListener(v -> loadData());

        binding.getRoot().addView(progressBar);
        binding.getRoot().addView(tvEmpty);
        binding.getRoot().addView(errorLayout);
    }

    private void setupBanner() {
        bannerAdapter = new BannerAdapter();
        binding.viewPagerBanner.setAdapter(bannerAdapter);
        binding.viewPagerBanner.setOffscreenPageLimit(3);

        binding.viewPagerBanner.registerOnPageChangeCallback(new ViewPager2.OnPageChangeCallback() {
            @Override
            public void onPageSelected(int position) {
                super.onPageSelected(position);
                updateIndicators(position);
            }
        });

        bannerAdapter.setOnItemClickListener((banner, position) -> {
            if (banner.getLink() != null && !banner.getLink().isEmpty()) {
                Intent intent = new Intent(requireContext(), WebViewActivity.class);
                intent.putExtra("url", banner.getLink());
                intent.putExtra("title", banner.getTitle());
                startActivity(intent);
            }
        });
    }

    private void updateIndicators(int selectedPosition) {
        indicatorContainer.removeAllViews();
        if (bannerList.size() <= 1) return;

        for (int i = 0; i < bannerList.size(); i++) {
            View dot = new View(requireContext());
            int size = DisplayUtils.dp2px(requireContext(), 6);
            LinearLayout.LayoutParams dotParams = new LinearLayout.LayoutParams(size, size);
            dotParams.setMargins(DisplayUtils.dp2px(requireContext(), 3), 0,
                    DisplayUtils.dp2px(requireContext(), 3), 0);
            dot.setLayoutParams(dotParams);

            GradientDrawable drawable = new GradientDrawable();
            drawable.setShape(GradientDrawable.OVAL);

            if (i == selectedPosition) {
                drawable.setColor(ContextCompat.getColor(requireContext(), R.color.primary));
                dot.setLayoutParams(new LinearLayout.LayoutParams(
                        DisplayUtils.dp2px(requireContext(), 12), size));
            } else {
                drawable.setColor(ContextCompat.getColor(requireContext(), R.color.divider));
            }
            dot.setBackground(drawable);
            indicatorContainer.addView(dot);
        }
    }

    private void startBannerAutoScroll() {
        if (bannerList.size() <= 1) return;

        stopBannerAutoScroll();

        bannerRunnable = () -> {
            int currentItem = binding.viewPagerBanner.getCurrentItem();
            int nextItem = (currentItem + 1) % bannerList.size();
            binding.viewPagerBanner.setCurrentItem(nextItem, true);
            bannerHandler.postDelayed(bannerRunnable, 3000);
        };
        bannerHandler.postDelayed(bannerRunnable, 3000);
    }

    private void stopBannerAutoScroll() {
        if (bannerRunnable != null) {
            bannerHandler.removeCallbacks(bannerRunnable);
            bannerRunnable = null;
        }
    }

    private void setupNoticeTicker() {
        LinearLayoutManager layoutManager = new LinearLayoutManager(requireContext(),
                LinearLayoutManager.HORIZONTAL, false);
        binding.rvNotice.setLayoutManager(layoutManager);
        binding.rvNotice.setNestedScrollingEnabled(false);

        RecyclerView.Adapter<RecyclerView.ViewHolder> noticeAdapter = new RecyclerView.Adapter<RecyclerView.ViewHolder>() {
            @NonNull
            @Override
            public RecyclerView.ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
                com.software.store.databinding.ItemNoticeTickerBinding itemBinding =
                        com.software.store.databinding.ItemNoticeTickerBinding.inflate(
                                LayoutInflater.from(parent.getContext()), parent, false);
                return new RecyclerView.ViewHolder(itemBinding.getRoot()) {};
            }

            @Override
            public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
                String noticeText = currentNotice != null ? currentNotice.getContent() : "";
                if (!TextUtils.isEmpty(noticeText)) {
                    ((LinearLayout) holder.itemView).removeAllViews();
                    android.widget.TextView tv = new android.widget.TextView(requireContext());
                    tv.setText(noticeText);
                    tv.setTextColor(ContextCompat.getColor(requireContext(), R.color.text_secondary));
                    tv.setTextSize(13);
                    tv.setEllipsize(TextUtils.TruncateAt.END);
                    tv.setMaxLines(1);
                    LinearLayout.LayoutParams lp = new LinearLayout.LayoutParams(
                            LinearLayout.LayoutParams.WRAP_CONTENT, LinearLayout.LayoutParams.WRAP_CONTENT);
                    holder.itemView.setOnClickListener(v -> {
                        Intent intent = new Intent(requireContext(), NoticeDetailActivity.class);
                        intent.putExtra("notice_type", currentNotice != null ? currentNotice.getType() : "");
                        intent.putExtra("notice_content", currentNotice != null ? currentNotice.getContent() : "");
                        intent.putExtra("notice_title", getString(R.string.notice));
                        startActivity(intent);
                    });
                    ((LinearLayout) holder.itemView).addView(tv);
                }
            }

            @Override
            public int getItemCount() {
                return currentNotice != null ? 1 : 0;
            }
        };
        binding.rvNotice.setAdapter(noticeAdapter);
    }

    private void setupSoftwareList() {
        binding.rvSoftwareList.setLayoutManager(new LinearLayoutManager(requireContext()));
        binding.rvSoftwareList.setNestedScrollingEnabled(false);
    }

    private void setupSwipeRefresh() {
        binding.swipeRefresh.setColorSchemeResources(R.color.primary);
        binding.swipeRefresh.setOnRefreshListener(this::loadData);
    }

    private void setupSearch() {
        binding.ivSearch.setOnClickListener(v -> {
            Intent intent = new Intent(requireContext(), SoftwareDetailActivity.class);
            startActivity(intent);
        });
        binding.etSearch.setOnEditorActionListener((v, actionId, event) -> {
            String keyword = binding.etSearch.getText().toString().trim();
            if (!keyword.isEmpty()) {
                Intent intent = new Intent(requireContext(), SoftwareDetailActivity.class);
                intent.putExtra("search_keyword", keyword);
                startActivity(intent);
            }
            return true;
        });
    }

    private void loadData() {
        showLoading();
        binding.swipeRefresh.setRefreshing(false);

        if (!NetworkUtils.isNetworkAvailable(requireContext())) {
            showError(getString(R.string.network_error));
            return;
        }

        repository.getHomeData(new HomeRepository.Callback<PageData>() {
            @Override
            public void onSuccess(PageData result) {
                if (!isAdded()) return;
                hideLoading();
                if (result == null) {
                    showEmpty();
                    return;
                }
                bindData(result);
            }

            @Override
            public void onError(String message) {
                if (!isAdded()) return;
                hideLoading();
                showError(message != null ? message : getString(R.string.network_error));
            }
        });
    }

    private void bindData(PageData data) {
        if (data.getBanners() != null && !data.getBanners().isEmpty()) {
            bannerList = data.getBanners();
            bannerAdapter.setData(bannerList);
            updateIndicators(0);
            startBannerAutoScroll();
        }

        if (data.getNotice() != null) {
            currentNotice = data.getNotice();
            if (binding.rvNotice.getAdapter() != null) {
                binding.rvNotice.getAdapter().notifyDataSetChanged();
            }
        }

        if (data.getSoftware() != null && !data.getSoftware().isEmpty()) {
            softwareList = data.getSoftware();
            SoftwareAdapter adapter = new SoftwareAdapter();
            adapter.setData(softwareList);
            adapter.setOnItemClickListener((software, position) -> {
                Intent intent = new Intent(requireContext(), SoftwareDetailActivity.class);
                intent.putExtra("software_id", software.getId());
                startActivity(intent);
            });
            binding.rvSoftwareList.setAdapter(adapter);
            binding.rvSoftwareList.setVisibility(View.VISIBLE);
        }

        if (bannerList.isEmpty() && softwareList.isEmpty() && currentNotice == null) {
            showEmpty();
        } else {
            hideEmpty();
        }
    }

    private void showLoading() {
        if (progressBar != null) {
            progressBar.setVisibility(View.VISIBLE);
        }
        if (tvEmpty != null) tvEmpty.setVisibility(View.GONE);
        if (errorLayout != null) errorLayout.setVisibility(View.GONE);
    }

    private void hideLoading() {
        if (progressBar != null) {
            progressBar.setVisibility(View.GONE);
        }
    }

    private void showEmpty() {
        hideLoading();
        if (tvEmpty != null) tvEmpty.setVisibility(View.VISIBLE);
        if (errorLayout != null) errorLayout.setVisibility(View.GONE);
    }

    private void hideEmpty() {
        if (tvEmpty != null) tvEmpty.setVisibility(View.GONE);
    }

    private void showError(String message) {
        hideLoading();
        if (errorLayout != null) errorLayout.setVisibility(View.VISIBLE);
        ToastUtils.getInstance().showError(requireContext(), message);
    }

    @Override
    public void onPause() {
        super.onPause();
        stopBannerAutoScroll();
    }

    @Override
    public void onResume() {
        super.onResume();
        if (bannerList.size() > 1) {
            startBannerAutoScroll();
        }
    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        stopBannerAutoScroll();
        binding = null;
    }
}