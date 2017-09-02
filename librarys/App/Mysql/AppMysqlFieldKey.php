<?php

    namespace Librarys\App\Mysql;

    final class AppMysqlFieldKey
    {

        private static $array = [
            'PRIMARY KEY' => 'Primary',
            'INDEX'       => 'Index',
            'UINQUE'      => 'Unique'
        ];

        const FIELD_KEY_NONE    = 'none';
        const FIELD_KEY_DEFAULT = 'PRIMARY KEY';

        public static function display($nameRadioCheck, $lngFieldKeyNone = null, $defaultFieldKey = null, $isPrint = true)
        {
            $buffer = null;

            if ($defaultFieldKey == null)
                $defaultFieldKey = self::getDefault();

            if (is_array(self::$array) == false || count(self::$array) <= 0) {
                $buffer .= '<li>';
                    $buffer .= '<input type="radio" name="' . $nameRadioCheck . '" value="' . self::FIELD_KEY_NONE . '" id="' . self::FIELD_KEY_NONE . '" class="radio-entry-list"/>';
                    $buffer .= '<label for="' . self::FIELD_KEY_NONE . '">';
                        $buffer .= '<span>' . $lngFieldKeyNone . '</span>';
                    $buffer .= '</label>';
                $buffer .= '</li>';
            } else {
                $buffer .= '<li>';
                    $buffer .= '<input type="radio" name="' . $nameRadioCheck . '" value="' . self::FIELD_KEY_NONE . '" id="' . self::FIELD_KEY_NONE . '" class="radio-entry-list"';

                    if ($defaultFieldKey != null && $defaultFieldKey == self::FIELD_KEY_NONE)
                        $buffer .= ' checked="checked"';

                    $buffer .= '/>';
                    $buffer .= '<label for="' . self::FIELD_KEY_NONE . '">';
                        $buffer .= '<span>' . $lngFieldKeyNone . '</span>';
                    $buffer .= '</label>';
                $buffer .= '</li>';

                foreach (self::$array AS $key => $value) {
                    $buffer .= '<li>';
                        $buffer .= '<input type="radio" name="' . $nameRadioCheck . '" value="' . $key . '" id="' . $key . '" class="radio-entry-list"';

                        if ($defaultFieldKey != null && $defaultFieldKey == $key)
                            $buffer .= ' checked="checked"';

                        $buffer .= '/>';
                        $buffer .= '<label for="' . $key . '">';
                            $buffer .= '<span>' . $value . '</span>';
                        $buffer .= '</label>';
                    $buffer .= '</li>';
                }
            }

            if ($isPrint == false)
                return $buffer;

            echo $buffer;
        }

        public static function getDefault()
        {
            return self::FIELD_KEY_DEFAULT;
        }

        public static function isTableHasFieldKey($tableName)
        {
            global $appMysqlConnect;

            if ($appMysqlConnect == null)
                return false;

            return $appMysqlConnect->numRows('SHOW INDEXES FROM `' . $tableName . '` WHERE `Key_name`="PRIMARY"') > 0;
        }

    }

?>