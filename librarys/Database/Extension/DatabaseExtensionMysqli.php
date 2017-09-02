<?php

    namespace Librarys\Database\Extension;

    if (defined('LOADED') == false)
        exit;

    use Librarys\Database\DatabaseConnect;

    final class DatabaseExtensionMysqli extends DatabaseExtensionInterface
    {

        public function __construct(DatabaseConnect $databaseConnect)
        {
            parent::__construct($databaseConnect);
        }

        public function connect($host, $username, $password, $name, $port)
        {
            return @mysqli_connect(
                $host,
                $username,
                $password,
                $name,
                $port
            );
        }

        public function disconnect()
        {
            return @mysqli_close($this->getDatabaseConnect()->getResource());
        }

        public function isConnect()
        {
            return $this->isResource($this->getDatabaseConnect()->getResource());
        }

        public function isResource($resource)
        {
            return is_resource($resource) || is_object($resource);
        }

        public function isSupportExtension()
        {
            return function_exists('mysqli_connect');
        }

        public function query($sql)
        {
            return @mysqli_query($this->getDatabaseConnect()->getResource(), $sql);
        }

        public function freeResult($result)
        {
            if ($this->isResource($result))
                return @mysqli_free_result($result);

            return false;
        }

        public function fetchAssoc($sqlOrQuery)
        {
            if ($this->isResource($sqlOrQuery))
                return @mysqli_fetch_assoc($sqlOrQuery);
            else
                return @mysqli_fetch_assoc($this->query($sqlOrQuery));
        }

        public function fetchArray($sqlOrQuery)
        {
            if ($this->isResource($sqlOrQuery))
                return @mysqli_fetch_array($sqlOrQuery);
            else
                return @mysqli_fetch_array($this->query($sqlOrQuery));
        }

        public function fetchRow($sqlOrQuery)
        {
            if ($this->isResource($sqlOrQuery))
                return @mysqli_fetch_row($sqlOrQuery);
            else
                return @mysqli_fetch_row($this->query($sqlOrQuery));
        }

        public function numRows($sqlOrQuery)
        {
            if ($this->isResource($sqlOrQuery))
                return @mysqli_num_rows($sqlOrQuery);
            else
                return @mysqli_num_rows($this->query($sqlOrQuery));
        }

        public function numFields($sqlOrQuery)
        {
            if ($this->isResource($sqlOrQuery))
                return @mysqli_num_fields($sqlOrQuery);
            else
                return @mysqli_num_fields($this->query($sqlOrQuery));
        }

        public function dataSeek($sqlOrQuery, $rowNumber = null)
        {
            if ($this->isResource($sqlOrQuery))
                return @mysqli_data_seek($sqlOrQuery, $rowNumber);
            else
                return @mysqli_data_seek($this->query($sqlOrQuery), $rowNumber);
        }

        public function insertId()
        {
            return @mysqli_insert_id();
        }

        public function fieldName($sqlOrQuery, $fieldOffset = 0)
        {
            if ($this->isResource($sqlOrQuery))
                $result = @mysqli_fetch_field_direct($sqlOrQuery, $fieldOffset);
            else
                $result = @mysqli_fetch_field_direct($this->query($sqlOrQuery), $fieldOffset);

            if ($result)
                return $result->name;

            return false;
        }

        public function fieldType($sqlOrQuery, $fieldOffset = 0)
        {
            if ($this->isResource($sqlOrQuery))
                $result = @mysqli_fetch_field_direct($sqlOrQuery, $fieldOffset);
            else
                $result = @mysqli_fetch_field_direct($this->query($sqlOrQuery), $fieldOffset);

            if ($result)
                return $result->type;

            return false;
        }

        public function setCharset($charset)
        {
            return @mysqli_set_charset($charset);
        }

        public function errorConnect()
        {
            return @mysqli_connect_error();
        }

        public function error()
        {
            return @mysqli_error($this->getDatabaseConnect()->getResource());
        }

        public function getExtensionType()
        {
            return 'mysqli';
        }

    }

?>