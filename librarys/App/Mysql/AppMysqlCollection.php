<?php

    namespace Librarys\App\Mysql;

    use Librarys\Database\DatabaseConnect;
    use Librarys\Database\DatabaseQuery;
    use Librarys\App\Mysql\AppMysqlConnect;

    final class AppMysqlCollection
    {

        private static $array;
        private static $default;

        const COLLECTION_TABLE           = 'COLLATIONS';
        const COLLECTION_NONE            = 'none';
        const COLLECTION_SPLIT           = '-@-';
        const COLLECTION_CHARSET_DEFAULT = 'utf8';

        const ARRAY_KEY_CHARACTER_SET_NAME = 'CHARACTER_SET_NAME';
        const ARRAY_KEY_COLLATION_NAME     = 'COLLATION_NAME';
        const ARRAY_KEY_IS_DEFAULT         = 'IS_DEFAULT';

        public static function display($lngCollectionNone = null, $defaultCollection = null, $isPrint = true)
        {
            $buffer = null;
            self::receiver();

            if ($defaultCollection == null)
                $defaultCollection = self::getDefault();

            if (is_array(self::$array) == false || count(self::$array) <= 0) {
                $buffer .= '<option value="' . self::COLLECTION_NONE . '">';
                $buffer .= $lngCollectionNone;
                $buffer .= '</option>';
            } else {
                $buffer .= '<option value="' . self::COLLECTION_NONE . '"';

                if ($defaultCollection != null && $defaultCollection == self::COLLECTION_NONE)
                    $buffer .= ' selected="selected"';

                $buffer .= '>';
                $buffer .= '</option>';

                foreach (self::$array AS $charset => $collection) {
                    $buffer .= '<optgroup label="' . $charset . '">';

                    foreach ($collection AS $collate) {
                        $collectionCurrent = $charset . self::COLLECTION_SPLIT . $collate[self::ARRAY_KEY_COLLATION_NAME];
                        $buffer           .= '<option value="' . $collectionCurrent . '"';

                        if ($defaultCollection != null && $defaultCollection == $collectionCurrent)
                            $buffer .= ' selected="selected"';

                        $buffer .= '>';
                        $buffer .= $collate[self::ARRAY_KEY_COLLATION_NAME];
                        $buffer .= '</option>';
                    }

                    $buffer .= '</optgroup>';
                }
            }

            if ($isPrint == false)
                return $buffer;

            echo $buffer;
        }

        private static function receiver()
        {
            global $appMysqlConnect;

            if ($appMysqlConnect == null)
                return self::COLLECTION_CHARSET_DEFAULT;

            if (is_array(self::$array))
                return self::$default;

            $query = new DatabaseQuery($appMysqlConnect, DatabaseQuery::COMMAND_SELECT, DatabaseConnect::DATABASE_INFORMATION . '.' . self::COLLECTION_TABLE);

            if ($query->query()) {
                self::$array = array();

                while ($assoc = $query->fetchAssoc()) {
                    $isDefault = strcasecmp($assoc[self::ARRAY_KEY_IS_DEFAULT], 'Yes') === 0;

                    self::$array[$assoc[self::ARRAY_KEY_CHARACTER_SET_NAME]][] = [
                        self::ARRAY_KEY_COLLATION_NAME => $assoc[self::ARRAY_KEY_COLLATION_NAME],
                        self::ARRAY_KEY_IS_DEFAULT     => $isDefault
                    ];

                    if ($isDefault                               &&
                        self::$default == null                   &&
                        self::COLLECTION_CHARSET_DEFAULT != null &&
                        strcasecmp($assoc[self::ARRAY_KEY_CHARACTER_SET_NAME], self::COLLECTION_CHARSET_DEFAULT) === 0)
                    {
                        self::$default = self::COLLECTION_CHARSET_DEFAULT .
                                         self::COLLECTION_SPLIT .
                                         $assoc[self::ARRAY_KEY_COLLATION_NAME];
                    }
                }

                if (self::$default == null || empty(self::$default))
                    self::$default = self::COLLECTION_CHARSET_DEFAULT;
            }

            return self::$default;
        }

        public static function getDefault()
        {
            return self::receiver();
        }

        public static function convertCollationToCollection($collation)
        {
            $indexShift = strpos($collation, '_');

            if ($indexShift === false)
                return self::getDefault();

            return substr($collation, 0, $indexShift). self::COLLECTION_SPLIT . $collation;
        }

        public static function isValidate($collection, &$character = null, &$collate = null)
        {
            if (preg_match('/^(.+?)' . self::COLLECTION_SPLIT . '(.+?)$/is', $collection, $matches)) {
                $character = $matches[1];
                $collate   = $matches[2];

                return true;
            }

            return false;
        }

    }

?>