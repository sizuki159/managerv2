<?php

    namespace Librarys\App\Config;

    if (defined('LOADED') == false)
        exit;

    use Librarys\Boot;

    final class AppUserConfig extends BaseConfig
    {

        const ARRAY_KEY_USERNAME  = 'username';
        const ARRAY_KEY_EMAIL     = 'email';
        const ARRAY_KEY_PASSWORD  = 'password';
        const ARRAY_KEY_POSITION  = 'position';
        const ARRAY_KEY_CREATE_AT = 'create_at';
        const ARRAY_KEY_MODIFY_AT = 'modify_at';
        const ARRAY_KEY_LOGIN_AT  = 'login_at';
        const ARRAY_KEY_BAND_AT   = 'band_at';
        const ARRAY_KEY_BAND_OF   = 'band_of';

        public function __construct(Boot $boot)
        {
            parent::__construct($boot, env('resource.config.user'));
            parent::parse(true);
        }

        public function callbackPreWrite()
        {
            return true;
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