<?php

    namespace Librarys\Database;

    if (defined('LOADED') == false)
        exit;

    final class DatabaseSchema
    {

        private $databaseTable;
        private $array;
        private $drop;
        private $engine;

        public function __construct(DatabaseTable $databaseTable)
        {
            $this->databaseTable = $databaseTable;
            $this->array         = array();
        }

        private function validateType($column, $length, $default, $hasSize = true, $validateInt = false)
        {
            if ($column == null || empty($column))
                return trigger_error('Column name is null');

            if ($validateInt && $default != null && is_int($default) == false)
                return trigger_error('Default is right int');

            if ($hasSize && ($length == null || empty($length) || $length <= 0))
                return trigger_error('Length varchar is zero');

            return true;
        }

        public function clear()
        {
            $this->array = array();
        }

        public function execute(DatabaseConnect $connect = null)
        {
            if ($connect == null)
                $connect = $this->databaseTable->getDatabaseConnect();

            $table    = $connect->getPrefix() . $this->databaseTable->getTable();
            $encoding = $connect->getEncoding();

            if ($encoding == null)
                $encoding = 'utf-8';

            if ($this->drop) {
                $connect->query('DROP TABLE `' . $table . '`');
                $this->drop = false;
            } else {
                $sql    = 'CREATE TABLE `' . $table . '` (';
                $length = count($this->array);

                for ($i = 0; $i < $length; ++$i) {
                    $entry = $this->array[$i];

                    $sql .= '`' . $entry['column'] . '`';

                    if ($entry['type'] == 'text')
                        $sql .= ' ' . strtoupper($entry['sql']);
                    else
                        $sql .= ' ' . strtoupper($entry['sql']) . '(' . $entry['length'] . ')';

                    if (isset($entry['default']) && ($entry['default'] != null || $entry['default'] == 0))
                        $sql .= ' DEFAULT "' . $entry['default'] . '"';

                    if (isset($entry['null']) && $entry['null'] == false)
                        $sql .= ' NOT NULL';
                    else
                        $sql .= ' NULL';

                    if (isset($entry['increment']) && $entry['increment'] == true)
                        $sql .= ' AUTO_INCREMENT';

                    if (isset($entry['key'])) {
                        if ($entry['key'] == 'primary')
                            $sql .= ' PRIMARY KEY';
                    }

                    if ($i + 1 < $length)
                        $sql .= ',';
                }

                $sql .= ')';

                if ($encoding != null)
                    $sql .= ' DEFAULT CHARACTER SET=' . $encoding;

                if ($this->engine == null)
                    $sql .= ' ENGINE=MyISAM';
                else
                    $sql .= ' ENGINE=' . $this->engine;

                $sql .= ';';

                if ($connect->query($sql) == false)
                    return trigger_error($connect->error());
            }
        }

        public function drop()
        {
            $this->drop = true;
        }

        public function unsigned()
        {
            if (is_array($this->array) && count($this->array) > 0) {
                $end = &$this->array[count($this->array) - 1];

                if (isset($end['type']) && $end['type'] == 'int')
                    $end['attribute'] = 'unsigned';
                else
                    return trigger_error('Column "' . $end['column'] . '" is not right type int');

                return $this;
            }

            return null;
        }

        public function notnull()
        {
            if (is_array($this->array) && count($this->array) > 0) {
                $end = &$this->array[count($this->array) - 1];
                $end['null'] = false;

                return $this;
            }

            return null;
        }

        public function null()
        {
            if (is_array($this->array) && count($this->array) > 0) {
                $end = &$this->array[count($this->array) - 1];
                $end['null'] = true;

                return $this;
            }

            return null;
        }

        public function primarykey($column = null)
        {
            if ($column == null || empty($column)) {
                if (is_array($this->array) && count($this->array) > 0) {
                    $end = &$this->array[count($this->array) - 1];
                    $end['key'] = 'primary';

                    return $this;
                }
            } else {
                foreach ($this->array AS &$entry) {
                    if ($entry['column'] == $column) {
                        $entry['key'] = 'primary';

                        return $this;
                    }
                }

                return trigger_error('Column "' . $column . '" set key not exists');
            }

            return $this;
        }

        public function increment($column = null)
        {
            if ($column == null || empty($column)) {
                if (is_array($this->array) && count($this->array) > 0) {
                    $end = &$this->array[count($this->array) - 1];
                    $end['increment'] = true;

                    return $this;
                }
            } else {
                foreach ($this->array AS &$entry) {
                    if ($entry['column'] == $column) {
                        $entry['increment'] = true;

                        return $this;
                    }
                }

                return trigger_error('Column "' . $column . '" set auto increment not exists');
            }

            return $this;
        }

        public function engineMyISAM()
        {
            $this->engine = 'MyISAM';
        }

        public function engineInnoDB()
        {
            $this->engine = 'InnoDB';
        }

        public function engineMEMORY()
        {
            $this->engine = 'MEMORY';
        }

        public function engineMERGE()
        {
            $this->engine = 'MERGE';
        }

        public function engineEXAMPLE()
        {
            $this->engine = 'EXAMPLE';
        }

        public function engineARCHIVE()
        {
            $this->engine = 'ARCHIVE';
        }

        public function engineCSV()
        {
            $this->engine = 'CSV';
        }

        public function engineBLACKHOLE()
        {
            $this->engine = 'BLACKHOLE';
        }

        public function engineFEDERATED()
        {
            $this->engine = 'FEDERATED';
        }

        public function tinyint($column, $length, $default = null)
        {
            if ($this->validateType($column, $length, $default, true, true)) {
                $this->array[] = [
                    'type'    => 'int',
                    'sql'     => 'tinyint',
                    'column'  => $column,
                    'length'  => $length,
                    'default' => $default
                ];

                return $this;
            }

            return null;
        }

        public function smallint($column, $length, $default = null)
        {
            if ($this->validateType($column, $length, $default, true, true)) {
                $this->array[] = [
                    'type'    => 'int',
                    'sql'     => 'smallint',
                    'column'  => $column,
                    'length'  => $length,
                    'default' => $default
                ];

                return $this;
            }

            return null;
        }

        public function mediumint($column, $length, $default = null)
        {
            if ($this->validateType($column, $length, $default, true, true)) {
                $this->array[] = [
                    'type'    => 'int',
                    'sql'     => 'mediumint',
                    'column'  => $column,
                    'length'  => $length,
                    'default' => $default
                ];

                return $this;
            }

            return null;
        }

        public function int($column, $length, $default = null)
        {
            if ($this->validateType($column, $length, $default, true, true)) {
                $this->array[] = [
                    'type'    => 'int',
                    'sql'     => 'int',
                    'column'  => $column,
                    'length'  => $length,
                    'default' => $default
                ];

                return $this;
            }

            return null;
        }

        public function bigint($column, $length, $default = null)
        {
            if ($this->validateType($column, $length, $default, true, true)) {
                $this->array[] = [
                    'type'    => 'int',
                    'sql'     => 'bigint',
                    'column'  => $column,
                    'length'  => $length,
                    'default' => $default
                ];

                return $this;
            }

            return null;
        }

        public function char($column, $length, $default = null)
        {
            if ($this->validateType($column, $length, $default, true, false)) {
                $this->array[] = [
                    'type'    => 'char',
                    'sql'     => 'char',
                    'column'  => $column,
                    'length'  => $length,
                    'default' => $default
                ];

                return $this;
            }

            return null;
        }

        public function varchar($column, $length, $default = null)
        {
            if ($this->validateType($column, $length, $default, true, false)) {
                $this->array[] = [
                    'type'    => 'char',
                    'sql'     => 'varchar',
                    'column'  => $column,
                    'length'  => $length,
                    'default' => $default
                ];

                return $this;
            }

            return null;
        }

    }

?>