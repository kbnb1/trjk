package com.software.store.adapter;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;
import com.software.store.R;
import com.software.store.data.model.Banner;

import java.util.ArrayList;
import java.util.List;

/**
 * 轮播图适配器
 * 用于首页顶部 Banner 滚动展示（配合 ViewPager2 使用）
 */
public class BannerAdapter extends RecyclerView.Adapter<BannerAdapter.ViewHolder> {

    private final List<Banner> data = new ArrayList<>();
    private OnBannerClickListener listener;

    public void setList(List<Banner> list) {
        data.clear();
        if (list != null) {
            data.addAll(list);
        }
        notifyDataSetChanged();
    }

    public void setOnBannerClickListener(OnBannerClickListener listener) {
        this.listener = listener;
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_banner, parent, false);
        return new ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        Banner banner = data.get(position);
        holder.bind(banner, listener);
    }

    @Override
    public int getItemCount() {
        return data.size();
    }

    static class ViewHolder extends RecyclerView.ViewHolder {
        ImageView image;
        TextView tag;
        TextView title;
        TextView subtitle;

        ViewHolder(@NonNull View itemView) {
            super(itemView);
            image = itemView.findViewById(R.id.iv_banner);
            tag = itemView.findViewById(R.id.tv_tag);
            title = itemView.findViewById(R.id.tv_title);
            subtitle = itemView.findViewById(R.id.tv_subtitle);
        }

        void bind(Banner banner, OnBannerClickListener listener) {
            // 加载图片
            if (banner.getImage() != null && !banner.getImage().isEmpty()) {
                Glide.with(itemView.getContext())
                        .load(banner.getImage())
                        .into(image);
            }
            // 标签
            if (tag != null) {
                if (banner.getTag() != null && !banner.getTag().isEmpty()) {
                    tag.setVisibility(View.VISIBLE);
                    tag.setText(banner.getTag());
                } else {
                    tag.setVisibility(View.GONE);
                }
            }
            // 标题
            if (title != null) {
                title.setText(banner.getTitle());
            }
            // 副标题
            if (subtitle != null) {
                subtitle.setText(banner.getSubtitle());
            }
            // 点击事件
            itemView.setOnClickListener(v -> {
                if (listener != null) {
                    listener.onBannerClick(banner, getAdapterPosition());
                }
            });
        }
    }

    /**
     * 轮播图点击回调
     */
    public interface OnBannerClickListener {
        void onBannerClick(Banner banner, int position);
    }
}
