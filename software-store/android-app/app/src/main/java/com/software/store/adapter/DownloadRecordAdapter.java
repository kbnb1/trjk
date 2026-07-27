package com.software.store.adapter;

import android.graphics.drawable.GradientDrawable;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;
import com.software.store.R;
import com.software.store.data.model.DownloadRecord;
import com.software.store.databinding.ItemDownloadRecordBinding;
import com.software.store.util.DisplayUtils;

import java.util.ArrayList;
import java.util.List;

public class DownloadRecordAdapter extends RecyclerView.Adapter<DownloadRecordAdapter.DownloadViewHolder> {

    private List<DownloadRecord> records = new ArrayList<>();
    private OnItemClickListener listener;
    private OnActionClickListener actionListener;

    public interface OnItemClickListener {
        void onItemClick(DownloadRecord record, int position);
    }

    public interface OnActionClickListener {
        void onActionClick(DownloadRecord record, int position);
    }

    public void setOnItemClickListener(OnItemClickListener listener) {
        this.listener = listener;
    }

    public void setOnActionClickListener(OnActionClickListener actionListener) {
        this.actionListener = actionListener;
    }

    public void setData(List<DownloadRecord> records) {
        this.records = records != null ? records : new ArrayList<>();
        notifyDataSetChanged();
    }

    public void updateProgress(int position, int progress) {
        if (position >= 0 && position < records.size()) {
            records.get(position).setProgress(progress);
            notifyItemChanged(position);
        }
    }

    public void updateStatus(int position, int status) {
        if (position >= 0 && position < records.size()) {
            records.get(position).setStatus(status);
            notifyItemChanged(position);
        }
    }

    @NonNull
    @Override
    public DownloadViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        ItemDownloadRecordBinding binding = ItemDownloadRecordBinding.inflate(
                LayoutInflater.from(parent.getContext()), parent, false);
        return new DownloadViewHolder(binding);
    }

    @Override
    public void onBindViewHolder(@NonNull DownloadViewHolder holder, int position) {
        holder.bind(records.get(position), position);
    }

    @Override
    public int getItemCount() {
        return records.size();
    }

    class DownloadViewHolder extends RecyclerView.ViewHolder {
        private final ItemDownloadRecordBinding binding;

        DownloadViewHolder(@NonNull ItemDownloadRecordBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }

        void bind(DownloadRecord record, int position) {
            Glide.with(binding.getRoot().getContext())
                    .load(record.getIcon())
                    .placeholder(R.drawable.ic_software)
                    .error(R.drawable.ic_software)
                    .centerCrop()
                    .into(binding.ivDownloadIcon);

            binding.tvDownloadName.setText(record.getSoftwareName());

            int status = record.getStatus();
            binding.tvDownloadStatus.setText(getStatusText(status));
            binding.tvDownloadStatus.setTextColor(getStatusColor(status));

            int progress = record.getProgress();
            binding.progressDownload.setProgress(progress);
            if (status == DownloadRecord.STATUS_DOWNLOADING) {
                binding.progressDownload.setVisibility(View.VISIBLE);
                binding.progressDownload.setIndeterminate(false);
                binding.tvDownloadStatus.setText(progress + "%");
            } else if (status == DownloadRecord.STATUS_COMPLETED) {
                binding.progressDownload.setVisibility(View.GONE);
            } else {
                binding.progressDownload.setVisibility(View.GONE);
            }

            String actionText = getActionText(status);
            binding.btnDownloadAction.setText(actionText);

            GradientDrawable btnDrawable = new GradientDrawable();
            btnDrawable.setCornerRadius(DisplayUtils.dp2px(binding.getRoot().getContext(), 20));
            btnDrawable.setColor(getActionColor(status));
            binding.btnDownloadAction.setBackground(btnDrawable);

            itemView.setOnClickListener(v -> {
                if (listener != null) {
                    listener.onItemClick(record, position);
                }
            });

            binding.btnDownloadAction.setOnClickListener(v -> {
                if (actionListener != null) {
                    actionListener.onActionClick(record, position);
                }
            });
        }

        private String getStatusText(int status) {
            switch (status) {
                case DownloadRecord.STATUS_DOWNLOADING:
                    return "下载中";
                case DownloadRecord.STATUS_COMPLETED:
                    return "已完成";
                case DownloadRecord.STATUS_FAILED:
                    return "下载失败";
                default:
                    return "未知状态";
            }
        }

        private int getStatusColor(int status) {
            switch (status) {
                case DownloadRecord.STATUS_DOWNLOADING:
                    return ContextCompat.getColor(binding.getRoot().getContext(), R.color.primary);
                case DownloadRecord.STATUS_COMPLETED:
                    return ContextCompat.getColor(binding.getRoot().getContext(), R.color.success);
                case DownloadRecord.STATUS_FAILED:
                    return ContextCompat.getColor(binding.getRoot().getContext(), R.color.error);
                default:
                    return ContextCompat.getColor(binding.getRoot().getContext(), R.color.text_hint);
            }
        }

        private String getActionText(int status) {
            switch (status) {
                case DownloadRecord.STATUS_DOWNLOADING:
                    return "暂停";
                case DownloadRecord.STATUS_COMPLETED:
                    return "安装";
                case DownloadRecord.STATUS_FAILED:
                    return "重试";
                default:
                    return "查看";
            }
        }

        private int getActionColor(int status) {
            switch (status) {
                case DownloadRecord.STATUS_DOWNLOADING:
                    return ContextCompat.getColor(binding.getRoot().getContext(), R.color.primary);
                case DownloadRecord.STATUS_COMPLETED:
                    return ContextCompat.getColor(binding.getRoot().getContext(), R.color.success);
                case DownloadRecord.STATUS_FAILED:
                    return ContextCompat.getColor(binding.getRoot().getContext(), R.color.error);
                default:
                    return ContextCompat.getColor(binding.getRoot().getContext(), R.color.primary);
            }
        }
    }
}