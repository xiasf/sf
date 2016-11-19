<?php
namespace Core\Model;
use Core\Model;
/**
 * SF视图模型扩展
 * Update time：2015-11-5 15:08:19
 */
class ViewModel extends Model {

    protected $viewFields = array();


    /**
     * 自动检测数据表信息
     * @access protected
     * @return void
     */
    protected function _checkTableInfo() {}


    /**
     * 得到完整的数据表名
     * @access public
     * @return string
     */
    public function getTableName() {
        if (empty($this->trueTableName)) {
            $tableName = '';
            foreach ($this->viewFields as $key => $view) {
                if (isset($view['_table'])) {
                    $tableName .= $view['_table'];
                    $prefix    = $this->tablePrefix;
                    $tableName = preg_replace_callback("/__([A-Z_-]+)__/sU", function ($match) use ($prefix) {return $prefix . strtolower($match[1]);}, $tableName);
                } else {
                    $class = $key . 'Model';
                    $Model = class_exists($class) ? new $class() : M($key);
                    $tableName .= $Model->getTableName();
                }
                $tableName .= !empty($view['_as']) ? ' ' . $view['_as'] : ' ' . $key;
                $tableName .= !empty($view['_on']) ? ' ON ' . $view['_on'] : '';
                $type = !empty($view['_type']) ? ' ' . $view['_type'] : ' INNER';
                $tableName .= strtoupper($type) . ' JOIN ';
            }
            $len                 = strlen($type . ' JOIN ');
            $tableName           = substr($tableName, 0, -$len);
            $this->trueTableName = $tableName;
        }
        return $this->trueTableName;
    }


    /**
     * 表达式过滤方法
     * @access protected
     * @param string $options 表达式
     * @return void
     */
    protected function _options_filter(&$options) {
        if (isset($options['field'])) {
            $options['field'] = $this->checkFields($options['field']);
        } else {
            $options['field'] = $this->checkFields();
        }

        if (isset($options['group'])) {
            $options['group'] = $this->checkGroup($options['group']);
        }

        if (isset($options['where'])) {
            $options['where'] = $this->checkCondition($options['where']);
        }

        if (isset($options['order'])) {
            $options['order'] = $this->checkOrder($options['order']);
        }
    }


    /**
     * 检查是否定义了所有字段
     * @access protected
     * @param string $name 模型名称
     * @param array $fields 字段数组
     * @return array
     */
    private function _checkFields($name, $fields) {
        if (false !== in_array('*', $fields)) {
            $fields = M($name)->getDbFields();
        }
        return $fields;
    }


    /**
     * 检查条件中的视图字段
     * @access protected
     * @param mixed $data 条件表达式
     * @return array
     */
    protected function checkCondition($where) {
        if (is_array($where)) {
            $view = array();
            foreach ($this->viewFields as $key => $val) {
                $k   = isset($val['_as']) ? $val['_as'] : $key;
                $val = $this->_checkFields($key, $val);
                foreach ($where as $name => $value) {
                    if (false !== $field = array_search($name, $val, true)) {
                        $_key        = is_numeric($field) ? $k . '.' . $name : $k . '.' . $field;
                        $view[$_key] = $value;
                        unset($where[$name]);
                    }
                }
            }
            $where = array_merge($where, $view);
        }
        return $where;
    }


    /**
     * 检查Order表达式中的视图字段
     * @access protected
     * @param string $order 字段
     * @return string
     */
    protected function checkOrder($order = '') {
        if (is_string($order) && !empty($order)) {
            $orders = explode(',', $order);
            $_order = array();
            foreach ($orders as $order) {
                $array = explode(' ', trim($order));
                $field = $array[0];
                $sort  = isset($array[1]) ? $array[1] : 'ASC';
                foreach ($this->viewFields as $name => $val) {
                    $k   = isset($val['_as']) ? $val['_as'] : $name;
                    $val = $this->_checkFields($name, $val);
                    if (false !== $_field = array_search($field, $val, true)) {
                        $field = is_numeric($_field) ? $k . '.' . $field : $k . '.' . $_field;
                        break;
                    }
                }
                $_order[] = $field . ' ' . $sort;
            }
            $order = implode(',', $_order);
        }
        return $order;
    }


    /**
     * 检查Group表达式中的视图字段
     * @access protected
     * @param string $group 字段
     * @return string
     */
    protected function checkGroup($group = '') {
        if (!empty($group)) {
            $groups = explode(',', $group);
            $_group = array();
            foreach ($groups as $field) {
                // 解析成视图字段
                foreach ($this->viewFields as $name => $val) {
                    $k   = isset($val['_as']) ? $val['_as'] : $name;
                    $val = $this->_checkFields($name, $val);
                    if (false !== $_field = array_search($field, $val, true)) {
                        // 存在视图字段
                        $field = is_numeric($_field) ? $k . '.' . $field : $k . '.' . $_field;
                        break;
                    }
                }
                $_group[] = $field;
            }
            $group = implode(',', $_group);
        }
        return $group;
    }


    /**
     * 检查fields表达式中的视图字段
     * @access protected
     * @param string $fields 字段
     * @return string
     */
    protected function checkFields($fields = '') {
        if (empty($fields) || '*' == $fields) {
            $fields = array();
            foreach ($this->viewFields as $name => $val) {
                $k   = isset($val['_as']) ? $val['_as'] : $name;
                $val = $this->_checkFields($name, $val);
                foreach ($val as $key => $field) {
                    if (is_numeric($key)) {
                        $fields[] = $k . '.' . $field . ' AS ' . $field;
                    } elseif ('_' != substr($key, 0, 1)) {
                        if (false !== strpos($key, '*') || false !== strpos($key, '(') || false !== strpos($key, '.')) {
                            $fields[] = $key . ' AS ' . $field;
                        } else {
                            $fields[] = $k . '.' . $key . ' AS ' . $field;
                        }
                    }
                }
            }
            $fields = implode(',', $fields);
        } else {
            if (!is_array($fields)) {
                $fields = explode(',', $fields);
            }
            $array = array();
            foreach ($fields as $key => $field) {
                if (strpos($field, '(') || strpos(strtolower($field), ' as ')) {
                    $array[] = $field;
                    unset($fields[$key]);
                }
            }
            foreach ($this->viewFields as $name => $val) {
                $k   = isset($val['_as']) ? $val['_as'] : $name;
                $val = $this->_checkFields($name, $val);
                foreach ($fields as $key => $field) {
                    if (false !== $_field = array_search($field, $val, true)) {
                        if (is_numeric($_field)) {
                            $array[] = $k . '.' . $field . ' AS ' . $field;
                        } elseif ('_' != substr($_field, 0, 1)) {
                            if (false !== strpos($_field, '*') || false !== strpos($_field, '(') || false !== strpos($_field, '.')) {
                                $array[] = $_field . ' AS ' . $field;
                            } else {
                                $array[] = $k . '.' . $_field . ' AS ' . $field;
                            }
                        }
                    }
                }
            }
            $fields = implode(',', $array);
        }
        return $fields;
    }
}