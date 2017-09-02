<?php

    namespace Librarys\App\Config;

    if (defined('LOADED') == false)
        exit;

    use Librarys\Boot;

    final class AppAssetsConfig extends BaseConfig
    {

        public function __construct(Boot $boot, $pathThemeEnv)
        {
            parent::__construct($boot, $pathThemeEnv);
            parent::parse(true);
        }

        public function callbackPreWrite()
        {
            return false;
        }

        public function takeConfigArrayWrite()
        {
            return $this->getConfigArraySystem();
        }

        public function takePathConfigWrite()
        {
            return $this->pathConfigSystem;
        }

    }

?>