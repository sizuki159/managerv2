<?php

    namespace Librarys\App;

    if (defined('LOADED') == false)
        exit;

    final class AppParameter
    {

        private $parameters;
        private $parameterString;

        public function __construct()
        {
            $this->parameters = array();
        }

        public function add($name, $value, $isPrint = true)
        {
            if ($name == null || empty($name))
                return $this;

            $this->parameters[$name] = [
                'value'    => $value,
                'is_print' => $isPrint
            ];

            return $this;
        }

        public function set($name, $value, $isPrint = true)
        {
            return $this->add($name, $value, $isPrint);
        }

        public function remove($name)
        {
            if (is_array($this->parameters) && isset($this->parameters[$name])) {
                $array = array();

                foreach ($this->parameters AS $key => $value) {
                    if ($key != $name)
                        $array[$key] = $value;
                }

                $this->parameters = $array;
            }

            return $this;
        }

        public function clear()
        {
            $this->parameters      = array();
            $this->parameterString = null;
        }

        public function toString($renew = false)
        {
            if (is_array($this->parameters) == false)
                return null;

            if ($renew == false && $this->parameterString != null && empty($this->parameterString) == false)
                return $this->parameterString;

            $nums   = count($this->parameters);
            $buffer = null;

            if ($nums > 0) {
                $parameterFist  = true;
                $parameterIndex = 0;

                foreach ($this->parameters AS $name => $array) {
                    $value   = $array['value'];
                    $isPrint = $array['is_print'];

                    if ($isPrint) {
                        if ($parameterFist)
                            $buffer .= '?';
                        else
                            $buffer .= '&';

                        $buffer        .= $name;
                        $buffer        .= '=';
                        $buffer        .= $value;
                        $parameterFist  = false;
                    }
                }
            }

            return $buffer;
        }

    }

?>