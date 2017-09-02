<?php

    namespace Librarys\App\Mysql;

    use Librarys\App\Mysql\AppMysqlConnect;

    final class AppMysqlEngineStorage
    {

        private static $array;
        private static $default;

        const ENGINE_STORAGE_NONE    = 'none';
        const ENGINE_STORAGE_DEFAULT = 'MyISAM';

        public static function display($lngEngineStorageNone = null, $defaultEngineStorage = null, $isPrint = true)
        {
            $buffer = null;
            self::receiver();

            if ($defaultEngineStorage == null)
                $defaultEngineStorage = self::getDefault();

            if (is_array(self::$array) == false || count(self::$array) <= 0) {
                $buffer .= '<option value="' . self::ENGINE_STORAGE_NONE . '">';
                $buffer .= $lngEngineStorageNone;
                $buffer .= '</option>';
            } else {
                foreach (self::$array AS $engine) {
                    $buffer .= '<option value="' . $engine . '"';

                        if ($defaultEngineStorage != null && $defaultEngineStorage == $engine)
                            $buffer .= ' selected="selected"';

                        $buffer .= '>';
                        $buffer .= $engine;
                    $buffer .= '</option>';
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
                return self::ENGINE_STORAGE_DEFAULT;

            if (is_array(self::$array))
                return self::$default;

            $query = $appMysqlConnect->query('SELECT * FROM `information_schema`.`ENGINES`');

            if ($appMysqlConnect->isResource($query)) {
                while ($assoc = $appMysqlConnect->fetchAssoc($query)) {
                    if (strcasecmp($assoc['SUPPORT'], 'default') === 0) {
                        self::$default = $assoc['ENGINE'];
                        self::$array[] = $assoc['ENGINE'];
                    } else if (strcasecmp($assoc['SUPPORT'], 'yes') === 0) {
                        self::$array[] = $assoc['ENGINE'];
                    }
                }

                if (self::$default == null || empty(self::$default))
                    self::$default = self::ENGINE_STORAGE_DEFAULT;
            }

            return self::$default;
        }

        public static function getDefault()
        {
            return self::receiver();
        }
    }

?>