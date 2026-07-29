<?php
/**
 * 输入验证类
 * 支持链式调用，提供 required/string/email/phone/length 等验证规则
 */
class Validator
{
    /** @var array 待验证的数据 */
    protected $data = [];

    /** @var array 验证规则 */
    protected $rules = [];

    /** @var array 错误信息 */
    protected $errors = [];

    /** @var array 字段中文名映射 */
    protected $labels = [];

    /**
     * 构造函数
     * @param array $data 待验证数据
     */
    public function __construct($data = [])
    {
        $this->data = $data;
    }

    /**
     * 设置待验证数据
     * @param array $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * 设置字段中文名
     * @param array $labels
     * @return $this
     */
    public function setLabels($labels)
    {
        $this->labels = $labels;
        return $this;
    }

    /**
     * 添加验证规则
     * @param string $field 字段名
     * @param string|array $rules 规则
     * @return $this
     */
    public function rule($field, $rules)
    {
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }
        $this->rules[$field] = $rules;
        return $this;
    }

    /**
     * 批量添加规则
     * @param array $rules [field => rules]
     * @return $this
     */
    public function rules($rules)
    {
        foreach ($rules as $field => $rule) {
            $this->rule($field, $rule);
        }
        return $this;
    }

    /**
     * 执行验证
     * @return bool
     */
    public function validate()
    {
        $this->errors = [];
        foreach ($this->rules as $field => $rules) {
            $value = isset($this->data[$field]) ? $this->data[$field] : null;
            $label = isset($this->labels[$field]) ? $this->labels[$field] : $field;
            foreach ($rules as $rule) {
                $this->applyRule($field, $value, $rule, $label);
                if (isset($this->errors[$field])) {
                    break; // 该字段已有错误，跳过后续规则
                }
            }
        }
        return empty($this->errors);
    }

    /**
     * 应用单条规则
     * @param string $field 字段名
     * @param mixed  $value 字段值
     * @param string $rule  规则
     * @param string $label 字段中文名
     */
    protected function applyRule($field, $value, $rule, $label)
    {
        // 处理带参数的规则，如 length:6,20
        $params = [];
        if (strpos($rule, ':') !== false) {
            list($rule, $paramStr) = explode(':', $rule, 2);
            $params = explode(',', $paramStr);
        }

        switch ($rule) {
            case 'required':
                if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                    $this->errors[$field] = "{$label}不能为空";
                }
                break;
            case 'string':
                if ($value !== null && $value !== '' && !is_string($value)) {
                    $this->errors[$field] = "{$label}必须为字符串";
                }
                break;
            case 'integer':
                if ($value !== null && $value !== '' && !preg_match('/^-?\d+$/', $value)) {
                    $this->errors[$field] = "{$label}必须为整数";
                }
                break;
            case 'email':
                if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field] = "{$label}格式不正确";
                }
                break;
            case 'phone':
                if ($value !== null && $value !== '' && !preg_match('/^1[3-9]\d{9}$/', $value)) {
                    $this->errors[$field] = "{$label}格式不正确";
                }
                break;
            case 'url':
                if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->errors[$field] = "{$label}格式不正确";
                }
                break;
            case 'length':
                $min = isset($params[0]) ? (int) $params[0] : 0;
                $max = isset($params[1]) ? (int) $params[1] : PHP_INT_MAX;
                $len = is_string($value) ? mb_strlen($value) : 0;
                if ($value !== null && $value !== '' && ($len < $min || $len > $max)) {
                    $this->errors[$field] = "{$label}长度需在{$min}-{$max}之间";
                }
                break;
            case 'min':
                $min = isset($params[0]) ? (int) $params[0] : 0;
                if ($value !== null && $value !== '' && mb_strlen($value) < $min) {
                    $this->errors[$field] = "{$label}长度不能少于{$min}";
                }
                break;
            case 'max':
                $max = isset($params[0]) ? (int) $params[0] : PHP_INT_MAX;
                if ($value !== null && $value !== '' && mb_strlen($value) > $max) {
                    $this->errors[$field] = "{$label}长度不能超过{$max}";
                }
                break;
            case 'in':
                if ($value !== null && $value !== '' && !in_array($value, $params, true)) {
                    $this->errors[$field] = "{$label}取值不合法";
                }
                break;
            case 'boolean':
                if ($value !== null && $value !== '' && !in_array($value, [0, 1, '0', '1', true, false], true)) {
                    $this->errors[$field] = "{$label}必须为布尔值";
                }
                break;
        }
    }

    /**
     * 获取所有错误信息
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * 获取第一条错误信息
     * @return string
     */
    public function getFirstError()
    {
        return !empty($this->errors) ? reset($this->errors) : '';
    }

    /**
     * 静态快速验证
     * @param array $data  数据
     * @param array $rules 规则
     * @param array $labels 字段中文名
     * @return array [pass, error]
     */
    public static function check($data, $rules, $labels = [])
    {
        $validator = new self($data);
        $validator->setLabels($labels);
        $validator->rules($rules);
        $pass = $validator->validate();
        return [$pass, $validator->getFirstError()];
    }
}
