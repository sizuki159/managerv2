<?php

    namespace Librarys;

    if (defined('SP') == false)
        define('SP', DIRECTORY_SEPARATOR);

    if (defined('LOADED') == false)
        exit;

    error_reporting(E_ALL);

    require_once(__DIR__ . SP . 'Function.php');
    require_once(__DIR__ . SP . 'Environment.php');
    require_once(__DIR__ . SP . 'Autoload.php');
    require_once(__DIR__ . SP . 'CFSR' . SP . 'CFSRToken.php');

    use Librarys\Autoload;
    use Librarys\CFSR\CFSRToken;

    final class Boot
    {

        private $environment;
        private $language;
        private $autoload;
        private $cfsr;
        private $isCustomHeader;

        public function __construct(array $config, $isCustomHeader = false)
        {
            $this->setCustomHeader($isCustomHeader);
            $this->obBufferStart();
            $this->obBufferEnd();
            $this->fixMagicQuotesGpc();

            $this->environment = new Environment($config);
            $this->language    = new Language($this);
            $this->autoload    = new Autoload($this);

            $this->environment->execute();
            $this->dateInitializing();

            $this->language->execute();
            $this->autoload->execute();

            if (env('app.session.init', false) == true)
                $this->sessionInitializing();

            $this->cfsr = new CFSRToken();
        }

        public function fixMagicQuotesGpc()
        {
            $_SERVER = filter_var_array($_SERVER, FILTER_SANITIZE_STRING);
            $_GET    = filter_var_array($_GET,    FILTER_SANITIZE_STRING);

            if (get_magic_quotes_gpc()) {
                stripcslashesResursive($_GET);
                stripcslashesResursive($_POST);
                stripcslashesResursive($_REQUEST);
                stripcslashesResursive($_COOKIE);
            }
        }

        public function sessionInitializing()
        {
            $sessionStart = false;

            if (version_compare(phpversion(), '5.4.0', '>='))
                $sessionStart = session_status() === PHP_SESSION_ACTIVE;
            else
                $sessionStart = session_id() !== '';

            if ($sessionStart == false) {
                session_name         (env('app.session.name',          session_name()));
                session_cache_limiter(env('app.session.cache_limiter', session_cache_limiter()));
                session_cache_expire (env('app.session.cache_expire',  session_cache_expire()));

                session_set_cookie_params(
                    env('app.session.cookie_lifetime', ini_get('session.cookie_lifetime')),
                    env('app.session.cookie_path',     ini_get('session.cookie_path'))
                );

                session_start();
            }

            return true;
        }

        public function dateInitializing()
        {
            @date_default_timezone_set(env('app.date.timezone', 'Asia/Ho_Chi_Minh'));
        }

        public function obBufferStart()
        {
            if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))
                @ob_start('ob_gzhandler');
            else
                @ob_start();

            if ($this->isCustomHeader == false) {
                header('Cache-Control: private, max-age=0, no-cache, no-store, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s ', time()) . 'GMT');
                header('Etag: "' . md5(time()) . '"');
            }
        }

        public function obBufferClean()
        {
            @ob_clean();
            @ob_end_clean();
        }

        public function obBufferEnd()
        {
            if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip'))
                return;

            register_shutdown_function(function() {
                @ob_flush();
                @ob_end_flush();
            });
        }

        public function getEnvironment()
        {
            return $this->environment;
        }

        public function getLanguage()
        {
            return $this->language;
        }

        public function getAutoload()
        {
            return $this->autoload;
        }

        public function getCFSRToken()
        {
            return $this->cfsr;
        }

        public function setCustomHeader($isCustomHeader)
        {
            $this->isCustomHeader = $isCustomHeader;
        }

        public function isCustomHeader()
        {
            return $this->isCustomHeader;
        }

        public function getMemoryUsageBegin()
        {
            return $this->memoryUsageBegin;
        }

        public function getMemoryUsageEnd()
        {
            return $this->memoryUsageEnd;
        }

        public static function isRunLocal()
        {
            $host = env('SERVER.HTTP_HOST');

            if (preg_match('/(localhost|127\.0\.0\.1|izerocs\.mobi)(:8080)?/i', $host))
                return true;
        }

    }

?>
