package com.software.store.ui.common;

import android.os.Bundle;
import android.view.MenuItem;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.fragment.app.Fragment;

import com.google.android.material.bottomnavigation.BottomNavigationView;
import com.software.store.R;
import com.software.store.databinding.ActivityMainBinding;
import com.software.store.ui.discover.DiscoverFragment;
import com.software.store.ui.home.HomeFragment;
import com.software.store.ui.profile.ProfileFragment;
import com.software.store.ui.software.SoftwareFragment;

public class MainActivity extends AppCompatActivity implements BottomNavigationView.OnItemSelectedListener {

    private ActivityMainBinding binding;
    private Fragment homeFragment;
    private Fragment softwareFragment;
    private Fragment discoverFragment;
    private Fragment profileFragment;
    private Fragment activeFragment;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivityMainBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        if (savedInstanceState == null) {
            initFragments();
            showFragment(homeFragment);
        } else {
            restoreFragments();
        }

        binding.bottomNavigation.setOnItemSelectedListener(this);
    }

    private void initFragments() {
        homeFragment = new HomeFragment();
        softwareFragment = new SoftwareFragment();
        discoverFragment = new DiscoverFragment();
        profileFragment = new ProfileFragment();

        getSupportFragmentManager()
                .beginTransaction()
                .add(R.id.fragment_container, softwareFragment, "software").hide(softwareFragment)
                .add(R.id.fragment_container, discoverFragment, "discover").hide(discoverFragment)
                .add(R.id.fragment_container, profileFragment, "profile").hide(profileFragment)
                .add(R.id.fragment_container, homeFragment, "home")
                .commit();

        activeFragment = homeFragment;
    }

    private void restoreFragments() {
        homeFragment = getSupportFragmentManager().findFragmentByTag("home");
        softwareFragment = getSupportFragmentManager().findFragmentByTag("software");
        discoverFragment = getSupportFragmentManager().findFragmentByTag("discover");
        profileFragment = getSupportFragmentManager().findFragmentByTag("profile");

        if (homeFragment == null) homeFragment = new HomeFragment();
        if (softwareFragment == null) softwareFragment = new SoftwareFragment();
        if (discoverFragment == null) discoverFragment = new DiscoverFragment();
        if (profileFragment == null) profileFragment = new ProfileFragment();

        int selectedItemId = binding.bottomNavigation.getSelectedItemId();
        if (selectedItemId == R.id.nav_software) {
            activeFragment = softwareFragment;
        } else if (selectedItemId == R.id.nav_discover) {
            activeFragment = discoverFragment;
        } else if (selectedItemId == R.id.nav_more) {
            activeFragment = profileFragment;
        } else {
            activeFragment = homeFragment;
        }
    }

    private void showFragment(Fragment target) {
        if (target == activeFragment) return;

        getSupportFragmentManager()
                .beginTransaction()
                .hide(activeFragment)
                .show(target)
                .commit();

        activeFragment = target;
    }

    @Override
    public boolean onNavigationItemSelected(@NonNull MenuItem item) {
        int itemId = item.getItemId();

        if (itemId == R.id.nav_home) {
            showFragment(homeFragment);
        } else if (itemId == R.id.nav_software) {
            showFragment(softwareFragment);
        } else if (itemId == R.id.nav_discover) {
            showFragment(discoverFragment);
        } else if (itemId == R.id.nav_more) {
            showFragment(profileFragment);
        } else {
            return false;
        }
        return true;
    }

    @Override
    public void onBackPressed() {
        if (activeFragment != homeFragment) {
            binding.bottomNavigation.setSelectedItemId(R.id.nav_home);
        } else {
            super.onBackPressed();
        }
    }

    @Override
    protected void onSaveInstanceState(@NonNull Bundle outState) {
        super.onSaveInstanceState(outState);
    }
}