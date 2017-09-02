<?php

	namespace Librarys\Database;

    if (defined('LOADED') == false)
        exit;

	use Librarys\Boot;
    use Librarys\Database\Extension\DatabaseExtensionInterface;

	class DatabaseConnect
	{

		private $boot;
		private $resource;
		private $query;

        private $extensionDefault;
        private $extensionRuntime;
        private $extensionObject;

        private $host;
        private $username;
        private $password;
        private $name;
        private $port;
        private $prefix;
        private $encoding;
        private $mysqli;

        const DATABASE_INFORMATION = 'information_schema';
        const DATABASE_MYSQL       = 'mysql';

        const TABLE_SCHEMATA_INFORMATION = 'schemata';

		public function __construct(Boot $boot)
		{
			$this->boot   = $boot;
			$this->query  = array();
		}

        public function executeInitializing()
        {
            if ($this->extensionDefault == null && $this->extensionRuntime == null)
                trigger_error('Extension Database is null');

            $autoload = $this->boot->getAutoload();

            if ($autoload->isFileLibrarys($this->extensionRuntime)) {
                $this->extensionObject = new $this->extensionRuntime($this);

                if ($this->extensionObject instanceof DatabaseExtensionInterface && $this->extensionObject->isSupportExtension())
                    return;
            }

            if ($autoload->isFileLibrarys($this->extensionDefault)) {
                $this->extensionObject = new $this->extensionDefault($this);

                if ($this->extensionObject instanceof DatabaseExtensionInterface && $this->extensionObject->isSupportExtension())
                    return;
            }

            trigger_error('Extension Database not support');
        }

		public function openConnect($useEnv = true)
		{
			if ($this->isResource($this->resource))
				return;

            if ($useEnv) {
    			$this->host     = env('mysql.host');
    			$this->username = env('mysql.username');
    			$this->password = env('mysql.password');
    			$this->name     = env('mysql.nane');
    			$this->port     = env('mysql.port');
                $this->prefix   = env('mysql.prefix');
                $this->encoding = env('mysql.encoding');

    			if (env('mysql.auto_local', false) && $this->boot->isRunLocal()) {
    				$this->host     = env('mysql.local.host');
    				$this->username = env('mysql.local.username');
    				$this->password = env('mysql.local.password');
    				$this->name     = env('mysql.local.name');
    				$this->port     = env('mysql.local.port');
                    $this->prefix   = env('mysql.local.prefix');
                    $this->encoding = env('mysql.local.encoding');
    			}
            }


			$this->resource = $this->extensionObject->connect(
				$this->host,
				$this->username,
				$this->password,
				$this->name,
				$this->port
			);

            if ($this->extensionObject->isResource($this->resource))
                $this->extensionObject->setCharset($this->resource, $this->encoding);

            $this->registerShutdown();

            return $this->isResource($this->resource);
		}

		public function freeConnect()
		{
			if (is_array($this->query)) {
				foreach ($this->query AS $result) {
					if ($this->isResource($result))
						$this->extensionObject->freeResult($result);
				}

				$this->query = array();
			}
		}

		public function disConnected()
		{
			if ($this->isResource($this->resource))
				$this->extensionObject->disconnect($this->resource);
		}

		private function registerShutdown()
		{
			register_shutdown_function(function() {
				$this->freeConnect();
				$this->disConnected();
			});
		}

		public function isResource($resource)
		{
            if ($this->extensionObject != null)
                return $this->extensionObject->isResource($resource);

            return is_resource($resource) || is_object($resource);
		}

		public function isConnect()
		{
		    return $this->extensionObject->isConnect();
		}

        public function getResource()
        {
            return $this->resource;
        }

        public function getExtension()
        {
            return $this->extensionObject;
        }

        public function setHost($host)
        {
            $this->host = $host;
        }

        public function getHost()
        {
            return $this->host;
        }

        public function setUsername($username)
        {
            $this->username = $username;
        }

        public function getUsername()
        {
            return $this->username;
        }

        public function setPassword($password)
        {
            $this->password = $password;
        }

        public function getPassword()
        {
            return $this->password;
        }

        public function setName($name)
        {
            $this->name = $name;
        }

        public function getName()
        {
            return $this->name;
        }

        public function setPort($port)
        {
            $this->port = $port;
        }

        public function getPort()
        {
            return $this->port;
        }

        public function setPrefix($prefix)
        {
            $this->prefix = $prefix;
        }

        public function getPrefix()
        {
            return $this->prefix;
        }

        public function setEncoding($encoding)
        {
            $this->encoding = $encoding;
        }

        public function getEncoding()
        {
            return $this->encoding;
        }

        public function setDatabaseExtensionDefault($namespace)
        {
            $this->extensionDefault = $namespace;
        }

        public function setDatabaseExtensionRuntime($namespace)
        {
            $this->extensionRuntime = $namespace;
        }

		public function query($sql, $isCache = true)
		{
			$query = null;

			if ($this->isResource($sql))
				$query = $sql;
			else
				$query = $this->extensionObject->query($sql);

            if ($isCache)
    			return ($this->query[] = $query);

            return $query;
		}

        public function error()
        {
            if ($this->isResource($this->resource))
                return $this->extensionObject->error();

            return null;
        }

        public function errorConnect()
        {
            return $this->extensionObject->errorConnect();
        }

        public function freeResult($result)
        {
            return $this->extensionObject->freeResult($result);
        }

        public function fetchAssoc($sql)
        {
            return $this->extensionObject->fetchAssoc($sql);
        }

		public function fetchArray($sql)
		{
			return $this->extensionObject->fetchArray($sql);
		}

		public function fetchRow($sql)
		{
            return $this->extensionObject->fetchRow($sql);
		}

		public function numRows($sql)
		{
            return $this->extensionObject->numRows($sql);
		}

		public function numFields($sql)
		{
            return $this->extensionObject->numFields($sql);
		}

		public function dataSeek($sql, $rowNumber)
		{
            return $this->extensionObject->dataSeek($sql, $rowNumber);
		}

		public function insertId()
		{
            return $this->extensionObject->insertId();
		}

        public function fieldName($sql, $fieldOffset)
        {
            return $this->extensionObject->fieldName($sql, $fieldOffset);
        }

        public function fieldType($sql, $fieldOffset)
        {
            return $this->extensionObject->fieldName($sql, $fieldOffset);
        }

	}

?>