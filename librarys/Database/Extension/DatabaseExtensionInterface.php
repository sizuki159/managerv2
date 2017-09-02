<?php

    namespace Librarys\Database\Extension;

    if (defined('LOADED') == false)
        exit;

    use Librarys\Database\DatabaseConnect;

    abstract class DatabaseExtensionInterface
    {

        private $databaseConnect;

        public function __construct(DatabaseConnect $databaseConnect)
        {
            $this->databaseConnect = $databaseConnect;
        }

        public function getDatabaseConnect()
        {
            return $this->databaseConnect;
        }

        public abstract function connect($host, $username, $password, $name, $port);

        public abstract function disconnect();

        public abstract function isConnect();

        public abstract function isResource($isResource);

        public abstract function isSupportExtension();

        public abstract function query($sql);

        public abstract function freeResult($result);

        public abstract function fetchAssoc($sqlOrQuery);

        public abstract function fetchArray($sqlOrQuery);

        public abstract function fetchRow($sqlOrQuery);

        public abstract function numRows($sqlOrQuery);

        public abstract function numFields($sqlOrQuery);

        public abstract function dataSeek($sqlOrQuery);

        public abstract function insertId();

        public abstract function fieldName($sqlOrQuery, $fieldOffset);

        public abstract function fieldType($sqlOrQuery, $fieldOffset);

        public abstract function setCharset($charset);

        public abstract function errorConnect();

        public abstract function error();

        public abstract function getExtensionType();

    }

?>