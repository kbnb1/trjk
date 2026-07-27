<?php

namespace App;

use PDO;
use PDOException;

/**
 * 数据库操作类
 *
 * 使用 PDO 单例模式，提供连接、查询、事务等常用数据库操作。
 *
 * @package App
 */
class Database
{
    /** @var PDO|null PDO 实例 */
    private static ?PDO $pdo = null;

    /** @var array 配置数组 */
    private static array $config = [];

    /**
     * 设置数据库配置
     *
     * @param array $config 数据库配置
     */
    public static function setConfig(array $config): void
    {
        self::$config = $config;
    }

    /**
     * 获取 PDO 实例（单例）
     *
     * @return PDO
     * @throws PDOException
     */
    public static function connect(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $cfg = self::$config;

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['host'],
            $cfg['port'] ?? 3306,
            $cfg['database'],
            $cfg['charset'] ?? 'utf8mb4'
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . ($cfg['charset'] ?? 'utf8mb4'),
        ];

        self::$pdo = new PDO($dsn, $cfg['username'], $cfg['password'], $options);

        return self::$pdo;
    }

    /**
     * 执行查询语句并返回 PDOStatement
     *
     * @param string $sql  SQL 语句
     * @param array  $params 绑定参数
     * @return \PDOStatement
     */
    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * 获取单条记录
     *
     * @param string $sql    SQL 语句
     * @param array  $params 绑定参数
     * @return array|null
     */
    public static function fetch(string $sql, array $params = []): ?array
    {
        $stmt = self::query($sql, $params);
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }

    /**
     * 获取所有记录
     *
     * @param string $sql    SQL 语句
     * @param array  $params 绑定参数
     * @return array
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * 插入记录
     *
     * @param string $table 表名
     * @param array  $data  字段 => 值 数组
     * @return int 最后插入的 ID
     */
    public static function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})";
        self::query($sql, array_values($data));

        return (int) self::connect()->lastInsertId();
    }

    /**
     * 更新记录
     *
     * @param string $table   表名
     * @param array  $data    字段 => 值 数组
     * @param string $where   WHERE 条件
     * @param array  $params  绑定参数
     * @return int 受影响的行数
     */
    public static function update(string $table, array $data, string $where, array $params = []): int
    {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "`{$column}` = ?";
        }

        $sql = "UPDATE `{$table}` SET " . implode(', ', $set) . " WHERE {$where}";
        $stmt = self::query($sql, array_merge(array_values($data), $params));

        return $stmt->rowCount();
    }

    /**
     * 删除记录
     *
     * @param string $table  表名
     * @param string $where  WHERE 条件
     * @param array  $params 绑定参数
     * @return int 受影响的行数
     */
    public static function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM `{$table}` WHERE {$where}";
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * 开启事务
     *
     * @return bool
     */
    public static function beginTransaction(): bool
    {
        return self::connect()->beginTransaction();
    }

    /**
     * 提交事务
     *
     * @return bool
     */
    public static function commit(): bool
    {
        return self::connect()->commit();
    }

    /**
     * 回滚事务
     *
     * @return bool
     */
    public static function rollback(): bool
    {
        return self::connect()->rollBack();
    }

    /**
     * 关闭数据库连接
     */
    public static function close(): void
    {
        self::$pdo = null;
    }

    /**
     * 防止实例化
     */
    private function __construct() {}

    /**
     * 防止克隆
     */
    private function __clone() {}
}