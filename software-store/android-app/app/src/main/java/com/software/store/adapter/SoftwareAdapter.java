package com.software.store.adapter;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;
import com.software.store.R;
import com.software.store.data.model.Software;

import java.util.ArrayList;
import java.util.List;

/**
 * 软件列表适配器
 * 用于首页推荐软件、软件页排行、详情页相关推荐等列表展示
 */
public class SoftwareAdapter extends RecyclerView.Adapter<SoftwareAdapter.ViewHolder> {

    /** 列表样式：普通列表 */
    public static final int STYLE_LIST = 0;
    /** 列表样式：带排名 */
    public static final int STYLE_RANK = 1;
    /** 列表样式：横向推荐 */
    public static final int STYLE_RECOMMEND = 2;

    private final List<Software> data = new ArrayList<>();
    private final int style;
    private OnItemClickListener listener;
    private OnDownloadClickListener downloadListener;

    public SoftwareAdapter(int style) {
        this.style = style;
    }

    public void setList(List<Software> list) {
        data.clear();
        if (list != null) {
            data.addAll(list);
        }
        notifyDataSetChanged();
    }

    public void addList(List<Software> list) {
        if (list != null) {
            int start = data.size();
            data.addAll(list);
            notifyItemRangeInserted(start, list.size());
        }
    }

    public void setOnItemClickListener(OnItemClickListener listener) {
        this.listener = listener;
    }

    public void setOnDownloadClickListener(OnDownloadClickListener listener) {
        this.downloadListener = listener;
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        int layoutRes;
        if (style == STYLE_RECOMMEND) {
            layoutRes = R.layout.item_software_recommend;
        } else {
            layoutRes = R.layout.item_software;
        }
        View view = LayoutInflater.from(parent.getContext()).inflate(layoutRes, parent, false);
        return new ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        Software software = data.get(position);
        holder.bind(software, style, position, listener, downloadListener);
    }

    @Override
    public int getItemCount() {
        return data.size();
    }

    static class ViewHolder extends RecyclerView.ViewHolder {
        ImageView icon;
        TextView name;
        TextView desc;
        TextView meta;
        TextView rank;
        TextView downloadCount;
        Button downloadBtn;

        ViewHolder(@NonNull View itemView) {
            super(itemView);
            icon = itemView.findViewById(R.id.iv_icon);
            name = itemView.findViewById(R.id.tv_name);
            desc = itemView.findViewById(R.id.tv_desc);
            meta = itemView.findViewById(R.id.tv_meta);
            rank = itemView.findViewById(R.id.tv_rank);
            downloadCount = itemView.findViewById(R.id.tv_download_count);
            downloadBtn = itemView.findViewById(R.id.btn_download);
        }

        void bind(Software software, int style, int position,
                  OnItemClickListener listener, OnDownloadClickListener downloadListener) {
            // 加载图标
            if (software.getIcon() != null && !software.getIcon().isEmpty()) {
                Glide.with(itemView.getContext())
                        .load(software.getIcon())
                        .placeholder(R.drawable.bg_app_icon)
                        .error(R.drawable.bg_app_icon)
                        .into(icon);
            }

            name.setText(software.getName());
            desc.setText(software.getDescription());

            // 排名样式
            if (rank != null) {
                if (style == STYLE_RANK) {
                    rank.setVisibility(View.VISIBLE);
                    rank.setText(String.valueOf(position + 1));
                    // 前三名特殊颜色
                    int colorRes;
                    switch (position) {
                        case 0:
                            colorRes = R.color.rank_top1;
                            break;
                        case 1:
                            colorRes = R.color.rank_top2;
                            break;
                        case 2:
                            colorRes = R.color.rank_top3;
                            break;
                        default:
                            colorRes = R.color.text_hint;
                    }
                    rank.setTextColor(itemView.getContext().getColor(colorRes));
                } else {
                    rank.setVisibility(View.GONE);
                }
            }

            // 元信息：大小 + 下载量
            if (meta != null) {
                String metaText = software.getSize() + "MB · " + software.getFormatDownloadCount() + "下载";
                meta.setText(metaText);
            }

            // 下载量（排行样式单独显示）
            if (downloadCount != null) {
                downloadCount.setText("下载 " + software.getFormatDownloadCount());
            }

            // 下载按钮
            if (downloadBtn != null) {
                downloadBtn.setOnClickListener(v -> {
                    if (downloadListener != null) {
                        downloadListener.onDownload(software, getAdapterPosition());
                    }
                });
            }

            // 整条点击
            itemView.setOnClickListener(v -> {
                if (listener != null) {
                    listener.onItemClick(software, getAdapterPosition());
                }
            });
        }
    }

    /**
     * 列表项点击回调
     */
    public interface OnItemClickListener {
        void onItemClick(Software software, int position);
    }

    /**
     * 下载按钮点击回调
     */
    public interface OnDownloadClickListener {
        void onDownload(Software software, int position);
    }
}
