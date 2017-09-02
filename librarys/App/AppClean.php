<?php

    namespace Librarys\App;

    if (defined('LOADED') == false)
        exit;

    use Librarys\File\FileInfo;
    use Librarys\App\AppUser;
    use Librarys\App\AppAssets;
    use Librarys\App\Config\AppConfig;

    final class AppClean
    {

        public static function scanAutoClean($isCleanAllNotCheck = false)
        {
            global $appConfig;

            if ($appConfig == null)
                return false;

            self::scanAutoCleanTmp  ($appConfig, $isCleanAllNotCheck);
            self::scanAutoCleanToken($appConfig, $isCleanAllNotCheck);
            self::scanAutoCleanCache($appConfig, $isCleanAllNotCheck);
            self::scanAutoCleanUser ($appConfig, $isCleanAllNotCheck);

            return true;
        }

        private static function scanAutoCleanTmp($appConfig, $isCleanAllNotCheck)
        {
            $directory = env('app.path.tmp');

            if (FileInfo::isTypeDirectory($directory)) {
                $time     = time();
                $lifetime = $appConfig->get('tmp.lifetime', 180);
                $limit    = $appConfig->get('tmp.limit',    10);
                $handle   = FileInfo::scanDirectory($directory);

                if ($handle === false)
                    return false;

                $lists = array();

                foreach ($handle AS $filename) {
                    $filepath = FileInfo::filterPaths($directory . SP . $filename);

                    if (FileInfo::isTypeFile($filepath)) {
                        $filetime         = FileInfo::fileMTime($filepath);
                        $lists[$filename] = $filetime;
                    }
                }

                $count       = count($lists);
                $countDelete = $count;

                if ($count <= 0)
                    return false;

                asort($lists);

                foreach ($lists AS $filename => $filetime) {
                    $filepath = FileInfo::filterPaths($directory . SP . $filename);

                    if ($isCleanAllNotCheck || $time - $filetime >= $lifetime || $countDelete >= $limit) {
                        $countDelete--;

                        if (FileInfo::isTypeFile($filepath))
                            FileInfo::unlink($filepath);
                    }
                }
            }
        }

        private static function scanAutoCleanToken($appConfig, $isCleanAllNotCheck)
        {
            global $appUser;

            $directory  = env('app.path.token');
            $tokenIgone = null;

            if ($appUser->isLogin())
                $tokenIgone = $appUser->getTokenValue();

            if (FileInfo::isTypeDirectory($directory)) {
                $time   = time();
                $handle = FileInfo::scanDirectory($directory);

                if ($handle === false)
                    return false;

                $tokenMaxLive = intval($appConfig->get('login.time_login'));

                foreach ($handle AS $filename) {
                    $filepath = FileInfo::filterPaths($directory . SP . $filename);

                    if (FileInfo::isTypeFile($filepath)) {
                        $tokenBuffer = FileInfo::fileReadContents($filepath);
                        $tokenArray = @unserialize($tokenBuffer);

                        if ($tokenBuffer === false || $tokenArray === false) {
                            FileInfo::unlink($filepath);
                        } else {
                            $tokenUserLive = intval($tokenArray[AppUser::TOKEN_ARRAY_KEY_USER_LIVE]);

                            if (($isCleanAllNotCheck || $time - $tokenUserLive >= $tokenMaxLive) && strcmp($tokenIgone, $filename) !== 0)
                                FileInfo::unlink($filepath);
                        }
                    }
                }
            }
        }

        private static function scanAutoCleanCache($appConfig, $isCleanAllNotCheck)
        {
            $directory = env('app.path.cache');

            if (FileInfo::isTypeDirectory($directory)) {
                $time   = time();
                $handle = FileInfo::scanDirectory($directory);

                if ($handle === false)
                    return false;

                $cacheLifetimeCss = $appConfig->get('cache.css.lifetime');
                $cacheLifetimeJs  = $appConfig->get('cache.js.lifetime');

                foreach ($handle AS $filename) {
                    $filepath = FileInfo::filterPaths($directory . SP . $filename);

                    if (FileInfo::isTypeFile($filepath)) {
                        $filemime      = FileInfo::extFile($filename);
                        $filetime      = FileInfo::fileMTime($filepath);
                        $cacheLifetime = 0;

                        if (strcasecmp($filemime, AppAssets::CACHE_CSS_MIME) === 0)
                            $cacheLifetime = $cacheLifetimeCss;
                        else if (strcasecmp($filemime, AppAssets::CACHE_JS_MIME) === 0)
                            $cacheLifetime = $cacheLifetimeJs;

                        if ($isCleanAllNotCheck || $time - $filetime >= $cacheLifetime)
                            FileInfo::unlink($filepath);
                    }
                }
            }
        }

        private static function scanAutoCleanUser($appConfig, $isCleanAllNotCheck)
        {
            global $appUser;

            if ($appUser->getConfig()->hasEntryConfigArraySystem()) {
                $arrays    = $appUser->getConfig()->getConfigArraySystem();
                $directory = env('app.path.user');
                $handle    = FileInfo::scanDirectory($directory);

                if ($handle === false)
                    return false;

                foreach ($handle AS $idDirectory) {
                    if ($idDirectory != '.' && $idDirectory != '..') {
                        $userPath = FileInfo::filterPaths($directory . SP . $idDirectory);

                        if (array_key_exists($idDirectory, $arrays) === false) {

                            if (FileInfo::isTypeDirectory($userPath))
                                FileInfo::rrmdirSystem($userPath);
                            else
                                FileInfo::unlink($userPath);
                        }
                    }
                }
            }

            return true;
        }

    }

?>