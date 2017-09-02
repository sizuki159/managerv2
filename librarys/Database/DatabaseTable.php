<?php

	namespace Librarys\Database;

    if (defined('LOADED') == false)
        exit;

	abstract class DatabaseTable
	{

        protected $connect;
        protected $table;
        protected $schema;

		public function __construct(DatabaseConnect $connect, $table)
		{
            $this->connect = $connect;
            $this->table   = $table;
		}

        public function execute()
        {
            $this->schema = new DatabaseSchema($this);

            $this->schema->clear();
            $this->drop($this->schema);
            $this->schema->execute($this->connect);

            $this->schema->clear();
            $this->create($this->schema);
            $this->schema->execute($this->connect);

            $this->insert($this->connect);

            echo('<strong style="color: green">Execute database table "' . $this->table . '" success</strong><br/>');
        }

        public function getTable()
        {
            return $this->table;
        }

        public function getDatabaseConnect()
        {
            return $this->connect;
        }

        public function insert(DatabaseConnect $connect)
        {

        }

        public abstract function create(DatabaseSchema $schema);

        public abstract function drop(DatabaseSchema $schema);

	}

?>