package com.software.store.adapter;

import android.graphics.drawable.GradientDrawable;
import android.view.LayoutInflater;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.software.store.R;
import com.software.store.data.model.Category;
import com.software.store.databinding.ItemCategoryTabBinding;
import com.software.store.util.DisplayUtils;

import java.util.ArrayList;
import java.util.List;

public class CategoryAdapter extends RecyclerView.Adapter<CategoryAdapter.CategoryViewHolder> {

    private List<Category> categories = new ArrayList<>();
    private int selectedPosition = 0;
    private OnCategorySelectedListener listener;

    public interface OnCategorySelectedListener {
        void onCategorySelected(Category category, int position);
    }

    public void setOnCategorySelectedListener(OnCategorySelectedListener listener) {
        this.listener = listener;
    }

    public void setData(List<Category> categories) {
        this.categories = categories != null ? categories : new ArrayList<>();
        notifyDataSetChanged();
    }

    public void setSelected(int position) {
        int oldPosition = selectedPosition;
        selectedPosition = position;
        notifyItemChanged(oldPosition);
        notifyItemChanged(position);
    }

    @NonNull
    @Override
    public CategoryViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        ItemCategoryTabBinding binding = ItemCategoryTabBinding.inflate(
                LayoutInflater.from(parent.getContext()), parent, false);
        return new CategoryViewHolder(binding);
    }

    @Override
    public void onBindViewHolder(@NonNull CategoryViewHolder holder, int position) {
        holder.bind(categories.get(position), position);
    }

    @Override
    public int getItemCount() {
        return categories.size();
    }

    class CategoryViewHolder extends RecyclerView.ViewHolder {
        private final ItemCategoryTabBinding binding;

        CategoryViewHolder(@NonNull ItemCategoryTabBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }

        void bind(Category category, int position) {
            binding.tvCategory.setText(category.getName());

            boolean isSelected = position == selectedPosition;
            binding.tvCategory.setSelected(isSelected);

            GradientDrawable drawable = new GradientDrawable();
            drawable.setCornerRadius(DisplayUtils.dp2px(binding.getRoot().getContext(), 4));

            if (isSelected) {
                drawable.setColor(ContextCompat.getColor(binding.getRoot().getContext(),
                        R.color.accent_orange));
                binding.tvCategory.setTextColor(
                        ContextCompat.getColor(binding.getRoot().getContext(), R.color.white));
            } else {
                drawable.setColor(ContextCompat.getColor(binding.getRoot().getContext(),
                        R.color.accent_light));
                binding.tvCategory.setTextColor(
                        ContextCompat.getColor(binding.getRoot().getContext(), R.color.accent_orange));
            }
            binding.tvCategory.setBackground(drawable);

            itemView.setOnClickListener(v -> {
                if (listener != null) {
                    listener.onCategorySelected(category, position);
                }
                setSelected(position);
            });
        }
    }
}