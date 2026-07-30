# ProGuard 混淆规则

# Retrofit
-keepattributes Signature
-keepattributes *Annotation*
-keep class retrofit2.** { *; }
-keepclasseswithmembers class * {
    @retrofit2.http.* <methods>;
}

# OkHttp
-dontwarn okhttp3.**
-dontwarn okio.**

# Gson
-keep class com.software.store.data.model.** { *; }
-keepattributes Signature

# Glide
-keep public class * implements com.bumptech.glide.module.GlideModule
-keep class * extends com.bumptech.glide.module.AppGlideModule { <init>(...); }

# ==================== 安全加固 ====================

# 代码混淆优化
-optimizationpasses 5
-mergeinterfacesaggressively
-allowaccessmodification
-overloadaggressively
-assumenosideeffects
-dontusemixedcaseclassnames
-dontskipnonpubliclibraryclasses
-dontpreverify
-verbose

# 混淆时不生成原始类名
-repackageclasses ''
-flattenpackagehierarchy ''

# 防止反编译查看字符串
-allowaccessmodification
-repackageclasses

# 安全工具类不被混淆（因为用到了反射）
-keep class com.software.store.util.SecurityUtils { *; }

# 隐藏源文件名和行号
-renamesourcefileattribute SourceFile
-keepattributes SourceFile,LineNumberTable

# 移除Log代码
-assumenosideeffects class android.util.Log {
    public static *** d(...);
    public static *** v(...);
    public static *** i(...);
}

# 防止JSON数据模型被混淆
-keep class com.software.store.data.model.** { *; }

# 防止Retrofit接口被混淆
-keep,allowobfuscation,allowshrinking interface com.software.store.data.remote.ApiService

# 加密字符串常量（通过混淆类名和方法名增加逆向难度）
-adaptclassstrings
