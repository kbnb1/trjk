<?php

namespace App;

if ($_ROUTE_METHOD !== 'GET') {
    Response::error('请求方法不允许', Response::CODE_BAD_REQUEST);
}

$siteName = getUserConfigValue('site', 'site_name', '软件商店');
$siteLogo = getUserConfigValue('site', 'site_logo', '');
$siteIcp = getUserConfigValue('site', 'site_icp', '');
$siteCopyright = getUserConfigValue('site', 'site_copyright', '');
$siteDescription = getUserConfigValue('site', 'site_description', '');
$siteKeywords = getUserConfigValue('site', 'site_keywords', '');

$registerEnabled = (bool)getUserConfigValue('register', 'enable_register', true);
$phoneVerify = (bool)getUserConfigValue('register', 'phone_verify', false);
$emailVerify = (bool)getUserConfigValue('register', 'email_verify', false);

$downloadEnabled = (bool)getUserConfigValue('download', 'enable_download', true);
$needLogin = (bool)getUserConfigValue('download', 'need_login', true);

$response = [
    'site' => [
        'name'        => $siteName,
        'logo'        => $siteLogo,
        'icp'         => $siteIcp,
        'copyright'   => $siteCopyright,
        'description' => $siteDescription,
        'keywords'    => $siteKeywords,
    ],
    'register' => [
        'enabled'       => $registerEnabled,
        'phone_verify'  => $phoneVerify,
        'email_verify'  => $emailVerify,
    ],
    'download' => [
        'enabled'     => $downloadEnabled,
        'need_login'  => $needLogin,
    ],
    'seo' => [
        'title'       => getUserConfigValue('seo', 'seo_title', $siteName),
        'keywords'    => getUserConfigValue('seo', 'seo_keywords', ''),
        'description' => getUserConfigValue('seo', 'seo_description', ''),
    ],
    'version' => '1.0.0',
];

Response::success($response, '获取成功');
