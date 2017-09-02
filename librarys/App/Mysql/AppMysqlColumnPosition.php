<?php

    namespace Librarys\App\Mysql;

    final class AppMysqlColumnPosition
    {

        const POSITION_AFTER       = 'after';
        const POSITION_SPLIT       = '-@-';
        const POSITION_AFTER_FIRST = 'first';
        const POSITION_AFTER_LAST  = 'last';

        public static function display($tableName, $lngLabelColumn, $lngLabelAfterFirst, $lngLabelAfterLast, $defaultPosition, $isPrint = true)
        {
            global $appMysqlConnect;

            if ($appMysqlConnect == null)
                return;

            $query  = $appMysqlConnect->query('SHOW COLUMNS FROM `' . addslashes($tableName) . '`');
            $buffer = null;

            $buffer .= '<option value="' . self::POSITION_AFTER_FIRST . '"';

            if ($defaultPosition == self::POSITION_AFTER_FIRST)
                $buffer .= ' selected="selected"';

            $buffer .= '>';
            $buffer .= $lngLabelAfterFirst;
            $buffer .= '</option>';

            $buffer .= '<option value="' . self::POSITION_AFTER_LAST . '"';

            if ($defaultPosition == null || empty($defaultPosition) || $defaultPosition == self::POSITION_AFTER_LAST)
                $buffer .= ' selected="selected"';

            $buffer .= '>';
            $buffer .= $lngLabelAfterLast;
            $buffer .= '</option>';

            if ($appMysqlConnect->isResource($query)) {
                $buffer .= '<optgroup label="' . $lngLabelColumn . '">';

                while ($assoc = $appMysqlConnect->fetchAssoc($query)) {
                    $key     = self::POSITION_AFTER . self::POSITION_SPLIT . $assoc['Field'];
                    $buffer .= '<option value="' . $key . '"';

                    if ($defaultPosition == $key)
                        $buffer .= ' selected="selected"';

                    $buffer .= '>';
                    $buffer .= $assoc['Field'];
                    $buffer .= '</option>';
                }

                $buffer .= '</optgroup>';
            }

            if ($isPrint == false)
                return $buffer;

            echo $buffer;
        }

        public static function isPositionValidate($position)
        {
            if (
                    $position != self::POSITION_AFTER_FIRST &&
                    $position != self::POSITION_AFTER_LAST  &&

                    preg_match('#^' . self::POSITION_AFTER . self::POSITION_SPLIT . '(.+?)$#', $position, $matches) == false
                )
            {
                return false;
            }

            if (isset($matches))
                return $matches[1];
            else
                return null;
        }

    }

?>