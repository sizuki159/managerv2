<?php

    namespace Librarys\App\Mysql;

    use Librarys\App\Base\BaseConfigWrite;

    final class AppMysqlConfigWrite extends BaseConfigWrite
    {

        public function __construct(AppMysqlConfig $appMysqlConfig)
        {
            parent::__construct($appMysqlConfig, $appMysqlConfig->getPathConfig());
        }

        public function callbackPreWrite()
        {
            if ($this->baseConfigRead->getPathConfig() == $this->baseConfigRead->getPathConfigSystem())
                return false;

            return true;
        }

        public function resultConfigArray()
        {
            return $this->baseConfigRead->getConfigArray();
        }

    }

?>