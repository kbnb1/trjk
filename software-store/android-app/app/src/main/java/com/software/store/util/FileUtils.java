package com.software.store.util;

import android.Manifest;
import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.net.Uri;
import android.os.Build;
import android.os.Environment;
import android.provider.Settings;

import androidx.annotation.NonNull;
import androidx.core.app.ActivityCompat;
import androidx.core.content.ContextCompat;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.net.HttpURLConnection;
import java.net.URL;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Locale;

public class FileUtils {

    private FileUtils() {
    }

    public static String formatFileSize(long bytes) {
        if (bytes < 0) return "0 B";
        final String[] units = new String[]{"B", "KB", "MB", "GB", "TB"};
        int unitIndex = 0;
        double size = bytes;
        while (size >= 1024 && unitIndex < units.length - 1) {
            size /= 1024;
            unitIndex++;
        }
        if (unitIndex == 0) {
            return (int) size + " " + units[unitIndex];
        }
        return String.format(Locale.getDefault(), "%.1f %s", size, units[unitIndex]);
    }

    public static String formatDate(String dateStr) {
        try {
            SimpleDateFormat inputFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.getDefault());
            Date date = inputFormat.parse(dateStr);
            if (date == null) date = inputFormat.parse(dateStr.replace("T", " "));
            if (date != null) {
                SimpleDateFormat outputFormat = new SimpleDateFormat("yyyy-MM-dd", Locale.getDefault());
                return outputFormat.format(date);
            }
        } catch (Exception e) {
            try {
                SimpleDateFormat inputFormat2 = new SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss", Locale.getDefault());
                Date date = inputFormat2.parse(dateStr);
                if (date != null) {
                    SimpleDateFormat outputFormat = new SimpleDateFormat("yyyy-MM-dd", Locale.getDefault());
                    return outputFormat.format(date);
                }
            } catch (Exception e2) {
                return dateStr;
            }
        }
        return dateStr;
    }

    public static boolean deleteFile(File file) {
        if (file == null || !file.exists()) return false;
        if (file.isDirectory()) {
            File[] children = file.listFiles();
            if (children != null) {
                for (File child : children) {
                    deleteFile(child);
                }
            }
        }
        return file.delete();
    }

    public static String getFileExtension(String url) {
        if (url == null || url.isEmpty()) return "";
        int lastDotIndex = url.lastIndexOf('.');
        if (lastDotIndex == -1) return "";
        int lastSlashIndex = url.lastIndexOf('/');
        if (lastDotIndex < lastSlashIndex) return "";
        return url.substring(lastDotIndex + 1).toLowerCase(Locale.ROOT);
    }

    public interface DownloadCallback {
        void onProgress(int progress);
        void onSuccess(File file);
        void onFailure(String error);
    }

    public static void downloadApk(Context context, String url, String filename, DownloadCallback callback) {
        new Thread(() -> {
            HttpURLConnection connection = null;
            InputStream inputStream = null;
            FileOutputStream outputStream = null;
            File downloadDir = getDownloadDir(context);
            File outputFile = new File(downloadDir, filename);

            try {
                connection = (HttpURLConnection) new URL(url).openConnection();
                connection.setConnectTimeout(30000);
                connection.setReadTimeout(30000);
                connection.setRequestMethod("GET");
                connection.connect();

                int responseCode = connection.getResponseCode();
                if (responseCode != HttpURLConnection.HTTP_OK) {
                    postOnFailure(context, callback, "下载失败: HTTP " + responseCode);
                    return;
                }

                long fileLength = connection.getContentLengthLong();
                inputStream = connection.getInputStream();
                outputStream = new FileOutputStream(outputFile);

                byte[] buffer = new byte[8192];
                long total = 0;
                int bytesRead;
                int lastProgress = 0;

                while ((bytesRead = inputStream.read(buffer)) != -1) {
                    total += bytesRead;
                    outputStream.write(buffer, 0, bytesRead);

                    if (fileLength > 0) {
                        int progress = (int) ((total * 100) / fileLength);
                        if (progress != lastProgress) {
                            lastProgress = progress;
                            postOnProgress(context, callback, progress);
                        }
                    }
                }

                outputStream.flush();
                postOnSuccess(context, callback, outputFile);

            } catch (Exception e) {
                postOnFailure(context, callback, e.getMessage());
            } finally {
                try {
                    if (outputStream != null) outputStream.close();
                    if (inputStream != null) inputStream.close();
                    if (connection != null) connection.disconnect();
                } catch (IOException ignored) {
                }
            }
        }).start();
    }

    private static File getDownloadDir(Context context) {
        File dir = context.getExternalFilesDir(Environment.DIRECTORY_DOWNLOADS);
        if (dir == null) {
            dir = new File(context.getFilesDir(), "download");
        }
        if (!dir.exists()) {
            dir.mkdirs();
        }
        return dir;
    }

    private static void postOnProgress(Context context, DownloadCallback callback, int progress) {
        if (callback != null) {
            new android.os.Handler(android.os.Looper.getMainLooper())
                    .post(() -> callback.onProgress(progress));
        }
    }

    private static void postOnSuccess(Context context, DownloadCallback callback, File file) {
        if (callback != null) {
            new android.os.Handler(android.os.Looper.getMainLooper())
                    .post(() -> callback.onSuccess(file));
        }
    }

    private static void postOnFailure(Context context, DownloadCallback callback, String error) {
        if (callback != null) {
            new android.os.Handler(android.os.Looper.getMainLooper())
                    .post(() -> callback.onFailure(error));
        }
    }

    public static void installApk(Context context, File file) {
        Intent intent = new Intent(Intent.ACTION_VIEW);
        Uri apkUri;
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.N) {
            apkUri = androidx.core.content.FileProvider.getUriForFile(context,
                    context.getPackageName() + ".fileprovider", file);
            intent.addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION);
        } else {
            apkUri = Uri.fromFile(file);
        }
        intent.setDataAndType(apkUri, "application/vnd.android.package-archive");
        intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
        context.startActivity(intent);
    }

    public static boolean checkAppPermission(Context context, String permission) {
        return ContextCompat.checkSelfPermission(context, permission)
                == PackageManager.PERMISSION_GRANTED;
    }

    public static void requestPermissions(Activity activity, String[] permissions, int requestCode) {
        ActivityCompat.requestPermissions(activity, permissions, requestCode);
    }
}