<?php

    namespace Librarys\App\Config;

    if (defined('LOADED') == false)
        exit;

    use Librarys\Boot;

    final class AppAboutConfig extends BaseConfig
    {

        const ARRAY_KEY_NAME       = 'name';
        const ARRAY_KEY_AUTHOR     = 'author';
        const ARRAY_KEY_VERSION    = 'version';
        const ARRAY_KEY_IS_BETA    = 'is_beta';
        const ARRAY_KEY_EMAIL      = 'email';
        const ARRAY_KEY_PHONE      = 'phone';
        const ARRAY_KEY_FB_LINK    = 'fb_link';
        const ARRAY_KEY_FB_TITLE   = 'fb_title';
        const ARRAY_KEY_GIT_LINK   = 'git_link';
        const ARRAY_KEY_GIT_TITLE  = 'git_title';
        const ARRAY_KEY_CREATE_AT  = 'create_at';
        const ARRAY_KEY_UPGRADE_AT = 'upgrade_at';
        const ARRAY_KEY_CHECK_AT   = 'check_at';
        const ARRAY_KEY_BUILD_AT   = 'build_at';

        public function __construct(Boot $boot)
        {
            parent::__construct($boot, env('resource.config.about'), env('resource.filename.config.about'));
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