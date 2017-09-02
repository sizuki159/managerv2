<?php

	namespace Librarys\Database;

    if (defined('LOADED') == false)
        exit;

    use Librarys\Boot;

	class DatabaseQuery
	{

		private $database;
		private $query;

		private $command;
		private $select;
		private $table;
		private $data;
		private $update;
		private $where;
		private $order;
		private $limit;

		const COMMAND_SELECT      = 'SELECT';
		const COMMAND_UPDATE      = 'UPDATE';
		const COMMAND_DELETE      = 'DELETE';
		const COMMAND_INSERT_INTO = 'INSERT INTO';

		const FUNCTION_AVG    = 'AVG';
		const FUNCTION_COUNT  = 'COUNT';
		const FUNCTION_FIRST  = 'FIRST';
		const FUNCTION_LAST   = 'LAST';
		const FUNCTION_MAX    = 'MAX';
		const FUNCTION_MIN    = 'MIN';
		const FUNCTION_UCASE  = 'UCASE';
		const FUNCTION_LCASE  = 'LCASE';
		const FUNCTION_MID    = 'MID';
		const FUNCTION_LEN    = 'LEN';
		const FUNCTION_ROUND  = 'ROUND';
		const FUNCTION_NOW    = 'NOW';
		const FUNCTION_FORMAT = 'FORMAT';

		const OPERATOR_EQUAL         = '=';
		const OPERATOR_NOT_EQUAL     = '<>';
		const OPERATOR_GREATER       = '>';
		const OPERATOR_LESS          = '<';
		const OPERATOR_GREATER_EQUAL = '>=';
		const OPERATOR_LESS_EQUAL    = '<=';
		const OPERATOR_BETWEEN       = 'BETWEEN';
		const OPERATOR_LIKE          = 'LIKE';
		const OPERATOR_NOT_LIKE      = 'NOT LIKE';
		const OPERATOR_IN            = 'IN';

		const WHERE_AND = 'AND';
		const WHERE_OR  = 'OR';

		const ORDER_ASC  = 'ASC';
		const ORDER_DESC = 'DESC';

		public function __construct($bootOrDatabaseInstance, $command = null, $table = null)
		{
            if ($bootOrDatabaseInstance instanceof Boot)
                $bootOrDatabaseInstance = $bootOrDatabaseInstance->getDatabaseConnect();
            else if ($bootOrDatabaseInstance instanceof DatabaseConnect == false)
                trigger_error('Params not instanceof Boot or DatabaseConnect');

			$this->setDatabase($bootOrDatabaseInstance);
			$this->setCommand($command);
			$this->setTable($table);
            $this->clear();
		}

        public function clear()
        {
            $this->query  = null;
            $this->select = array();
            $this->data   = array();
            $this->where  = array();
            $this->order  = array();
            $this->update = null;
            $this->limit  = null;
        }

		public function setDatabase(DatabaseConnect $database)
		{
			if ($database instanceof DatabaseConnect == false)
				trigger_error('Variable $database is null');

			$this->database = $database;
		}

		public function addSelect($column, $as = null, $function = null)
		{
			if ($column == null)
				trigger_error('Column is null');

			$this->select[$column] = array(
				'function' => $function,
				'as'       => $as
			);

			return $this;
		}

		public function setSelect($column, $as = null, $function = null)
		{
			return $this->addSelect($column, $as, $function);
		}

		public function removeSelect($column)
		{
			if (array_key_exists($column, $this->select)) {
				$select = array();

				foreach ($this->select AS $key => $value) {
					if (strcmp($column, $key) !== 0)
						$select[$key] = $value;
				}

				$this->select = $select;
			}

			return $this;
		}

		public function setCommand($command)
		{
			$this->command = $command;
		}

		public function getCommand()
		{
			return $this->command;
		}

		public function setTable($table)
		{
            if (($index = strpos($table, '.')) !== false) {
                $begin = substr($table, 0, $index);
                $end   = substr($table, $index + 1);

                $this->table = $begin;
                $this->table .= '`.`';
                $this->table .= $this->database->getPrefix();
                $this->table .= $end;
            } else {
                $this->table  = $this->database->getPrefix();
    			$this->table .= $table;
            }
		}

		public function getTable()
		{
			return $this->table;
		}

		public function addData($column, $value)
		{
			if ($column == null)
				trigger_error('Column is null');

			$this->data[$column] = $value;
		}

		public function setData($column, $value)
		{
			$this->addData($column, $value);
		}

		public function removeData($column)
		{
			if (array_key_exists($column, $this->data)) {
				$data = array();

				foreach ($this->data AS $key => $value) {
					if (strcmp($column, $key) !== 0)
						$data[$key] = $value;
				}

				$this->data = $data;
			}
		}

		public function addWhere($column, $value, $operator = self::OPERATOR_EQUAL, $where = self::WHERE_AND)
		{
			if ($column == null)
				trigger_error('Column is null');

			$this->where[$column] = array(
				'operator' => $operator,
				'value'    => $value,
				'where'    => $where
			);

			return $this;
		}

		public function setWhere($column, $value, $operator = self::OPERATOR_EQUAL, $where = self::WHERE_AND)
		{
			return $this->addWhere($column, $value, $operator, $where);
		}

		public function removeWhere($column)
		{
			if (array_key_exists($column, $this->where)) {
				$where = array();

				foreach ($this->where AS $key => $value) {
					if (strcmp($column, $key) !== 0)
						$where[$key] = $value;
				}

				$this->where = $where;
			}
		}

		public function addOrderBy($column, $order = self::ORDER_ASC)
		{
			if ($column == null)
				trigger_error('Column is null');

			$this->order[$column] = $order;
		}

		public function setOrderBy($column, $order = self::ORDER_ASC)
		{
			$this->addOrderBy($column, $order);
		}

		public function removeOrderBy($column)
		{
			if (array_key_exists($column, $this->order)) {
				$order = array();

				foreach ($this->order AS $key => $value) {
					if (strcmp($column, $key) !== 0)
						$order[$key] = $value;
				}

				$this->order = $order;
			}
		}

		public function setLimit($start, $offset = null)
		{
			$this->limit = array(
				'start'  => $start,
				'offset' => $offset
			);
		}

		public function removeLimit()
		{
			$this->limit = null;
		}

		public function toSql()
		{
			if ($this->command == null)
				trigger_error('Command is null');

			if ($this->table == null)
				trigger_error('Table is null');

			$sql = $this->command;

			if ($this->command == self::COMMAND_SELECT) {
                $sql .= ' ';

				if (count($this->select) <= 0) {
					$sql .= '*';
				} else {
					$index  = 0;
					$length = count($this->select);

					foreach ($this->select AS $key => $value) {
						$entry = '`' . $key . '`';

						if ($value['as'] != null)
							$entry = '`' . $key . '` AS `' . $value['as'] . '`';

						if ($value['function'] != null)
							$entry = $value['function'] . '(' . $entry . ')';

						if (++$index < $length)
							$entry .= ', ';

						$sql .= ' ' . $entry;
					}
				}
			}

			if ($this->command == self::COMMAND_SELECT || $this->command == self::COMMAND_DELETE)
				$sql .= ' FROM';

			$sql .= ' `' . $this->table . '`';

			if ($this->command == self::COMMAND_UPDATE) {
				$sql .= ' SET ';

				$index  = 0;
				$length = count($this->data);

				if ($length <= 0)
					trigger_error('Entry insert is zero');

				foreach ($this->data AS $key => $value) {
					$entry  = '`' . $key . '`';
					$entry .= '=';
					$entry .= '\'' . $value . '\'';

					if (++$index < $length)
						$entry .= ', ';

					$sql .= $entry;
				}
			} else if ($this->command == self::COMMAND_INSERT_INTO) {
				$index  = 0;
				$length = count($this->data);

				if ($length <= 0)
					trigger_error('Entry insert is zero');

				$columns = '(';
				$values  = '(';

				foreach ($this->data AS $key => $value) {
					$columns .= '`' . $key . '`';
					$values  .= '\'' . $value . '\'';

					if (++$index < $length) {
						$columns .= ', ';
						$values  .= ', ';
					}
				}

				$columns .= ')';
				$values  .= ')';

				$sql     .= $columns . ' VALUES ' . $values;
			}

			if ($this->command != self::COMMAND_INSERT_INTO && count($this->where) > 0) {
				$index  = 0;
				$length = count($this->where);

				foreach ($this->where AS $key => $value) {
					$entry = null;

					if ($index == 0)
						$entry .= ' WHERE';
					else if ($value['where'] == null)
						$entry .= ' ' . self::WHERE_AND;
					else
						$entry .= ' ' . $value['where'];

					$entry .= ' `' . $key . '`';

					if ($value['operator'] == null)
						$entry .= self::OPERATOR_EQUAL;
					else
						$entry .= $value['operator'];

					$entry .= ' \'' . $value['value'] . '\'';
					$sql   .= $entry;

					$index++;
				}
			}

			if ($this->command == self::COMMAND_SELECT) {
				$index  = 0;
				$length = count($this->order);

				if ($length > 0) {
					$sql .= ' ORDER BY ';

					foreach ($this->order as $key => $value) {
						$entry = '`' . $key . '`';

						if ($value == null)
							$entry .= ' ' . self::ORDER_ASC;
						else
							$entry .= ' ' . $value;

						if (++$index < $length)
							$entry .= ', ';

						$sql .= $entry;
					}
				}
			}

			if ($this->command != self::COMMAND_INSERT_INTO && is_array($this->limit)) {
				$sql .= ' LIMIT';
				$sql .= ' ' . $this->limit['start'];

				if ($this->limit['offset'] != null)
					$sql .= ', ' . $this->limit['offset'];
			}

			return $sql;
		}

		public function query()
		{
			return ($this->query = $this->database->query($this->toSql()));
		}

        public function error()
        {
            return $this->database->error();
        }

		public function fetchAssoc($modify = false)
		{
			if ($this->command != self::COMMAND_SELECT)
				return false;

			if ($this->query == null || $modify)
				return $this->database->fetchAssoc($this->query());
			else
				return $this->database->fetchAssoc($this->query);
		}

		public function fetchRow($modify = false)
		{
			if ($this->command != self::COMMAND_SELECT)
				return false;

			if ($this->query == null || $modify)
				return $this->database->fetchRow($this->query());
			else
				return $this->database->fetchRow($this->query);
		}

		public function numRows($modify = false)
		{
			if ($this->command != self::COMMAND_SELECT)
				return false;

			if ($this->query == null || $modify)
				return $this->database->numRows($this->query());
			else
				return $this->database->numRows($this->query);
		}

		public function numFields($modify = false)
		{
			if ($this->command != self::COMMAND_SELECT)
				return false;

			if ($this->query == null || $modify)
				return $this->database->numFields($this->query());
			else
				return $this->database->numFields($this->query);
		}

		public function dataSeek($modify = false)
		{
			if ($this->command != self::COMMAND_SELECT)
				return false;

			if ($this->query == null || $modify)
				return $this->database->dataSeek($this->query());
			else
				return $this->database->dataSeek($this->query);
		}

		public function insertId($modify = false)
		{
			return $this->database->insertId();
		}

	}

?>