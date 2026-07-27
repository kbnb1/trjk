package com.software.store.ui.profile;

import android.content.ClipData;
import android.content.ClipboardManager;
import android.content.Intent;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.GridView;
import android.widget.ImageView;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;

import com.bumptech.glide.Glide;
import com.software.store.R;
import com.software.store.data.model.User;
import com.software.store.databinding.FragmentProfileBinding;
import com.software.store.ui.common.WebViewActivity;
import com.software.store.ui.download.DownloadManagementActivity;
import com.software.store.ui.login.FeedbackActivity;
import com.software.store.ui.login.LoginActivity;
import com.software.store.util.SharedPrefsManager;
import com.software.store.util.ToastUtils;

import java.util.ArrayList;
import java.util.List;

public class ProfileFragment extends Fragment {

    private FragmentProfileBinding binding;
    private SharedPrefsManager prefsManager;
    private boolean isLoggedIn;

    private static final String URL_PERMANENT_ADDRESS = "https://example.com/permanent";
    private static final String URL_BUSINESS_CONTACT = "mailto:business@example.com";
    private static final String URL_GROUP = "https://example.com/group";
    private static final String URL_SHARE = "https://example.com/share";

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container,
                             @Nullable Bundle savedInstanceState) {
        binding = FragmentProfileBinding.inflate(inflater, container, false);
        return binding.getRoot();
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        prefsManager = SharedPrefsManager.getInstance(requireContext());
        initMenuGrid();
        setupClickListeners();
    }

    @Override
    public void onResume() {
        super.onResume();
        checkLoginState();
    }

    private void initMenuGrid() {
        List<MenuItem> menuItems = new ArrayList<>();
        menuItems.add(new MenuItem(R.drawable.ic_link, R.string.permanent_address, URL_PERMANENT_ADDRESS, MenuItem.TYPE_WEBVIEW));
        menuItems.add(new MenuItem(R.drawable.ic_info, R.string.business_contact, "business@example.com", MenuItem.TYPE_COPY));
        menuItems.add(new MenuItem(R.drawable.ic_star, R.string.software_feedback, null, MenuItem.TYPE_FEEDBACK));
        menuItems.add(new MenuItem(R.drawable.ic_user_default, R.string.join_group, URL_GROUP, MenuItem.TYPE_WEBVIEW));

        GridAdapter adapter = new GridAdapter(menuItems);
        binding.gvMenu.setAdapter(adapter);
        binding.gvMenu.setOnItemClickListener((parent, view1, position, id) -> {
            MenuItem item = menuItems.get(position);
            handleMenuItemClick(item);
        });
    }

    private void handleMenuItemClick(MenuItem item) {
        switch (item.type) {
            case MenuItem.TYPE_WEBVIEW:
                Intent webIntent = new Intent(requireContext(), WebViewActivity.class);
                webIntent.putExtra("url", item.url);
                webIntent.putExtra("title", getString(item.nameRes));
                startActivity(webIntent);
                break;
            case MenuItem.TYPE_COPY:
                copyToClipboard(item.url);
                break;
            case MenuItem.TYPE_FEEDBACK:
                Intent feedbackIntent = new Intent(requireContext(), FeedbackActivity.class);
                startActivity(feedbackIntent);
                break;
        }
    }

    private void copyToClipboard(String text) {
        ClipboardManager clipboard = (ClipboardManager) requireContext()
                .getSystemService(requireContext().CLIPBOARD_SERVICE);
        if (clipboard != null) {
            ClipData clip = ClipData.newPlainText("contact", text);
            clipboard.setPrimaryClip(clip);
            ToastUtils.getInstance().showShort(requireContext(),
                    getString(R.string.copied_to_clipboard));
        }
    }

    private void setupClickListeners() {
        binding.layoutUserInfo.setOnClickListener(v -> {
            if (!isLoggedIn) {
                navigateToLogin();
            }
        });

        binding.itemDownload.setOnClickListener(v -> {
            Intent intent = new Intent(requireContext(), DownloadManagementActivity.class);
            startActivity(intent);
        });

        binding.itemFavorite.setOnClickListener(v -> {
            Intent intent = new Intent(requireContext(), WebViewActivity.class);
            intent.putExtra("url", URL_SHARE);
            intent.putExtra("title", getString(R.string.favorite));
            startActivity(intent);
        });

        binding.itemCache.setOnClickListener(v -> showClearCacheDialog());

        binding.itemFeedback.setOnClickListener(v -> {
            Intent intent = new Intent(requireContext(), FeedbackActivity.class);
            startActivity(intent);
        });

        binding.btnLogout.setOnClickListener(v -> handleLogout());
    }

    private void checkLoginState() {
        isLoggedIn = prefsManager.isLogin();
        if (isLoggedIn) {
            showLoggedInState();
        } else {
            showLoggedOutState();
        }
    }

    private void showLoggedInState() {
        User user = prefsManager.getUser();
        if (user != null) {
            binding.tvUsername.setText(user.getUsername());
            binding.tvUserDesc.setText(getString(R.string.version_name) + " 1.0.0");
            Glide.with(requireContext())
                    .load(user.getAvatar())
                    .placeholder(R.drawable.ic_user_default)
                    .error(R.drawable.ic_user_default)
                    .centerCrop()
                    .into(binding.ivAvatar);
        } else {
            binding.tvUsername.setText(getString(R.string.username));
            binding.tvUserDesc.setText(getString(R.string.version_name) + " 1.0.0");
            binding.ivAvatar.setImageResource(R.drawable.ic_user_default);
        }

        binding.btnLogout.setVisibility(View.VISIBLE);
        binding.btnLogout.setText(R.string.logout);
        binding.ivArrow.setVisibility(View.VISIBLE);
    }

    private void showLoggedOutState() {
        binding.tvUsername.setText(getString(R.string.login));
        binding.tvUserDesc.setText(getString(R.string.welcome));
        binding.ivAvatar.setImageResource(R.drawable.ic_user_default);
        binding.btnLogout.setVisibility(View.VISIBLE);
        binding.btnLogout.setText(R.string.login);
        binding.ivArrow.setVisibility(View.VISIBLE);
    }

    private void navigateToLogin() {
        Intent intent = new Intent(requireContext(), LoginActivity.class);
        startActivity(intent);
    }

    private void handleLogout() {
        if (isLoggedIn) {
            showLogoutConfirmDialog();
        } else {
            navigateToLogin();
        }
    }

    private void showLogoutConfirmDialog() {
        new androidx.appcompat.app.AlertDialog.Builder(requireContext())
                .setTitle(R.string.confirm)
                .setMessage(R.string.confirm_exit)
                .setPositiveButton(R.string.confirm, (dialog, which) -> performLogout())
                .setNegativeButton(R.string.cancel, null)
                .show();
    }

    private void performLogout() {
        prefsManager.logout();
        isLoggedIn = false;
        showLoggedOutState();
        ToastUtils.getInstance().showSuccess(requireContext(),
                getString(R.string.logout_success));
    }

    private void showClearCacheDialog() {
        new androidx.appcompat.app.AlertDialog.Builder(requireContext())
                .setTitle(R.string.clear_cache)
                .setMessage(R.string.confirm_delete)
                .setPositiveButton(R.string.confirm, (dialog, which) -> clearCache())
                .setNegativeButton(R.string.cancel, null)
                .show();
    }

    private void clearCache() {
        try {
            Glide.get(requireContext()).clearDiskCache();
            Glide.get(requireContext()).clearMemory();
            ToastUtils.getInstance().showSuccess(requireContext(),
                    getString(R.string.clear_success));
        } catch (Exception e) {
            ToastUtils.getInstance().showError(requireContext(),
                    getString(R.string.network_error));
        }
    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        binding = null;
    }

    private static class MenuItem {
        static final int TYPE_WEBVIEW = 0;
        static final int TYPE_COPY = 1;
        static final int TYPE_FEEDBACK = 2;

        final int iconRes;
        final int nameRes;
        final String url;
        final int type;

        MenuItem(int iconRes, int nameRes, String url, int type) {
            this.iconRes = iconRes;
            this.nameRes = nameRes;
            this.url = url;
            this.type = type;
        }
    }

    private class GridAdapter extends android.widget.BaseAdapter {
        private final List<MenuItem> items;

        GridAdapter(List<MenuItem> items) {
            this.items = items;
        }

        @Override
        public int getCount() {
            return items.size();
        }

        @Override
        public Object getItem(int position) {
            return items.get(position);
        }

        @Override
        public long getItemId(int position) {
            return position;
        }

        @Override
        public View getView(int position, View convertView, ViewGroup parent) {
            ViewHolder holder;
            if (convertView == null) {
                convertView = LayoutInflater.from(requireContext())
                        .inflate(R.layout.item_menu_grid, parent, false);
                holder = new ViewHolder();
                holder.ivIcon = convertView.findViewById(R.id.iv_menu_icon);
                holder.tvName = convertView.findViewById(R.id.tv_menu_name);
                convertView.setTag(holder);
            } else {
                holder = (ViewHolder) convertView.getTag();
            }

            MenuItem item = items.get(position);
            holder.ivIcon.setImageResource(item.iconRes);
            holder.tvName.setText(item.nameRes);
            return convertView;
        }

        private class ViewHolder {
            ImageView ivIcon;
            TextView tvName;
        }
    }
}