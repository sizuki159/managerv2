<?php

    use Librarys\File\FileInfo;
    use Librarys\File\FileMime;
    use Librarys\App\AppDirectory;
    use Librarys\App\AppLocationPath;
    use Librarys\App\AppParameter;
    use Librarys\App\AppFileCopy;

    define('LOADED',               1);
    define('ACTION_COPY',          1);
    define('ACTION_MOVE',          2);
    define('EXISTS_FUNC_OVERRIDE', 1);
    define('EXISTS_FUNC_SKIP',     2);
    define('EXISTS_FUNC_RENAME',   3);

    require_once('incfiles' . DIRECTORY_SEPARATOR . 'global.php');

    if ($appDirectory->isFileExistsDirectorySeparatorName() == false)
        $appAlert->danger(lng('home.alert.path_not_exists'), ALERT_INDEX, env('app.http.host'));
    else if ($appDirectory->isPermissionDenyPath($appDirectory->getDirectory()))
        $appAlert->danger(lng('home.alert.path_not_permission', 'path', $appDirectory->getDirectoryAndName()), ALERT_INDEX, env('app.http.host'));

    $appFileCopy     = new AppFileCopy();
    $appLocationPath = new AppLocationPath($appDirectory, 'index.php');
    $appLocationPath->setIsPrintLastEntry(true);
    $appLocationPath->setIsLinkLastEntry(true);

    $appParameter = new AppParameter();
    $appParameter->add(AppDirectory::PARAMETER_DIRECTORY_URL, $appDirectory->getDirectoryEncode(), true);
    $appParameter->add(AppDirectory::PARAMETER_PAGE_URL,      $appDirectory->getPage(),            $appDirectory->getPage() > 1);
    $appParameter->add(AppDirectory::PARAMETER_NAME_URL,      $appDirectory->getNameEncode(),      true);

    $fileInfo    = new FileInfo($appDirectory->getDirectory() . SP . $appDirectory->getName());
    $fileMime    = new FileMime($fileInfo);
    $isDirectory = $fileInfo->isDirectory();

    if ($isDirectory)
        $title = lng('file_copy.title_page_directory');
    else
        $title = lng('file_copy.title_page_file');

    $themes  = [ env('resource.filename.theme.file') ];
    $appAlert->setID(ALERT_FILE_COPY);
    require_once('incfiles' . SP . 'header.php');

    $forms = [
        'path'        => $appDirectory->getDirectory(),
        'action'      => ACTION_COPY,
        'exists_func' => EXISTS_FUNC_OVERRIDE
    ];

    if (isset($_POST['browser'])) {
        $forms['path']        = $_POST['path'];
        $forms['action']      = intval($_POST['action']);
        $forms['exists_func'] = intval($_POST['exists_func']);

        if ($forms['action'] !== ACTION_COPY && $forms['action'] !== ACTION_MOVE) {
            $appAlert->danger(lng('file_copy.alert.action_not_validate'));
        } else {
            $appFileCopy->setSession($appDirectory->getDirectory(), $appDirectory->getName(), $forms['action'] === ACTION_MOVE, $forms['exists_func']);

            $appParameter->remove(AppDirectory::PARAMETER_NAME_URL);
            $appParameter->toString(true);

            $appAlert->gotoURL('index.php' . $appParameter->toString());
        }
    } else if (isset($_POST['copy'])) {
        $forms['path']        = $_POST['path'];
        $forms['action']      = intval($_POST['action']);
        $forms['exists_func'] = intval($_POST['exists_func']);

        $appFileCopy->clearSession();

        if (empty($forms['path'])) {
            $appAlert->danger(lng('file_copy.alert.not_input_path_copy'));
        } else if ($forms['action'] !== ACTION_COPY && $forms['action'] !== ACTION_MOVE) {
            $appAlert->danger(lng('file_copy.alert.action_not_validate'));
        } else if ($forms['exists_func'] !== EXISTS_FUNC_OVERRIDE &&
                   $forms['exists_func'] !== EXISTS_FUNC_SKIP     &&
                   $forms['exists_func'] !== EXISTS_FUNC_RENAME)
        {
            $appAlert->danger(lng('file_copy.alert.exists_func_not_validate'));
        } else if (FileInfo::filterPaths($forms['path'] . SP . $appDirectory->getName()) == FileInfo::filterPaths($appDirectory->getDirectory() . SP . $appDirectory->getName())) {
            if ($forms['action'] === ACTION_COPY)
                $appAlert->danger(lng('file_copy.alert.path_copy_is_equal_path_current'));
            else
                $appAlert->danger(lng('file_copy.alert.path_move_is_equal_path_current'));
        } else if (FileInfo::isTypeDirectory(FileInfo::filterPaths($forms['path'])) == false) {
            $appAlert->danger(lng('file_copy.alert.path_copy_not_exists'));
        } else if (FileInfo::permissionDenyPath($forms['path'])) {
            $appAlert->danger(lng('file_copy.alert.not_copy_file_to_directory_app'));
        } else {
            $filePathOld            = FileInfo::filterPaths($appDirectory->getDirectory() . SP . $appDirectory->getName());
            $filePathNew            = FileInfo::filterPaths($forms['path'] . SP . $appDirectory->getName());
            $isHasFileAppPermission = false;

            $callbackFileExists = function($directory, $filename, $isDirectory) {
                global $forms;

                if ($forms['exists_func'] === EXISTS_FUNC_SKIP) {
                    return null;
                } else if ($forms['exists_func'] === EXISTS_FUNC_RENAME) {
                    $fileRename = null;
                    $pathRename = null;

                    if (FileInfo::fileExists(FileInfo::filterPaths($directory . SP . $filename))) {
                        while (true) {
                            $fileRename = rand(10000, 99999) . '_' . $filename;
                            $pathRename = FileInfo::filterPaths($directory . SP . $fileRename);

                            if (FileInfo::fileExists($pathRename) == false)
                                break;
                        }

                        return $pathRename;
                    }
                }

                return $directory . SP . $filename;
            };

            if (FileInfo::copy($filePathOld, $filePathNew, true, $forms['action'] === ACTION_MOVE, $isHasFileAppPermission, $callbackFileExists) == false) {
                if ($isDirectory)
                    $appAlert->danger(lng('file_copy.alert.copy_directory_failed', 'filename', $appDirectory->getName()));
                else
                    $appAlert->danger(lng('file_copy.alert.copy_file_failed', 'filename', $appDirectory->getName()));
            } else {
                if ($isHasFileAppPermission)
                    $appAlert->warning(lng('file_copy.alert.has_file_app_not_permission_copy'), ALERT_INDEX);

                $appParameter->remove(AppDirectory::PARAMETER_NAME_URL);
                $appParameter->toString(true);

                if ($isDirectory)
                    $appAlert->success(lng('file_copy.alert.copy_directory_success', 'filename', $appDirectory->getName()), ALERT_INDEX, 'index.php' . $appParameter->toString());
                else
                    $appAlert->success(lng('file_copy.alert.copy_file_success', 'filename', $appDirectory->getName()), ALERT_INDEX, 'index.php' . $appParameter->toString());
            }
        }
    }

    if ($appFileCopy->isSession()) {
        $appFileCopyPathSrc          = FileInfo::filterPaths($appFileCopy->getDirectory() . SP . $appFileCopy->getName());
        $appFileCopyPathDest         = FileInfo::filterPaths($appFileCopy->getPath()      . SP . $appFileCopy->getName());
        $isChooseDirectoryPathFailed = true;

        if ($appFileCopyPathSrc == FileInfo::filterPaths($appDirectory->getDirectory() . SP . $appDirectory->getName())) {
            $idAlert = null;

            if (isset($_SERVER['HTTP_REFERER']) && strpos(strtolower($_SERVER['HTTP_REFERER']), 'index.php') !== false)
                $idAlert = ALERT_INDEX;

            if (FileInfo::isTypeDirectory($appFileCopy->getPath()) == false) {
                $appAlert->danger(lng('file_copy.alert.path_copy_not_exists'), $idAlert);
            } else if ($appFileCopyPathSrc == $appFileCopyPathDest) {
                if ($idAlert !== ALERT_INDEX) {
                    if ($appFileCopy->isMove() == false)
                        $appAlert->danger(lng('file_copy.alert.path_copy_is_equal_path_current'));
                    else
                        $appAlert->danger(lng('file_copy.alert.path_move_is_equal_path_current'));
                }

                $isChooseDirectoryPathFailed = true;
            } else {
                $forms['path']               = $appFileCopy->getPath();
                $forms['exists_func']        = $appFileCopy->getExistsFunc();
                $forms['action']             = ACTION_COPY;
                $isChooseDirectoryPathFailed = false;

                if ($appFileCopy->isMove())
                    $forms['action'] = ACTION_MOVE;

                $appAlert->success(lng('file_copy.alert.directory_path_choose_is_validate', 'path', $appFileCopy->getPath()));
            }

            if ($isChooseDirectoryPathFailed)
                $appFileCopy->clearSession();

            if ($isChooseDirectoryPathFailed && $idAlert === ALERT_INDEX) {
                $appParameter->remove(AppDirectory::PARAMETER_NAME_URL);
                $appParameter->toString(true);

                $appAlert->gotoURL('index.php' . $appParameter->toString());
            }
        }
    }
?>

    <?php $appAlert->display(); ?>
    <?php $appLocationPath->display(); ?>

    <div class="form-action">
        <div class="title">
            <?php if ($isDirectory) { ?>
                <span><?php echo lng('file_copy.title_page_directory'); ?>: <?php echo $appDirectory->getName(); ?></span>
            <?php } else { ?>
                <span><?php echo lng('file_copy.title_page_file'); ?>: <?php echo $appDirectory->getName(); ?></span>
            <?php } ?>
        </div>
        <form action="file_copy.php<?php echo $appParameter->toString(); ?>" method="post">
            <input type="hidden" name="<?php echo $boot->getCFSRToken()->getName(); ?>" value="<?php echo $boot->getCFSRToken()->getToken(); ?>"/>

            <ul class="form-element">
                <li class="input">
                    <span><?php echo lng('file_copy.form.input.path_copy'); ?></span>
                    <input type="text" name="path" value="<?php echo $forms['path']; ?>" placeholder="<?php echo lng('file_copy.form.placeholder.input_path_copy'); ?>"/>
                </li>
                <li class="radio-choose">
                    <ul class="radio-choose-tab">
                        <li>
                            <input type="radio" name="action" value="<?php echo ACTION_COPY; ?>" id="action-copy"<?php if ($forms['action'] === ACTION_COPY) { ?> checked="checked"<?php } ?>/>
                            <label for="action-copy">
                                <span><?php echo lng('file_copy.form.input.radio_action_copy'); ?></span>
                            </label>
                        </li>
                        <li>
                            <input type="radio" name="action" value="<?php echo ACTION_MOVE; ?>" id="action-move"<?php if ($forms['action'] === ACTION_MOVE) { ?> checked="checked"<?php } ?>/>
                            <label for="action-move">
                                <span><?php echo lng('file_copy.form.input.radio_action_move'); ?></span>
                            </label>
                        </li>
                    </ul>
                </li>
                <li class="radio-choose">
                    <span><?php echo lng('file_copy.form.input.if_has_entry_is_exists'); ?></span>
                    <ul class="radio-choose-tab">
                        <li>
                            <input type="radio" name="exists_func" value="<?php echo EXISTS_FUNC_OVERRIDE; ?>" id="exists_func_override"<?php if ($forms['exists_func'] === EXISTS_FUNC_OVERRIDE) { ?> checked="checked"<?php } ?>/>
                            <label for="exists_func_override">
                                <span><?php echo lng('file_copy.form.input.exists_func_override'); ?></span>
                            </label>
                        </li>
                        <li>
                            <input type="radio" name="exists_func" value="<?php echo EXISTS_FUNC_SKIP; ?>" id="exists_func_skip"<?php if ($forms['exists_func'] === EXISTS_FUNC_SKIP) { ?> checked="checked"<?php } ?>/>
                            <label for="exists_func_skip">
                                <span><?php echo lng('file_copy.form.input.exists_func_skip'); ?></span>
                            </label>
                        </li>
                        <li>
                            <input type="radio" name="exists_func" value="<?php echo EXISTS_FUNC_RENAME; ?>" id="exists_func_rename"<?php if ($forms['exists_func'] == EXISTS_FUNC_RENAME) { ?> checked="checked"<?php } ?>/>
                            <label for="exists_func_rename">
                                <span><?php echo lng('file_copy.form.input.exists_func_rename'); ?></span>
                            </label>
                        </li>
                    </ul>
                </li>
                <li class="button">
                    <button type="submit" name="copy" id="button-save-on-javascript">
                        <span><?php echo lng('file_copy.form.button.copy'); ?></span>
                    </button>
                    <button type="submit" name="browser">
                        <span><?php echo lng('file_copy.form.button.browser'); ?></span>
                    </button>
                    <a href="index.php<?php echo $appParameter->toString(); ?>">
                        <span><?php echo lng('file_copy.form.button.cancel'); ?></span>
                    </a>
                </li>
            </ul>
        </form>
    </div>

    <ul class="menu-action">
        <li>
            <a href="file_info.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-about"></span>
                <span><?php echo lng('file_info.menu_action.info'); ?></span>
            </a>
        </li>
        <?php if ($fileMime->isFormatTextEdit()) { ?>
            <?php if ($fileMime->isFormatTextAsEdit()) { ?>
                <li>
                    <a href="file_edit_text.php<?php echo $appParameter->toString(); ?>">
                        <span class="icomoon icon-edit"></span>
                        <span><?php echo lng('file_info.menu_action.edit_as_text'); ?></span>
                    </a>
                </li>
            <?php } else { ?>
                <li>
                    <a href="file_edit_text.php<?php echo $appParameter->toString(); ?>">
                        <span class="icomoon icon-edit"></span>
                        <span><?php echo lng('file_info.menu_action.edit_text'); ?></span>
                    </a>
                </li>
                <li>
                    <a href="file_edit_text_line.php<?php echo $appParameter->toString(); ?>">
                        <span class="icomoon icon-edit"></span>
                        <span><?php echo lng('file_info.menu_action.edit_text_line'); ?></span>
                    </a>
                </li>
            <?php } ?>
        <?php } ?>
        <?php if ($isDirectory == false) { ?>
            <li>
                <a href="file_download.php<?php echo $appParameter->toString(); ?>">
                    <span class="icomoon icon-download"></span>
                    <span><?php echo lng('file_info.menu_action.download'); ?></span>
                </a>
            </li>

            <?php if ($fileMime->isFormatArchiveZip()) { ?>
                <li>
                    <a href="file_unzip.php<?php echo $appParameter->toString(); ?>">
                        <span class="icomoon icon-archive"></span>
                        <span><?php echo lng('file_info.menu_action.unzip'); ?></span>
                    </a>
                </li>
                <li>
                    <a href="file_viewzip.php<?php echo $appParameter->toString(); ?>">
                        <span class="icomoon icon-archive"></span>
                        <span><?php echo lng('file_info.menu_action.viewzip'); ?></span>
                    </a>
                </li>
            <?php } ?>
        <?php } ?>
        <li>
            <a href="file_rename.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-edit"></span>
                <span><?php echo lng('file_info.menu_action.rename'); ?></span>
            </a>
        </li>
        <li>
            <a href="file_delete.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-trash"></span>
                <span><?php echo lng('file_info.menu_action.delete'); ?></span>
            </a>
        </li>
        <li>
            <a href="file_chmod.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-key"></span>
                <span><?php echo lng('file_info.menu_action.chmod'); ?></span>
            </a>
        </li>
    </ul>

<?php require_once('incfiles' . SP . 'footer.php'); ?>
