<?php

    namespace Librarys\Database;

    if (defined('LOADED') == false)
        exit;

    use Librarys\File\FileInfo;

    final class DatabaseBackupRestore
    {

        private $databaseConnect;
        private $backupBuffer;
        private $backupFilename;
        private $isBackupCreateFile;

        private $restoreFilename;

        const MIME = 'sql';

        public function __construct(DatabaseConnect $databaseConnect)
        {
            $this->databaseConnect = $databaseConnect;
        }

        public function autoScanClean()
        {
            $directory = env('app.path.backup_mysql');

            if (FileInfo::isTypeDirectory($directory) == false)
                return false;

            $handle = FileInfo::scanDirectory($directory);

            if ($handle === false)
                return false;

            foreach ($handle AS $filename) {
                $extFile = FileInfo::extFile($filename);

                if (strcasecmp($extFile, self::MIME) !== 0)
                    FileInfo::unlink(FileInfo::filterPaths($directory . SP . $filename));
            }
        }

        public function setBackupFilename($filename)
        {
            $this->backupFilename = $filename;
        }

        public function setRestoreFilename($filename)
        {
            $this->restoreFilename = $filename;
        }

        // Clone content in https://github.com/aniketan/backup-restore
        public function backupInfomation()
        {
            $this->backupBuffer .= "# MySQL database backup\n";
            $this->backupBuffer .= "#\n";
            $this->backupBuffer .= "# Generated: " . date( 'l j. F Y H:i T' ) . "\n";
            $this->backupBuffer .= "# Hostname: " . $this->databaseConnect->getHost() . "\n";
            $this->backupBuffer .= "# Database: " . $this->sqlBackquote($this->databaseConnect->getName()) . "\n";
            $this->backupBuffer .= "# --------------------------------------------------------\n";

            return true;
        }

        // Clone content in https://github.com/aniketan/backup-restore
        public function backupTable($tableName)
        {
            // Create the SQL statements
            $this->backupBuffer .= "# --------------------------------------------------------\n";
            $this->backupBuffer .= "# Table: " . $this->sqlBackquote($tableName) . "\n";
            $this->backupBuffer .= "# --------------------------------------------------------\n";

            // Add SQL statement to drop existing table
            $this->backupBuffer .= "\n";
            $this->backupBuffer .= "\n";
            $this->backupBuffer .= "#\n";
            $this->backupBuffer .= "# Delete any existing table " . $this->sqlBackquote($tableName) . "\n";
            $this->backupBuffer .= "#\n";
            $this->backupBuffer .= "\n";
            $this->backupBuffer .= "DROP TABLE IF EXISTS " . $this->sqlBackquote($tableName) . ";\n";

            /* Table Structure */

            // Comment in SQL-file
            $this->backupBuffer .= "\n";
            $this->backupBuffer .= "\n";
            $this->backupBuffer .= "#\n";
            $this->backupBuffer .= "# Table structure of table " . $this->sqlBackquote($tableName) . "\n";
            $this->backupBuffer .= "#\n";
            $this->backupBuffer .= "\n";

            $query = $this->databaseConnect->query('SHOW CREATE TABLE ' . $this->sqlBackquote($tableName), false);

            if ($this->databaseConnect->isResource($query)) {
                if ($this->databaseConnect->numRows($query) > 0)
                    $this->backupBuffer .= $this->databaseConnect->fetchArray($query)[1];

                $this->databaseConnect->freeResult($query);
                $this->backupBuffer .= ';';
            }

            /* Table Contents */

            // Get table contents
            $query       = $this->databaseConnect->query('SELECT * FROM ' . $this->sqlBackquote($tableName), false);
            $fieldsCount = 0;
            $rowsCount   = 0;

            if ($this->databaseConnect->isResource($query)) {
                $fieldsCount = $this->databaseConnect->numFields($query);
                $rowsCount   = $this->databaseConnect->numRows($query);
            }

            // Comment in SQL-file
            $this->backupBuffer .= "\n";
            $this->backupBuffer .= "\n";
            $this->backupBuffer .= "#\n";
            $this->backupBuffer .= "# Data contents of table " . $this->sqlBackquote($tableName) . " (" . $rowsCount . " records)\n";
            $this->backupBuffer .= "#\n";

            // Checks whether the field is an integer or not
            for ( $j = 0; $j < $fieldsCount; $j++ ) {
                $fieldSet[$j] = $this->sqlBackquote($this->databaseConnect->fieldName($query, $j));
                $fieldType    = $this->databaseConnect->fieldType($query, $j);

                //if ( $type === 'tinyint' || $type === 'smallint' || $type === 'mediumint' || $type === 'int' || $type === 'bigint'  || $type === 'timestamp')
                # Remove timestamp to avoid error while restore
                if ($fieldType === 'tinyint' || $fieldType === 'smallint' || $fieldType === 'mediumint' || $fieldType === 'int' || $fieldType === 'bigint')
                    $fieldNum[$j] = true;
                else
                    $fieldNum[$j] = false;
            }

            // Sets the scheme
            $entries  = 'INSERT INTO ' . $this->sqlBackquote($tableName) . ' VALUES (';
            $search   = array( '\x00', '\x0a', '\x0d', '\x1a' );  //\x08\\x09, not required
            $replace  = array( '\0', '\n', '\r', '\Z' );
            $currentRow = 0;
            $batchWrite = 0;

            while ($row = $this->databaseConnect->fetchRow($query)) {
                $currentRow++;

                // build the statement
                for ($j = 0; $j < $fieldsCount; $j++) {
                    if (isset($row[$j]) == false) {
                        $values[] = 'NULL';
                    } else if ($row[$j] === '0' || $row[$j] !== '') {
                        // a number
                        if ($fieldNum[$j])
                            $values[] = $row[$j];
                        else
                            $values[] = '\'' . str_replace($search, $replace, $this->sqlAddslashes($row[$j], false, true)) . '\'';

                    } else {
                        $values[] = '\'\'';
                    }
                }

                $this->backupBuffer .= " \n" . $entries . implode(', ', $values) . ") ;";

                // write the rows in batches of 100
                if ($batchWrite === 100) {
                    $batchWrite = 0;

                    if ($this->backupWrite() == false)
                        return false;
                }

                $batchWrite++;
                unset($values);
            }

            $this->databaseConnect->freeResult($query);

            // Create footer/closing comment in SQL-file
            $this->backupBuffer .= "\n";
            $this->backupBuffer .= "#\n";
            $this->backupBuffer .= "# End of data contents of table " . $this->sqlBackquote($tableName) . "\n";
            $this->backupBuffer .= "# --------------------------------------------------------\n";
            $this->backupBuffer .= "\n";

            return $this->backupWrite();
        }

        private function backupWrite()
        {
            $directory = env('app.path.backup_mysql');

            if (FileInfo::mkdir($directory, true) == false)
                return false;

            $path   = $this->getPathFileDatabaseBackup();
            $handle = null;

            if ($this->isBackupCreateFile == false) {
                $this->isBackupCreateFile = true;
                $handle = FileInfo::fileOpen($path, 'w+');
            } else {
                $handle = FileInfo::fileOpen($path, 'a');
            }

            if ($handle !== false) {
                if (FileInfo::fileWrite($handle, $this->backupBuffer) === false) {
                    FileInfo::fileFlush($handle);
                    FileInfo::fileClose($handle);

                    return false;
                } else {
                    $this->backupBuffer = '';
                }

                return FileInfo::fileFlush($handle) && FileInfo::fileClose($handle);
            }

            return false;
        }

        public function restore($pathCustom = null)
        {
            $filename = null;
            $filepath = null;

            if ($pathCustom)
                $filename = basename($pathCustom);
            else
                $filename = $this->restoreFilename;

            if (empty($filename) || $filename == null)
                return false;


            if ($pathCustom)
                $filepath = FileInfo::filterPaths($pathCustom);
            else
                $filepath = FileInfo::filterPaths($this->getPathFileDatabaseBackup($filename));

            if (FileInfo::isTypeFile($filepath) == false)
                return false;

            $lines = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if ($lines === false)
                return false;

            $buffer = '';

            foreach ($lines as $line) {
                // Skipping comments
                if (substr(ltrim($line), 0, 2) == '--' || $line[0] == '#')
                    continue;

                // Skip empty lines
                if (($line = trim($line)) == '') {
                    continue;
                }

                // Multiline query
                else if($line[strlen($line) - 1] != ";") {
                    $buffer .= $line;
                    continue;
                } else if ($buffer) {
                    $line = $buffer . $line;
                    $buffer = '';
                }

                if ($this->databaseConnect->query($line) == false)
                    return false;
            }

            return true;
        }

        private function sqlBackquote($name)
        {
            if (empty($name) == false && $name !== '*') {
                if (is_array($name)) {
                    $result = array();
                    reset($name);

                    while (list($key, $val) = each($name))
                        $result[$key] = '`' . $val . '`';

                    return $result;
                } else {
                    return '`' . $name . '`';
                }
            } else {
                return $name;
            }
        }

        private function sqlAddslashes($string = '', $isLike = false, $crlf = false, $phpCode = false)
        {
            if ($isLike)
                $string = str_replace('\\', '\\\\\\\\', $string);
            else
                $string = str_replace('\\', '\\\\', $string);

            if ($crlf) {
                $string = str_replace("\r\n", "\\r\\n", $string);
                $string = str_replace("\r",   "\\r",    $string);
                $string = str_replace("\n",   "\\n",    $string);
            }

            if ($phpCode)
                $string = str_replace('\'', '\\\'', $string);
            else
                $string = str_replace('\'', '\'\'', $string);

            return $string;
        }

        public function getPathDirectoryDatabaseBackup()
        {
            return env('app.path.backup_mysql');
        }

        public function getPathFileDatabaseBackup($filename = null)
        {
            if ($filename == null)
                $filename = $this->backupFilename;

            return $this->getPathDirectoryDatabaseBackup() . SP . $filename;
        }

        public function getRestoreDatabaseRecordCount()
        {
            $directory = $this->getPathDirectoryDatabaseBackup();
            $count     = 0;

            if (FileInfo::isTypeDirectory($directory)) {
                $handle = FileInfo::scanDirectory($directory);

                if ($handle !== false) {
                    foreach ($handle AS $entry) {
                        if ($entry != '.' && $entry != '..' && strcasecmp(FileInfo::extFile($entry), self::MIME) === 0)
                            $count++;
                    }
                }
            }

            return $count;
        }

        public static function isFilenameValidate($filename)
        {
            return strcasecmp(FileInfo::extFile($filename), self::MIME) === 0;
        }

    }

?>