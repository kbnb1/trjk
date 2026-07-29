package com.software.store.ui.common;

import android.os.Bundle;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.fragment.app.Fragment;

import com.google.android.material.bottomnavigation.BottomNavigationView;
import com.software.store.R;
import com.software.store.ui.discover.DiscoverFragment;
import com.software.store.ui.home.HomeFragment;
import com.software.store.ui.profile.ProfileFragment;
import com.software.store.ui.software.SoftwareFragment;

/**
 * 主 Activity
 * 通过 BottomNavigationView 切换首页、软件、发现、个人中心四个 Fragment
 */
public class MainActivity extends AppCompatActivity {

    private BottomNavigationView bottomNav;

    /** 当前显示的 Fragment */
    private Fragment currentFragment;

    private final HomeFragment homeFragment = new HomeFragment();
    private final SoftwareFragment softwareFragment = new SoftwareFragment();
    private final DiscoverFragment discoverFragment = new DiscoverFragment();
    private final ProfileFragment profileFragment = new ProfileFragment();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        bottomNav = findViewById(R.id.bottom_nav);

        // 默认显示首页
        if (savedInstanceState == null) {
            switchFragment(homeFragment);
        }

        // 底部导航点击监听
        bottomNav.setOnItemSelectedListener(item -> {
            int itemId = item.getItemId();
            if (itemId == R.id.nav_home) {
                switchFragment(homeFragment);
                return true;
            } else if (itemId == R.id.nav_software) {
                switchFragment(softwareFragment);
                return true;
            } else if (itemId == R.id.nav_discover) {
                switchFragment(discoverFragment);
                return true;
            } else if (itemId == R.id.nav_profile) {
                switchFragment(profileFragment);
                return true;
            }
            return false;
        });
    }

    /**
     * 切换 Fragment（使用 hide/show 复用，避免重复创建）
     *
     * @param target 目标 Fragment
     */
    private void switchFragment(@NonNull Fragment target) {
        if (target == currentFragment) {
            return;
        }
        getSupportFragmentManager().beginTransaction()
                .setReorderingAllowed(true)
                .replace(R.id.fragment_container, target)
                .commitNowAllowingStateLoss();
        currentFragment = target;
    }
}
