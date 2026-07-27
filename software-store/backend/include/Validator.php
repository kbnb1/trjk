<?php

namespace App;

/**
 * 输入验证类
 *
 * 支持链式调用，最后通过 check() 返回错误信息数组。
 *
 * @package App
 */
class Validator
{
    /** @var array 待验证的数据 */
    private array $data = [];

    /** @var array 验证规则 */
    private array $rules = [];

    /** @var array 自定义错误消息 */
    private array $messages = [];

    /** @var string 当前验证字段名 */
    private string $currentField = '';

    /** @var array 验证错误收集 */
    private array $errors = [];

    /** @var string|null 当前字段标签（用于错误消息） */
    private ?string $currentLabel = null;

    /**
     * 构造函数
     *
     * @param array $data 待验证的数据
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * 指定验证字段
     *
     * @param string $field 字段名
     * @param string $label 字段标签（可选）
     * @return $this
     */
    public function field(string $field, string $label = ''): self
    {
        $this->currentField = $field;
        $this->currentLabel = $label ?: $field;
        return $this;
    }

    /**
     * 自定义错误消息
     *
     * @param string $rule    规则名
     * @param string $message 错误消息
     * @return $this
     */
    public function message(string $rule, string $message): self
    {
        $this->messages[$rule] = $message;
        return $this;
    }

    /**
     * 标记当前字段为必填
     *
     * @return $this
     */
    public function required(): self
    {
        $this->rules[$this->currentField][] = ['rule' => 'required', 'params' => []];
        return $this;
    }

    /**
     * 验证字符串类型及最大长度
     *
     * @param int $max 最大长度（0 表示不限制）
     * @return $this
     */
    public function string(int $max = 0): self
    {
        $this->rules[$this->currentField][] = ['rule' => 'string', 'params' => ['max' => $max]];
        return $this;
    }

    /**
     * 验证邮箱格式
     *
     * @return $this
     */
    public function email(): self
    {
        $this->rules[$this->currentField][] = ['rule' => 'email', 'params' => []];
        return $this;
    }

    /**
     * 验证手机号
     *
     * @return $this
     */
    public function phone(): self
    {
        $this->rules[$this->currentField][] = ['rule' => 'phone', 'params' => []];
        return $this;
    }

    /**
     * 验证最小值
     *
     * @param int $len 最小长度或数值
     * @return $this
     */
    public function min(int $len): self
    {
        $this->rules[$this->currentField][] = ['rule' => 'min', 'params' => ['len' => $len]];
        return $this;
    }

    /**
     * 验证最大值
     *
     * @param int $len 最大长度或数值
     * @return $this
     */
    public function max(int $len): self
    {
        $this->rules[$this->currentField][] = ['rule' => 'max', 'params' => ['len' => $len]];
        return $this;
    }

    /**
     * 验证整数
     *
     * @return $this
     */
    public function integer(): self
    {
        $this->rules[$this->currentField][] = ['rule' => 'integer', 'params' => []];
        return $this;
    }

    /**
     * 验证 URL 格式
     *
     * @return $this
     */
    public function url(): self
    {
        $this->rules[$this->currentField][] = ['rule' => 'url', 'params' => []];
        return $this;
    }

    /**
     * 验证是否在允许值列表中
     *
     * @param array $values 允许的值
     * @return $this
     */
    public function in(array $values): self
    {
        $this->rules[$this->currentField][] = ['rule' => 'in', 'params' => ['values' => $values]];
        return $this;
    }

    /**
     * 正则表达式验证
     *
     * @param string $pattern 正则表达式
     * @return $this
     */
    public function regex(string $pattern): self
    {
        $this->rules[$this->currentField][] = ['rule' => 'regex', 'params' => ['pattern' => $pattern]];
        return $this;
    }

    /**
     * 自定义闭包验证
     *
     * @param \Closure $callback 闭包
     * @return $this
     */
    public function custom(\Closure $callback): self
    {
        $this->rules[$this->currentField][] = ['rule' => 'custom', 'params' => ['callback' => $callback]];
        return $this;
    }

    /**
     * 执行验证并返回错误信息
     *
     * @return array 错误数组，空数组表示通过
     */
    public function check(): array
    {
        $this->errors = [];

        foreach ($this->rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;
            $label = $this->getLabel($field);

            foreach ($fieldRules as $ruleDef) {
                $rule = $ruleDef['rule'];
                $params = $ruleDef['params'];

                if (!$this->validateRule($rule, $value, $params, $field, $label)) {
                    break;
                }
            }
        }

        return $this->errors;
    }

    /**
     * 获取验证结果（true 表示全部通过）
     *
     * @return bool
     */
    public function passes(): bool
    {
        return empty($this->check());
    }

    /**
     * 获取字段标签
     *
     * @param string $field 字段名
     * @return string
     */
    private function getLabel(string $field): string
    {
        return $this->currentLabel ?? $field;
    }

    /**
     * 执行单条规则验证
     *
     * @param string $rule  规则名
     * @param mixed  $value 待验证值
     * @param array  $params 规则参数
     * @param string $field 字段名
     * @param string $label 字段标签
     * @return bool
     */
    private function validateRule(string $rule, $value, array $params, string $field, string $label): bool
    {
        switch ($rule) {
            case 'required':
                if ($value === null || $value === '' || (is_string($value) && trim($value) === '')) {
                    $this->addError($field, $this->getMessage($rule, "{$label}不能为空"));
                    return false;
                }
                break;

            case 'string':
                if ($value !== null && $value !== '') {
                    if (!is_string($value)) {
                        $this->addError($field, $this->getMessage($rule, "{$label}必须为字符串"));
                        return false;
                    }
                    if ($params['max'] > 0 && mb_strlen($value) > $params['max']) {
                        $this->addError($field, $this->getMessage($rule, "{$label}长度不能超过{$params['max']}个字符"));
                        return false;
                    }
                }
                break;

            case 'email':
                if ($value !== null && $value !== '') {
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->addError($field, $this->getMessage($rule, "{$label}格式不正确"));
                        return false;
                    }
                }
                break;

            case 'phone':
                if ($value !== null && $value !== '') {
                    if (!preg_match('/^1[3-9]\d{9}$/', $value)) {
                        $this->addError($field, $this->getMessage($rule, "{$label}格式不正确"));
                        return false;
                    }
                }
                break;

            case 'min':
                if ($value !== null && $value !== '') {
                    if (is_numeric($value)) {
                        if ((float) $value < $params['len']) {
                            $this->addError($field, $this->getMessage($rule, "{$label}不能小于{$params['len']}"));
                            return false;
                        }
                    } else {
                        if (mb_strlen($value) < $params['len']) {
                            $this->addError($field, $this->getMessage($rule, "{$label}长度不能少于{$params['len']}个字符"));
                            return false;
                        }
                    }
                }
                break;

            case 'max':
                if ($value !== null && $value !== '') {
                    if (is_numeric($value)) {
                        if ((float) $value > $params['len']) {
                            $this->addError($field, $this->getMessage($rule, "{$label}不能大于{$params['len']}"));
                            return false;
                        }
                    } else {
                        if (mb_strlen($value) > $params['len']) {
                            $this->addError($field, $this->getMessage($rule, "{$label}长度不能超过{$params['len']}个字符"));
                            return false;
                        }
                    }
                }
                break;

            case 'integer':
                if ($value !== null && $value !== '') {
                    if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                        $this->addError($field, $this->getMessage($rule, "{$label}必须为整数"));
                        return false;
                    }
                }
                break;

            case 'url':
                if ($value !== null && $value !== '') {
                    if (!filter_var($value, FILTER_VALIDATE_URL)) {
                        $this->addError($field, $this->getMessage($rule, "{$label}格式不正确"));
                        return false;
                    }
                }
                break;

            case 'in':
                if ($value !== null && $value !== '') {
                    if (!in_array($value, $params['values'], true)) {
                        $this->addError($field, $this->getMessage($rule, "{$label}必须为指定值之一"));
                        return false;
                    }
                }
                break;

            case 'regex':
                if ($value !== null && $value !== '') {
                    if (!preg_match($params['pattern'], $value)) {
                        $this->addError($field, $this->getMessage($rule, "{$label}格式不正确"));
                        return false;
                    }
                }
                break;

            case 'custom':
                if ($value !== null && $value !== '') {
                    $callback = $params['callback'];
                    $result = $callback($value);
                    if ($result !== true) {
                        $this->addError($field, is_string($result) ? $result : "{$label}验证失败");
                        return false;
                    }
                }
                break;
        }

        return true;
    }

    /**
     * 添加错误
     *
     * @param string $field   字段名
     * @param string $message 错误消息
     */
    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * 获取自定义消息或默认消息
     *
     * @param string $rule   规则名
     * @param string $default 默认消息
     * @return string
     */
    private function getMessage(string $rule, string $default): string
    {
        return $this->messages[$rule] ?? $default;
    }
}