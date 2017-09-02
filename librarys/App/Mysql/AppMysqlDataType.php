<?php

    namespace Librarys\App\Mysql;

    final class AppMysqlDataType
    {

        private static $array = [
            'Numeric' => [
                'TINYINT',
                'SMALLINT',
                'MEDIUMINT',
                'INT',
                'BIGINT',
                'DECIMAL',
                'FLOAT',
                'DOUBLE',
                'BIT'
            ],

            'String' => [
                'CHAR',
                'VARCHAR',
                'BINARY',
                'VARBINARY',
                'TINYBLOB',
                'BLOB',
                'MEDIUMBLOB',
                'LONGBLOB',
                'TINYTEXT',
                'TEXT',
                'MEDIUMTEXT',
                'LONGTEXT',
                'ENUM',
                'SET'
            ],

            'Date/Time' => [
                'DATA',
                'TIME',
                'DATETIME',
                'TIMESTAMP',
                'YEAR'
            ]
        ];

        private static $arrayTypeHasLength = [
            'DATE',
            'DATETIME',
            'TIME',
            'TINYBLOB',
            'TINYTEXT',
            'BLOB',
            'TEXT',
            'MEDIUMBLOB',
            'MEDIUMTEXT',
            'LONGBLOB',
            'LONGTEXT',
            'SERIAL',
            'BOOLEAN',
            'UUID'
        ];

        const DATA_TYPE_NONE    = 'none';
        const DATA_TYPE_DEFAULT = 'VARCHAR';

        public static function display($lngDataTypeNone = null, $defaultDataType = null, $isPrint = true)
        {
            $buffer = null;

            if ($defaultDataType == null)
                $defaultDataType = self::getDefault();

            if (is_array(self::$array) == false || count(self::$array) <= 0) {
                $buffer .= '<option value="' . self::DATA_TYPE_NONE . '">';
                $buffer .= $lngDataTypeNone;
                $buffer .= '</option>';
            } else {
                foreach (self::$array AS $label => $type) {
                    $buffer .= '<optgroup label="' . $label . '">';

                    foreach ($type AS $value) {
                        $buffer .= '<option value="' . $value . '"';

                        if ($defaultDataType != null && $defaultDataType == $value)
                            $buffer .= ' selected="selected"';

                        $buffer .= '>';
                        $buffer .= $value;
                        $buffer .= '</option>';
                    }

                    $buffer .= '</optgroup>';
                }
            }

            if ($isPrint == false)
                return $buffer;

            echo $buffer;
        }

        public static function isHasLength($type)
        {
            foreach (self::$arrayTypeHasLength AS $value) {
                if (strcasecmp($value, $type) === 0)
                    return false;
            }

            return true;
        }

        public static function isNumeric($type)
        {
            if (is_array(self::$array)         == false ||
                count(self::$array)            <= 0     ||
                isset(self::$array['Numeric']) == false)
            {
                return false;
            }

            return in_array(strtoupper($type), self::$array['Numeric']);
        }

        public static function getDefault()
        {
            return self::DATA_TYPE_DEFAULT;
        }

    }

?>