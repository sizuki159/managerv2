<?php

    namespace Librarys\App\Mysql;

    use Librarys\Boot;
    use Librarys\App\Config\BaseConfig;

    final class AppMysqlConfig extends BaseConfig
    {

        public function __construct(Boot $boot)
        {
            parent::__construct($boot, env('resource.config.mysql'), env('resource.filename.config.mysql'));
            parent::parse(true);
        }

        public function callbackPreWrite()
        {
            if ($this->getPathConfig() == $this->getPathConfigSystem())
                return false;

            return true;
        }

        public function takeConfigArrayWrite()
        {
            return $this->getConfigArray();
        }

        public function takePathConfigWrite()
        {
            return $this->pathConfig;
        }

    }

?>