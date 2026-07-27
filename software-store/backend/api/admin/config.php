<?php

namespace App;

$route = $GLOBALS['_route'];
$method = $route['method'];
$action = $route['action'];

if ($method === 'GET') {
    $groupName = trim($_GET['group'] ?? '');

    if ($groupName !== '') {
        $list = Database::fetchAll(
            "SELECT * FROM config WHERE group_name = ? ORDER BY sort DESC, id ASC",
            [$groupName]
        );
    } else {
        $list = Database::fetchAll(
            "SELECT * FROM config ORDER BY group_name, sort DESC, id ASC"
        );
    }

    $grouped = [];
    foreach ($list as $item) {
        $item['id'] = (int)$item['id'];
        $item['sort'] = (int)$item['sort'];
        $item['group_name'] = $item['group_name'];
        $item['key_name'] = $item['key_name'];
        $item['type'] = $item['type'];
        $item['value'] = $item['value'];

        $group = $item['group_name'];
        if (!isset($grouped[$group])) {
            $grouped[$group] = [];
        }
        $grouped[$group][] = $item;
    }

    Response::success([
        'groups' => array_keys($grouped),
        'config' => $grouped,
    ]);
}

if ($method === 'POST' && $action === null) {
    $input = parseJsonInput();

    if (empty($input['items']) || !is_array($input['items'])) {
        Response::error('参数错误: items 数组必填');
    }

    $updated = 0;
    foreach ($input['items'] as $item) {
        if (!isset($item['id']) || !isset($item['value'])) {
            continue;
        }

        $configItem = Database::fetch("SELECT id, type FROM config WHERE id = ?", [(int)$item['id']]);
        if (!$configItem) {
            continue;
        }

        $value = $item['value'];
        $type = $configItem['type'];

        switch ($type) {
            case 'number':
                $value = is_numeric($value) ? (float)$value : 0;
                break;
            case 'bool':
                $value = in_array($value, ['1', 1, 'true', true, 'on'], true) ? '1' : '0';
                break;
            case 'json':
                $decoded = json_decode($value, true);
                if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                    Response::error("配置项 ID {$item['id']} 的 JSON 格式无效");
                }
                $value = is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
                break;
            default:
                $value = is_string($value) ? $value : (string)$value;
        }

        Database::update('config', ['value' => $value], 'id = ?', [(int)$item['id']]);
        $updated++;
    }

    Response::success(['updated' => $updated], "成功更新 {$updated} 项配置");
}

if ($method === 'POST' && $action === 'verify_toggle') {
    $input = parseJsonInput();

    $type = $input['type'] ?? '';
    $enable = $input['enable'] ?? null;

    if (!in_array($type, ['phone', 'email'], true)) {
        Response::error('无效的验证类型');
    }

    if ($enable === null) {
        Response::error('缺少 enable 参数');
    }

    $keyName = $type === 'phone' ? 'enable_phone_verify' : 'enable_email_verify';
    $value = $enable ? '1' : '0';

    $configItem = Database::fetch("SELECT id FROM config WHERE key_name = ?", [$keyName]);
    if ($configItem) {
        Database::update('config', ['value' => $value], 'id = ?', [(int)$configItem['id']]);
    } else {
        Database::insert('config', [
            'group_name'  => 'register',
            'key_name'    => $keyName,
            'value'       => $value,
            'type'        => 'bool',
            'description' => $type === 'phone' ? '是否开启手机验证' : '是否开启邮箱验证',
            'sort'        => 50,
        ]);
    }

    Response::success([
        'type'   => $type,
        'enable' => (bool)$enable,
    ], '切换成功');
}

Response::error('请求方式不允许', Response::CODE_BAD_REQUEST);