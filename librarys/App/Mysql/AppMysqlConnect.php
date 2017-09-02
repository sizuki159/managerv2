<?php

    namespace Librarys\App\Mysql;

    use Librarys\Boot;
    use Librarys\Database\DatabaseConnect;

    final class AppMysqlConnect extends DatabaseConnect
    {

        private $tableCurrent;
        private $columnCurrent;

        private $isDatabaseNameCustom;
        private $mysqlQueryStringCurrent;

        public function __construct(Boot $boot)
        {
            parent::__construct($boot);
        }

        public function setTableCurrent($table)
        {
            $this->tableCurrent = $table;
        }

        public function getTableCurrent()
        {
            return $this->tableCurrent;
        }

        public function checkTableCurrent()
        {
            if ($this->tableCurrent != null && $this->isConnect() && $this->isTableNameExists($this->tableCurrent))
                return true;

            return false;
        }

        public function setColumnCurrent($column)
        {
            $this->columnCurrent = $column;
        }

        public function getColumnCurrent()
        {
            return $this->column;
        }

        public function checkColumnCurrent()
        {

        }

        public function setDatabaseNameCustom($isDatabaseNameCustom)
        {
            $this->isDatabaseNameCustom = $isDatabaseNameCustom;
        }

        public function isDatabaseNameCustom()
        {
            return $this->isDatabaseNameCustom;
        }

        public function query($sql, $isCache = true)
        {
            return parent::query($this->mysqlQueryStringCurrent = $sql, $isCache);
        }

        public function getMysqlQueryExecStringCurrent()
        {
            return $this->getHost() . '@' . $this->getUsername() . ' > ' . $this->mysqlQueryStringCurrent;
        }

        public function isDatabasenameExists($databaseName, $databaseNameIgone = null, $isStringLowerCase = false, &$bufferOutput = false)
        {
            if ($isStringLowerCase) {
                $databaseName = strtolower($databaseName);

                if ($databaseNameIgone != null)
                    $databaseNameIgone = strtolower($databaseNameIgone);
            }

            $query = $this->query('SHOW DATABASES');

            if ($this->isResource($query)) {
                while ($assoc = $this->fetchAssoc($query)) {
                    $databaseNameCurrentLoop = $assoc['Database'];

                    if ($isStringLowerCase)
                        $databaseNameCurrentLoop = strtolower($databaseNameCurrentLoop);

                    if ($databaseName == $databaseNameCurrentLoop) {
                        if ($assoc != false)
                            $bufferOutput = $assoc;

                        if ($databaseNameIgone == null || $databaseNameIgone != $databaseNameCurrentLoop)
                            return true;
                    }
                }
            }

            return false;
        }

        public function isTableNameExists($tableName, $tableNameIgone = null, $isStringLowerCase = false, &$bufferOutput = false)
        {
            if ($isStringLowerCase) {
                $tableName = strtolower($tableName);

                if ($tableNameIgone != null)
                    $tableNameIgone = strtolower($tableNameIgone);
            }

            $query = $this->query('SHOW TABLE STATUS');

            if ($this->isResource($query)) {
                while ($assoc = $this->fetchAssoc($query)) {
                    $tableNameCurrentLoop = $assoc['Name'];

                    if ($isStringLowerCase)
                        $tableNameCurrentLoop = strtolower($tableNameCurrentLoop);

                    if ($tableName == $tableNameCurrentLoop) {
                        if ($assoc != false)
                            $bufferOutput = $assoc;

                        if ($tableNameIgone == null || $tableNameIgone != $tableNameCurrentLoop)
                            return true;
                    }
                }
            }

            return false;
        }

        public function isColumnNameExists($columnName, $table, $columnNameIgone = null, $isStringLowerCase = false, &$bufferOutput = false)
        {
            if ($isStringLowerCase) {
                $columnName = strtolower($columnName);

                if ($columnNameIgone != null)
                    $columnNameIgone = strtolower($columnNameIgone);
            }

            $query = $this->query('SHOW COLUMNS FROM `' . addslashes($table) . '`');

            if ($this->isResource($query)) {
                while ($assoc = $this->fetchAssoc($query)) {
                    $columnNameCurrentLoop = $assoc['Field'];

                    if ($isStringLowerCase)
                        $columnNameCurrentLoop = strtolower($columnNameCurrentLoop);

                    if ($columnName == $columnNameCurrentLoop) {
                        if ($assoc != false)
                            $bufferOutput = $assoc;

                        if ($columnNameIgone == null || $columnNameIgone != $tableNameCurrentLoop)
                            return true;
                    }
                }
            }

            return false;
        }

        public function isLengthDataValidate($length)
        {
            if (empty($length) == false && preg_match('#\\b[0-9]+\\b#', $length) != false)
                return true;

            return false;
        }

        public function getColumnKey($table)
        {
            $table = addslashes($table);
            $query = $this->query('SHOW INDEXES FROM `' . $table . '` WHERE `Key_name`="PRIMARY"');
            $key   = null;

            if ($this->numRows($query) > 0) {
                $key = $this->fetchAssoc($query);
                $key = $key['Column_name'];
            } else {
                $query = $this->query('SHOW COLUMNS FROM `' . $table . '`');
                $key   = $this->fetchAssoc($query);
                $key   = $key['Field'];
            }

            return $key;
        }

    }

?>