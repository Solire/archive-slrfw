<?php

namespace Slrfw\Library;

/** @todo faire la présentation du code */

class MyPDOStatement extends \PDOStatement {

    protected $bound_params;

    public function execute($allParams = array(), $bindMode = MyPDO::BINDMODE_PARAM) {
        if (!in_array($bindMode, array(MyPDO::BINDMODE_VALUE, MyPDO::BINDMODE_PARAM))) {
            $this->throwError('Unknow bind mode "<b>' . $bindMode . '</b>".');
            return FALSE;
        } else {
            $last_marker = 0;
            foreach ($allParams as $marker => $value) {
                if (!is_string($marker))
                    $marker = ++$last_marker;
                $type = MyPDO::PARAM_STR;
                if (is_int($value))
                    $type = MyPDO::PARAM_INT;
                $this->$bindMode($marker, $value, $type);
            }
            return parent::execute();
        }
    }

    protected function throwError($error_message, $code = NULL) {
        if ($this->getAttribute(MyPDO::ATTR_ERRMODE) == MyPDO::ERRMODE_EXCEPTION)
            throw new MyPDOException($error_message, $code);
        elseif ($this->getAttribute(MyPDO::ATTR_ERRMODE) == MyPDO::ERRMODE_WARNING)
            trigger_error($error_message, E_WARNING);
    }

    public function bindValue($parameter, $value, $data_type = MyPDO::PARAM_STR) {
        if ($data_type == MyPDO::PARAM_INT)
            $value = (int) $value;
        $this->setBoundParams($parameter, $value, $data_type);
        return parent::bindValue($parameter, $value, $data_type);
    }

    public function bindParam($parameter, &$variable, $data_type = null, $length = null, $driver_options = null) {
        if ($data_type == MyPDO::PARAM_INT)
            $variable = (int) $variable;
        $this->setBoundParams($parameter, $variable, $data_type);
        return parent::bindParam($parameter, $variable, $data_type, $length, $driver_options);
    }

    protected function setBoundParams($name, $value, $data_type = MyPDO::PARAM_STR) {
        if ($data_type == MyPDO::PARAM_STR)
            $value = "'$value'";
        if (is_string($name) AND $name[0] != ':')
            $name = ':' . $name;
        $this->bound_params[$name] = $value;
    }

    public function getBuiltQuery() {
        $res = $this->queryString;
        if (count($this->bound_params)) {
            if (is_int(key($this->bound_params))) {
                $res = str_replace('?', '%s', $this->queryString);
                foreach ($this->bound_params as &$p)
                    $p = (string) $p;
                unset($p);
                $vals = $this->bound_params;
                for ($i = count($this->bound_params), $max = substr_count($this->queryString, '?'); $i < $max; $i++)
                    $vals[] = '?';
                $res = vsprintf($res, $vals);
            } elseif (is_string(key($this->bound_params))) {
                $res = str_replace(array_keys($this->bound_params), $this->bound_params, $this->queryString);
            }
        }
        return $res;
    }

}
