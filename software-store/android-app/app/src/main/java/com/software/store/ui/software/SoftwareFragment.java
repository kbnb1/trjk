package com.software.store.ui.software;

import android.content.Intent;
import android.os.Bundle;
import android.text.TextUtils;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.EditText;
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
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;

import com.software.store.R;
import com.software.store.ui.software.SoftwareDetailActivity;
import com.software.store.adapter.CategoryAdapter;
import com.software.store.adapter.LoadMoreAdapter;
import com.software.store.adapter.SoftwareAdapter;
import com.software.store.data.model.Category;
import com.software.store.data.model.Software;
import com.software.store.data.repository.HomeRepository;
import com.software.store.data.repository.SoftwareRepository;
import com.software.store.databinding.FragmentSoftwareBinding;
import com.software.store.util.DisplayUtils;
import com.software.store.util.NetworkUtils;
import com.software.store.util.ToastUtils;

import java.util.ArrayList;
import java.util.List;

public class SoftwareFragment extends Fragment {

    private FragmentSoftwareBinding binding;
    private CategoryAdapter categoryAdapter;
    private SoftwareAdapter softwareAdapter;
    private LoadMoreAdapter loadMoreAdapter;
    private SoftwareRepository softwareRepository;
    private HomeRepository homeRepository;

    private final List<Software> softwareList = new ArrayList<>();
    private final List<Category> categoryList = new ArrayList<>();

    private int currentPage = 1;
    private static final int PER_PAGE = 20;
    private boolean isLoading = false;
    private boolean hasMore = true;
    private int selectedCategoryId = 0;
    private String searchKeyword = "";

    private SwipeRefreshLayout swipeRefresh;
    private LinearLayout searchBar;
    private EditText etSearch;
    private ImageView ivSearch;
    private ProgressBar progressCenter;
    private LinearLayout emptyLayout;
    private TextView tvEmpty;
    private LinearLayout errorLayout;

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container,
                             @Nullable Bundle savedInstanceState) {
        binding = FragmentSoftwareBinding.inflate(inflater, container, false);
        return binding.getRoot();
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        softwareRepository = SoftwareRepository.getInstance();
        homeRepository = HomeRepository.getInstance();
        initProgrammaticViews();
        setupCategories();
        setupSoftwareList();
        setupSwipeRefresh();
        loadCategories();
    }

    private void initProgrammaticViews() {
        swipeRefresh = new SwipeRefreshLayout(requireContext());
        swipeRefresh.setLayoutParams(new LinearLayout.LayoutParams(
                LinearLayout.LayoutParams.MATCH_PARENT, LinearLayout.LayoutParams.MATCH_PARENT));
        swipeRefresh.setColorSchemeResources(R.color.primary);

        View originalRoot = binding.getRoot();
        if (originalRoot instanceof LinearLayout) {
            ((LinearLayout) originalRoot).removeAllViews();
            swipeRefresh.addView(originalRoot);
            ((LinearLayout) originalRoot).addView(swipeRefresh);
        }

        searchBar = new LinearLayout(requireContext());
        searchBar.setOrientation(LinearLayout.HORIZONTAL);
        searchBar.setGravity(android.view.Gravity.CENTER_VERTICAL);
        searchBar.setBackgroundColor(ContextCompat.getColor(requireContext(), R.color.white));
        searchBar.setPadding(DisplayUtils.dp2px(requireContext(), 16),
                DisplayUtils.dp2px(requireContext(), 8),
                DisplayUtils.dp2px(requireContext(), 16),
                DisplayUtils.dp2px(requireContext(), 8));

        etSearch = new EditText(requireContext());
        etSearch.setLayoutParams(new LinearLayout.LayoutParams(
                0, DisplayUtils.dp2px(requireContext(), 40), 1));
        etSearch.setBackgroundResource(R.drawable.bg_rounded_corners_light);
        etSearch.setHint(R.string.search_software);
        etSearch.setPadding(DisplayUtils.dp2px(requireContext(), 12),
                DisplayUtils.dp2px(requireContext(), 8),
                DisplayUtils.dp2px(requireContext(), 8),
                DisplayUtils.dp2px(requireContext(), 8));
        etSearch.setTextColor(ContextCompat.getColor(requireContext(), R.color.text_primary));
        etSearch.setTextColorHint(ContextCompat.getColor(requireContext(), R.color.text_hint));
        etSearch.setTextSize(14);

        ivSearch = new ImageView(requireContext());
        ivSearch.setLayoutParams(new LinearLayout.LayoutParams(
                DisplayUtils.dp2px(requireContext(), 24), DisplayUtils.dp2px(requireContext(), 24)));
        ivSearch.setImageResource(R.drawable.ic_search);
        ivSearch.setContentDescription(getString(R.string.search_software));
        LinearLayout.LayoutParams searchIconParams = (LinearLayout.LayoutParams) ivSearch.getLayoutParams();
        searchIconParams.setMarginStart(DisplayUtils.dp2px(requireContext(), 8));
        ivSearch.setLayoutParams(searchIconParams);
        ivSearch.setClickable(true);
        ivSearch.setFocusable(true);

        searchBar.addView(etSearch);
        searchBar.addView(ivSearch);

        LinearLayout rootLayout = (LinearLayout) binding.getRoot();
        int hsvIndex = rootLayout.indexOfChild(binding.hsvCategories);
        if (hsvIndex >= 0) {
            rootLayout.addView(searchBar, hsvIndex);
        } else {
            rootLayout.addView(searchBar, 0);
        }

        progressCenter = binding.progressCenter;

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
        errorLayout.setOnClickListener(v -> loadSoftwareList(true));

        swipeRefresh.addView(emptyLayout);
        swipeRefresh.addView(errorLayout);

        etSearch.setOnEditorActionListener((v, actionId, event) -> {
            searchKeyword = etSearch.getText().toString().trim();
            currentPage = 1;
            hasMore = true;
            softwareList.clear();
            softwareAdapter.setData(softwareList);
            loadSoftwareList(true);
            return true;
        });

        ivSearch.setOnClickListener(v -> {
            searchKeyword = etSearch.getText().toString().trim();
            currentPage = 1;
            hasMore = true;
            softwareList.clear();
            softwareAdapter.setData(softwareList);
            loadSoftwareList(true);
        });
    }

    private void setupCategories() {
        categoryAdapter = new CategoryAdapter();
        categoryAdapter.setOnCategorySelectedListener((category, position) -> {
            selectedCategoryId = category.getId();
            currentPage = 1;
            hasMore = true;
            softwareList.clear();
            softwareAdapter.setData(softwareList);
            loadSoftwareList(true);
        });

        LinearLayoutManager layoutManager = new LinearLayoutManager(requireContext(),
                LinearLayoutManager.HORIZONTAL, false);
        binding.hsvCategories.setLayoutManager(layoutManager);
        binding.hsvCategories.setAdapter(categoryAdapter);

        Category allCategory = new Category();
        allCategory.setId(0);
        allCategory.setName(getString(R.string.all));
        categoryList.add(allCategory);
        categoryAdapter.setData(categoryList);
    }

    private void setupSoftwareList() {
        softwareAdapter = new SoftwareAdapter();
        softwareAdapter.setOnItemClickListener((software, position) -> {
            Intent intent = new Intent(requireContext(), SoftwareDetailActivity.class);
            intent.putExtra("software_id", software.getId());
            startActivity(intent);
        });
        softwareAdapter.setOnItemActionListener((software, position) -> {
            Intent intent = new Intent(requireContext(), SoftwareDetailActivity.class);
            intent.putExtra("software_id", software.getId());
            startActivity(intent);
        });

        loadMoreAdapter = new LoadMoreAdapter();
        loadMoreAdapter.setOnRetryListener(() -> loadSoftwareList(false));

        LinearLayoutManager layoutManager = new LinearLayoutManager(requireContext());
        binding.rvSoftware.setLayoutManager(layoutManager);
        binding.rvSoftware.setAdapter(softwareAdapter);

        binding.rvSoftware.addOnScrollListener(new RecyclerView.OnScrollListener() {
            @Override
            public void onScrolled(@NonNull RecyclerView recyclerView, int dx, int dy) {
                super.onScrolled(recyclerView, dx, dy);
                if (dy <= 0) return;
                LinearLayoutManager lm = (LinearLayoutManager) recyclerView.getLayoutManager();
                if (lm == null) return;
                int totalItemCount = lm.getItemCount();
                int lastVisibleItem = lm.findLastVisibleItemPosition();
                if (!isLoading && hasMore && totalItemCount > 0 && lastVisibleItem >= totalItemCount - 3) {
                    loadSoftwareList(false);
                }
            }
        });
    }

    private void setupSwipeRefresh() {
        swipeRefresh.setOnRefreshListener(() -> {
            currentPage = 1;
            hasMore = true;
            softwareList.clear();
            softwareAdapter.setData(softwareList);
            loadSoftwareList(true);
        });
    }

    private void loadCategories() {
        homeRepository.getCategories(new HomeRepository.Callback<List<Category>>() {
            @Override
            public void onSuccess(List<Category> result) {
                if (!isAdded()) return;
                if (result != null && !result.isEmpty()) {
                    categoryList.clear();
                    Category allCategory = new Category();
                    allCategory.setId(0);
                    allCategory.setName(getString(R.string.all));
                    categoryList.add(allCategory);
                    categoryList.addAll(result);
                    categoryAdapter.setData(categoryList);
                }
                loadSoftwareList(true);
            }

            @Override
            public void onError(String message) {
                if (!isAdded()) return;
                loadSoftwareList(true);
            }
        });
    }

    private void loadSoftwareList(boolean isRefresh) {
        if (isLoading) return;
        if (!isRefresh && !hasMore) return;

        isLoading = true;
        if (isRefresh) {
            showLoading();
        } else {
            loadMoreAdapter.showLoading();
            softwareAdapter.notifyDataSetChanged();
        }

        if (!NetworkUtils.isNetworkAvailable(requireContext())) {
            isLoading = false;
            if (isRefresh) {
                swipeRefresh.setRefreshing(false);
                showError(getString(R.string.network_error));
            } else {
                loadMoreAdapter.showError();
                softwareAdapter.notifyDataSetChanged();
            }
            return;
        }

        if (!TextUtils.isEmpty(searchKeyword)) {
            softwareRepository.searchSoftware(searchKeyword, currentPage,
                    new SoftwareRepository.Callback<List<Software>>() {
                        @Override
                        public void onSuccess(List<Software> result) {
                            if (!isAdded()) return;
                            isLoading = false;
                            handleLoadResult(result, isRefresh);
                        }

                        @Override
                        public void onError(String message) {
                            if (!isAdded()) return;
                            isLoading = false;
                            handleLoadError(message, isRefresh);
                        }
                    });
        } else {
            softwareRepository.getSoftwareList(
                    selectedCategoryId == 0 ? null : selectedCategoryId,
                    currentPage, PER_PAGE,
                    new SoftwareRepository.Callback<List<Software>>() {
                        @Override
                        public void onSuccess(List<Software> result) {
                            if (!isAdded()) return;
                            isLoading = false;
                            handleLoadResult(result, isRefresh);
                        }

                        @Override
                        public void onError(String message) {
                            if (!isAdded()) return;
                            isLoading = false;
                            handleLoadError(message, isRefresh);
                        }
                    });
        }
    }

    private void handleLoadResult(List<Software> result, boolean isRefresh) {
        if (isRefresh) {
            swipeRefresh.setRefreshing(false);
            hideLoading();
            softwareList.clear();
        }

        if (result == null || result.isEmpty()) {
            if (isRefresh) {
                hasMore = false;
                softwareAdapter.setData(softwareList);
                showEmpty();
            } else {
                hasMore = false;
                loadMoreAdapter.showEnd();
            }
            return;
        }

        hideEmpty();
        int startPosition = softwareList.size();
        softwareList.addAll(result);
        softwareAdapter.setData(softwareList);

        if (!isRefresh) {
            softwareAdapter.notifyItemRangeInserted(startPosition, result.size());
        }

        if (result.size() < PER_PAGE) {
            hasMore = false;
            loadMoreAdapter.showEnd();
        } else {
            hasMore = true;
            currentPage++;
        }
    }

    private void handleLoadError(String message, boolean isRefresh) {
        if (isRefresh) {
            swipeRefresh.setRefreshing(false);
            hideLoading();
            showError(message != null ? message : getString(R.string.network_error));
        } else {
            loadMoreAdapter.showError();
            softwareAdapter.notifyDataSetChanged();
        }
        ToastUtils.getInstance().showError(requireContext(),
                message != null ? message : getString(R.string.network_error));
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
        tvEmpty.setText(TextUtils.isEmpty(searchKeyword)
                ? getString(R.string.no_data)
                : getString(R.string.no_data));
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