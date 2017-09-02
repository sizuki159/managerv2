<?php

    namespace Librarys\App\Mysql;

    use Librarys\Database\DatabaseQuery;

    final class AppMysqlQueryCreate extends DatabaseQuery
    {

        public function __construct($command = null, $table = null)
        {
            parent::__construct($GLOBALS['appMysqlConnect'], $command, $table);
        }

    }

?>