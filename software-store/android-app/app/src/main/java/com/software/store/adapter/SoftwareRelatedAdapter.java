package com.software.store.adapter;

import android.view.LayoutInflater;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;
import com.software.store.R;
import com.software.store.data.model.Software;
import com.software.store.databinding.ItemSoftwareDetailRelatedBinding;
import com.software.store.util.FileUtils;

import java.util.ArrayList;
import java.util.List;

public class SoftwareRelatedAdapter extends RecyclerView.Adapter<SoftwareRelatedAdapter.RelatedViewHolder> {

    private List<Software> relatedList = new ArrayList<>();
    private OnItemClickListener listener;

    public interface OnItemClickListener {
        void onItemClick(Software software, int position);
    }

    public void setOnItemClickListener(OnItemClickListener listener) {
        this.listener = listener;
    }

    public void setData(List<Software> list) {
        this.relatedList = list != null ? list : new ArrayList<>();
        notifyDataSetChanged();
    }

    @NonNull
    @Override
    public RelatedViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        ItemSoftwareDetailRelatedBinding binding = ItemSoftwareDetailRelatedBinding.inflate(
                LayoutInflater.from(parent.getContext()), parent, false);
        return new RelatedViewHolder(binding);
    }

    @Override
    public void onBindViewHolder(@NonNull RelatedViewHolder holder, int position) {
        holder.bind(relatedList.get(position), position);
    }

    @Override
    public int getItemCount() {
        return relatedList.size();
    }

    class RelatedViewHolder extends RecyclerView.ViewHolder {
        private final ItemSoftwareDetailRelatedBinding binding;

        RelatedViewHolder(@NonNull ItemSoftwareDetailRelatedBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }

        void bind(Software software, int position) {
            Glide.with(binding.getRoot().getContext())
                    .load(software.getIcon())
                    .placeholder(R.drawable.ic_software)
                    .error(R.drawable.ic_software)
                    .centerCrop()
                    .into(binding.ivRelatedIcon);

            binding.tvRelatedName.setText(software.getName());

            String size = software.getSize();
            String sizeStr;
            if (size == null || size.isEmpty()) {
                sizeStr = FileUtils.formatFileSize(0);
            } else {
                try {
                    long sizeBytes = Long.parseLong(size);
                    sizeStr = FileUtils.formatFileSize(sizeBytes);
                } catch (NumberFormatException e) {
                    sizeStr = size;
                }
            }

            String info = sizeStr + " · " + FileUtils.formatDate(software.getUpdatedAt());
            binding.tvRelatedInfo.setText(info);

            itemView.setOnClickListener(v -> {
                if (listener != null) {
                    listener.onItemClick(software, position);
                }
            });
        }
    }
}