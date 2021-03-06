<?php
namespace BaseApp\Model;
use Zend\Config\Config;
abstract class ModelAbstract
{
    public function __construct($options = null)
    {
        if ($options instanceof Config) {
            $options = $options->toArray();
        }
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function setOptions(array $options)
    {
        $classMethods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = $this->_fieldToMethod($key);
            if (in_array($method, $classMethods)) {
                $this->$method($value);
            }
        }
        return $this;
    }
    
    private function _fieldToMethod($name)
    {
        return 'set' . $this->_toCamelCase($name);
    }

    private function _toCamelCase($name)
    {
        return implode('',array_map('ucfirst', explode('_',$name)));
    }

    private function _fromCamelCase($name)
    {
        return trim(preg_replace_callback('/([A-Z])/', function($c){ return '_'.strtolower($c[1]); }, $name),'_');
    }

    public function toArray($array = false)
    {
        $array = $array ?: get_object_vars($this);
        foreach ($array as $key => $value) {
            unset($array[$key]);
            $key = $this->_fromCamelCase($key);
            if (is_object($value)) {
                $array[$key] = $value->toArray();
            } elseif (is_array($value) && count($value) > 0) {
                $array[$key] = $this->toArray($value);
            } elseif ($value !== NULL && !is_array($value)) {
                $array[$key] = $value;
            }
        }
        return $array;
    }
}
