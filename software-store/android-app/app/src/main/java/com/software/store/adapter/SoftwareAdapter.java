package com.software.store.adapter;

import android.view.LayoutInflater;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;
import com.software.store.R;
import com.software.store.data.model.Software;
import com.software.store.databinding.ItemSoftwareBinding;
import com.software.store.util.FileUtils;

import java.util.ArrayList;
import java.util.List;

public class SoftwareAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final int TYPE_NORMAL = 0;
    private static final int TYPE_GRID = 1;

    private List<Software> softwareList = new ArrayList<>();
    private boolean isGridLayout = false;
    private OnItemClickListener listener;
    private OnItemActionListener actionListener;

    public interface OnItemClickListener {
        void onItemClick(Software software, int position);
    }

    public interface OnItemActionListener {
        void onActionClick(Software software, int position);
    }

    public void setOnItemClickListener(OnItemClickListener listener) {
        this.listener = listener;
    }

    public void setOnItemActionListener(OnItemActionListener actionListener) {
        this.actionListener = actionListener;
    }

    public void setGridLayout(boolean grid) {
        this.isGridLayout = grid;
        notifyDataSetChanged();
    }

    public void setData(List<Software> list) {
        this.softwareList = list != null ? list : new ArrayList<>();
        notifyDataSetChanged();
    }

    @Override
    public int getItemViewType(int position) {
        return isGridLayout ? TYPE_GRID : TYPE_NORMAL;
    }

    @NonNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        ItemSoftwareBinding binding = ItemSoftwareBinding.inflate(
                LayoutInflater.from(parent.getContext()), parent, false);
        return new SoftwareViewHolder(binding);
    }

    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
        SoftwareViewHolder vh = (SoftwareViewHolder) holder;
        vh.bind(softwareList.get(position), position);
    }

    @Override
    public int getItemCount() {
        return softwareList.size();
    }

    class SoftwareViewHolder extends RecyclerView.ViewHolder {
        private final ItemSoftwareBinding binding;

        SoftwareViewHolder(@NonNull ItemSoftwareBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }

        void bind(Software software, int position) {
            Glide.with(binding.getRoot().getContext())
                    .load(software.getIcon())
                    .placeholder(R.drawable.ic_software)
                    .error(R.drawable.ic_software)
                    .centerCrop()
                    .into(binding.ivSoftwareIcon);

            binding.tvSoftwareName.setText(software.getName());

            String size = software.getSize();
            if (size == null || size.isEmpty()) {
                binding.tvSoftwareSize.setText(FileUtils.formatFileSize(0));
            } else {
                try {
                    long sizeBytes = Long.parseLong(size);
                    binding.tvSoftwareSize.setText(FileUtils.formatFileSize(sizeBytes));
                } catch (NumberFormatException e) {
                    binding.tvSoftwareSize.setText(size);
                }
            }

            binding.tvSoftwareDate.setText(FileUtils.formatDate(software.getUpdatedAt()));

            if (software.isTop()) {
                binding.tvSoftwareName.setCompoundDrawablesWithIntrinsicBounds(
                        R.drawable.ic_star, 0, 0, 0);
            } else {
                binding.tvSoftwareName.setCompoundDrawables(null, null, null, null);
            }

            itemView.setOnClickListener(v -> {
                if (listener != null) {
                    listener.onItemClick(software, position);
                }
            });

            binding.btnDownload.setOnClickListener(v -> {
                if (actionListener != null) {
                    actionListener.onActionClick(software, position);
                }
            });
        }
    }
}