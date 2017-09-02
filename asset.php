<?php

    use Librarys\File\FileInfo;
    use Librarys\App\AppAssets;

    define('LOADED',               1);
    define('DISABLE_CHECK_LOGIN',  1);
    define('ENABLE_CUSTOM_HEADER', 1);

    require_once('incfiles' . DIRECTORY_SEPARATOR . 'global.php');
    requireDefine('asset');

    if ($boot->getCFSRToken()->validateGet() !== true)
        die(lng('default.global.cfsr_token_not_validate'));

    if (
            isset($_GET[ASSET_PARAMETER_THEME_URL]) && empty($_GET[ASSET_PARAMETER_THEME_URL]) == false &&
            isset($_GET[ASSET_PARAMETER_CSS_URL])   && empty($_GET[ASSET_PARAMETER_CSS_URL]) == false
    ) {
        $themeDirectory = addslashes(trim($_GET[ASSET_PARAMETER_THEME_URL]));
        $themeFile      = addslashes(trim($_GET[ASSET_PARAMETER_CSS_URL]));
        $themePath      = env('app.path.theme');

        $themePath = FileInfo::filterPaths($themePath . SP . $themeDirectory);

        if (FileInfo::isTypeDirectory($themePath) == false)
            die(lng('default.resource.directory_not_found'));

        header('Content-Type: text/css');

        $themeFilename = $themeFile . '.css';
        $themeFilepath = FileInfo::filterPaths($themePath . SP . $themeFilename);
        $appAssets     = new AppAssets($themePath, $themeFilename);

        if ($appAssets->loadCss())
            $appAssets->display();
        else
            die(lng('default.resource.file_not_found'));
    } else if (isset($_GET[ASSET_PARAMETER_JS_URL]) && empty($_GET[ASSET_PARAMETER_JS_URL]) == false) {
        $jsFile   = addslashes(trim($_GET[ASSET_PARAMETER_JS_URL]));
        $jsPath   = env('app.path.javascript');

        if (FileInfo::isTypeDirectory($jsPath) == false)
            die(lng('default.resource.directory_not_found'));

        header('Content-Type: text/javascript');

        $jsFilename = $jsFile . '.js';
        $jsFilePath = FileInfo::filterPaths($jsPath . SP . $jsFilename);
        $appAssets  = new AppAssets($jsPath, $jsFilename);

        if ($appAssets->loadJs())
            $appAssets->display();
        else
            die(lng('default.resource.file_not_found'));
    } else {
        die(lng('default.resource.file_not_found'));
    }

?>