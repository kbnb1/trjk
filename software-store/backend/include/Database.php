<?php
/**
 * 数据库操作类（PDO 单例模式）
 * 提供参数化查询、获取、插入、更新、删除等常用方法
 */
class Database
{
    /** @var PDO|null PDO 实例 */
    private static $instance = null;

    /** @var PDOStatement|null 当前预编译语句 */
    private $statement = null;

    /**
     * 私有构造，禁止外部实例化
     */
    private function __construct()
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // 抛出异常
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // 关联数组
            PDO::ATTR_EMULATE_PREPARES   => false,                   // 关闭预处理模拟
            PDO::ATTR_PERSISTENT         => false,                   // 非持久连接
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode([
                'code'    => 500,
                'message' => '数据库连接失败：' . $e->getMessage(),
                'data'    => null,
                'time'    => time(),
            ]));
        }
    }

    /**
     * 禁止克隆
     */
    private function __clone() {}

    /**
     * 获取单例实例
     * @return Database
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 获取原生 PDO 对象
     * @return PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * 执行 SQL 查询（参数化）
     * @param string $sql    SQL 语句
     * @param array  $params 绑定参数
     * @return $this
     */
    public function query($sql, $params = [])
    {
        $this->statement = $this->pdo->prepare($sql);
        // 统一处理布尔值绑定
        foreach ($params as $key => $value) {
            $param = is_int($key) ? $key + 1 : $key;
            $type  = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $this->statement->bindValue($param, $value, $type);
        }
        $this->statement->execute();
        return $this;
    }

    /**
     * 获取单条记录
     * @param string $sql    SQL 语句
     * @param array  $params 绑定参数
     * @return array|false
     */
    public function fetch($sql, $params = [])
    {
        $this->query($sql, $params);
        return $this->statement->fetch();
    }

    /**
     * 获取全部记录
     * @param string $sql    SQL 语句
     * @param array  $params 绑定参数
     * @return array
     */
    public function fetchAll($sql, $params = [])
    {
        $this->query($sql, $params);
        return $this->statement->fetchAll();
    }

    /**
     * 插入数据
     * @param string $table  表名
     * @param array  $data   关联数组（字段 => 值）
     * @return int|false     插入的ID，失败返回 false
     */
    public function insert($table, $data)
    {
        if (empty($data)) {
            return false;
        }
        $fields = array_keys($data);
        $placeholders = array_map(function ($f) {
            return ':' . $f;
        }, $fields);

        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            $table,
            implode('`, `', $fields),
            implode(', ', $placeholders)
        );

        // 构建绑定参数
        $params = [];
        foreach ($data as $field => $value) {
            $params[':' . $field] = $value;
        }

        $this->query($sql, $params);
        $id = $this->pdo->lastInsertId();
        return $id ? (int) $id : false;
    }

    /**
     * 更新数据
     * @param string $table       表名
     * @param array  $data        待更新字段（字段 => 值）
     * @param string $where       条件语句（不含 WHERE 关键字）
     * @param array  $whereParams 条件参数
     * @return int                受影响的行数
     */
    public function update($table, $data, $where, $whereParams = [])
    {
        if (empty($data) || empty($where)) {
            return 0;
        }

        $setParts = [];
        $params   = [];
        foreach ($data as $field => $value) {
            $key = ':' . $field;
            $setParts[] = "`{$field}` = {$key}";
            $params[$key] = $value;
        }

        $sql = sprintf('UPDATE `%s` SET %s WHERE %s', $table, implode(', ', $setParts), $where);
        $params = array_merge($params, $whereParams);

        $this->query($sql, $params);
        return $this->statement->rowCount();
    }

    /**
     * 删除数据
     * @param string $table       表名
     * @param string $where       条件语句（不含 WHERE 关键字）
     * @param array  $whereParams 条件参数
     * @return int                受影响的行数
     */
    public function delete($table, $where, $whereParams = [])
    {
        if (empty($where)) {
            return 0; // 安全防护：禁止无条件删除
        }
        $sql = sprintf('DELETE FROM `%s` WHERE %s', $table, $where);
        $this->query($sql, $whereParams);
        return $this->statement->rowCount();
    }

    /**
     * 获取总记录数
     * @param string $table       表名
     * @param string $where       条件语句
     * @param array  $whereParams 条件参数
     * @return int
     */
    public function count($table, $where = '1=1', $whereParams = [])
    {
        $sql = sprintf('SELECT COUNT(*) AS cnt FROM `%s` WHERE %s', $table, $where);
        $row = $this->fetch($sql, $whereParams);
        return $row ? (int) $row['cnt'] : 0;
    }

    /**
     * 获取单个字段值
     * @param string $sql    SQL 语句
     * @param array  $params 绑定参数
     * @return mixed|null
     */
    public function value($sql, $params = [])
    {
        $row = $this->fetch($sql, $params);
        if ($row) {
            return reset($row);
        }
        return null;
    }

    /**
     * 事务开启
     */
    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    /**
     * 事务提交
     */
    public function commit()
    {
        $this->pdo->commit();
    }

    /**
     * 事务回滚
     */
    public function rollBack()
    {
        $this->pdo->rollBack();
    }

    /**
     * 引号转义（用于无法使用预处理的场景）
     * @param string $value
     * @return string
     */
    public function quote($value)
    {
        return $this->pdo->quote($value);
    }
}
