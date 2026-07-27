package com.software.store.adapter;

import android.view.LayoutInflater;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.bumptech.glide.Glide;
import com.software.store.R;
import com.software.store.data.model.Toolbar;
import com.software.store.databinding.ItemMenuGridBinding;

import java.util.ArrayList;
import java.util.List;

public class ToolbarAdapter extends RecyclerView.Adapter<ToolbarAdapter.ToolbarViewHolder> {

    private List<Toolbar> items = new ArrayList<>();
    private OnItemClickListener listener;

    public interface OnItemClickListener {
        void onItemClick(Toolbar item, int position);
    }

    public void setOnItemClickListener(OnItemClickListener listener) {
        this.listener = listener;
    }

    public void setData(List<Toolbar> items) {
        this.items = items != null ? items : new ArrayList<>();
        notifyDataSetChanged();
    }

    @NonNull
    @Override
    public ToolbarViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        ItemMenuGridBinding binding = ItemMenuGridBinding.inflate(
                LayoutInflater.from(parent.getContext()), parent, false);
        return new ToolbarViewHolder(binding);
    }

    @Override
    public void onBindViewHolder(@NonNull ToolbarViewHolder holder, int position) {
        holder.bind(items.get(position), position);
    }

    @Override
    public int getItemCount() {
        return items.size();
    }

    class ToolbarViewHolder extends RecyclerView.ViewHolder {
        private final ItemMenuGridBinding binding;

        ToolbarViewHolder(@NonNull ItemMenuGridBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }

        void bind(Toolbar item, int position) {
            Glide.with(binding.getRoot().getContext())
                    .load(item.getIcon())
                    .placeholder(R.drawable.ic_software)
                    .error(R.drawable.ic_software)
                    .centerInside()
                    .into(binding.ivMenuIcon);

            binding.tvMenuName.setText(item.getName());

            itemView.setOnClickListener(v -> {
                if (listener != null) {
                    listener.onItemClick(item, position);
                }
            });
        }
    }
}