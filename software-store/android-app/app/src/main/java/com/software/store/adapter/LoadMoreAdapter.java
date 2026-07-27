package com.software.store.adapter;

import android.view.LayoutInflater;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.software.store.databinding.ItemLoadMoreBinding;

public class LoadMoreAdapter extends RecyclerView.Adapter<LoadMoreAdapter.LoadMoreViewHolder> {

    private static final int TYPE_LOADING = 0;
    private static final int TYPE_END = 1;
    private static final int TYPE_ERROR = 2;

    private int currentState = TYPE_LOADING;
    private String endText = "没有更多数据了";
    private OnRetryListener retryListener;

    public interface OnRetryListener {
        void onRetry();
    }

    public void setOnRetryListener(OnRetryListener listener) {
        this.retryListener = listener;
    }

    public void setState(int state) {
        this.currentState = state;
        notifyDataSetChanged();
    }

    public void setEndText(String text) {
        this.endText = text;
    }

    public void showLoading() {
        setState(TYPE_LOADING);
    }

    public void showEnd() {
        setState(TYPE_END);
    }

    public void showError() {
        setState(TYPE_ERROR);
    }

    @Override
    public int getItemViewType(int position) {
        return currentState;
    }

    @NonNull
    @Override
    public LoadMoreViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        ItemLoadMoreBinding binding = ItemLoadMoreBinding.inflate(
                LayoutInflater.from(parent.getContext()), parent, false);
        return new LoadMoreViewHolder(binding);
    }

    @Override
    public void onBindViewHolder(@NonNull LoadMoreViewHolder holder, int position) {
        holder.bind();
    }

    @Override
    public int getItemCount() {
        return 1;
    }

    class LoadMoreViewHolder extends RecyclerView.ViewHolder {
        private final ItemLoadMoreBinding binding;

        LoadMoreViewHolder(@NonNull ItemLoadMoreBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }

        void bind() {
            switch (currentState) {
                case TYPE_LOADING:
                    binding.progressLoadMore.setVisibility(android.view.View.VISIBLE);
                    binding.tvLoadMoreText.setText("加载中...");
                    binding.tvLoadMoreText.setClickable(false);
                    break;
                case TYPE_END:
                    binding.progressLoadMore.setVisibility(android.view.View.GONE);
                    binding.tvLoadMoreText.setText(endText);
                    binding.tvLoadMoreText.setClickable(false);
                    break;
                case TYPE_ERROR:
                    binding.progressLoadMore.setVisibility(android.view.View.GONE);
                    binding.tvLoadMoreText.setText("加载失败，点击重试");
                    binding.tvLoadMoreText.setClickable(true);
                    binding.tvLoadMoreText.setOnClickListener(v -> {
                        if (retryListener != null) {
                            retryListener.onRetry();
                        }
                    });
                    break;
            }
        }
    }
}