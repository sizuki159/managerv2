<?php

    use Librarys\File\FileInfo;
    use Librarys\File\FileMime;
    use Librarys\App\AppDirectory;
    use Librarys\App\AppLocationPath;
    use Librarys\App\AppParameter;
    use Librarys\Zip\PclZip;

    define('LOADED', 1);
    require_once('incfiles' . DIRECTORY_SEPARATOR . 'global.php');

    if ($appDirectory->isDirectoryExists() == false)
        $appAlert->danger(lng('home.alert.path_not_exists'), ALERT_INDEX, env('app.http.host'));
    else if ($appDirectory->isPermissionDenyPath())
        $appAlert->danger(lng('home.alert.path_not_permission', 'path', $appDirectory->getDirectory()), ALERT_INDEX, env('app.http.host'));

    requireDefine('file_action');

    $appLocationPath = new AppLocationPath($appDirectory, 'index.php');
    $appLocationPath->setIsPrintLastEntry(true);
    $appLocationPath->setIsLinkLastEntry(true);

    $appParameter = new AppParameter();
    $appParameter->add(AppDirectory::PARAMETER_DIRECTORY_URL, $appDirectory->getDirectoryEncode(), true);
    $appParameter->add(AppDirectory::PARAMETER_PAGE_URL,      $appDirectory->getPage(),            $appDirectory->getPage() > 1);

    $listEntrys  = array();
    $countEntrys = 0;
    $isOddEntrys = false;

    if (isset($_POST['entrys']) && is_array($_POST['entrys'])) {
        $listEntrys = $_POST['entrys'];
        $countEntrys = count($listEntrys);
        $isOddEntrys = ($countEntrys % 2) !== 0;
    }

    if ($countEntrys <= 0)
        $appAlert->danger(lng('file_action.alert.not_file_select'), ALERT_INDEX, 'index.php');

    $listEntrys = AppDirectory::rawDecodes($listEntrys);
    $nameAction = null;

    if (isset($_POST['action']) && empty($_POST['action']) == false)
        $nameAction = addslashes(trim($_POST['action']));

    if ($nameAction == FILE_ACTION_RENAME_MULTI)
        $title = 'rename';
    else if ($nameAction == FILE_ACTION_COPY_MULTI)
        $title = 'copy';
    else if ($nameAction == FILE_ACTION_DELETE_MULTI)
        $title = 'delete';
    else if ($nameAction == FILE_ACTION_ZIP_MULTI)
        $title = 'zip';
    else if ($nameAction == FILE_ACTION_CHMOD_MULTI)
        $title = 'chmod';
    else
        $appAlert->danger(lng('file_action.alert.action_not_validate'), ALERT_INDEX, 'index.php');

    $themes  = [ env('resource.filename.theme.file') ];
    $scripts = [ env('resource.filename.javascript.checkbox_checkall') ];
    $appAlert->setID(ALERT_FILE_ACTION);

    $forms = [
        'rename' => [
            'modifier_entrys' => array(),
            'modifier_count'  => 0
        ],

        'copy' => [
            'path_copy'   => $appDirectory->getDirectory(),
            'mode'        => FILE_ACTION_COPY_MULTI_MODE_COPY,
            'exists_func' => FILE_ACTION_COPY_MULTI_EXISTS_FUNC_OVERRIDE
        ],

        'zip' => [
            'path_create_zip' => $appDirectory->getDirectory(),
            'name_zip'        => 'archive.zip',
            'delete_source'   => false,
            'override_zip'    => true
        ],

        'chmod' => [
            'directory' => '755',
            'file'      => '644'
        ]
    ];

    if (isset($_POST['rename_button'])) {
        if (isset($_POST['rename_entrys']) && is_array($_POST['rename_entrys'])) {
            $forms['rename']['modifier_entrys'] = $_POST['rename_entrys'];
            $forms['rename']['modifier_count']  = count($forms['rename']['modifier_entrys']);

            if ($forms['rename']['modifier_entrys'] > 0) {
                $isFailed                           = false;
                $isHasModifier                      = false;
                $forms['rename']['modifier_entrys'] = addslashesArray($forms['rename']['modifier_entrys']);

                foreach ($listEntrys AS $entryFilename) {
                    $entryFilenameRawEncode = AppDirectory::rawEncode($entryFilename);

                    if (isset($forms['rename']['modifier_entrys'][$entryFilenameRawEncode])) {
                        $entryFilenameModifier = $forms['rename']['modifier_entrys'][$entryFilenameRawEncode];
                        $entryPathOdd          = FileInfo::filterPaths($appDirectory->getDirectory() . SP . $entryFilename);
                        $entryIsTypeDirectory  = FileInfo::isTypeDirectory($entryPathOdd);

                        if (empty($entryFilenameModifier)) {
                            $isFailed = true;

                            if ($entryIsTypeDirectory)
                                $appAlert->danger(lng('file_action.alert.rename.name_directory_not_set_null', 'name', $entryFilename));
                            else
                                $appAlert->danger(lng('file_action.alert.rename.name_file_not_set_null', 'name', $entryFilename));

                            break;
                        }

                        $entryPathNew = FileInfo::filterPaths($appDirectory->getDirectory() . SP . $entryFilenameModifier);

                        if ($entryFilename != $entryFilenameModifier)
                            $isHasModifier = true;

                        if (FileInfo::isNameValidate($entryFilename) == false) {
                            $isFailed = true;

                            if ($entryIsTypeDirectory)
                                $appAlert->danger(lng('file_action.alert.rename.name_directory_is_error', 'name', $entryFilenameModifier, 'validate', FileInfo::FILENAME_VALIDATE));
                            else
                                $appAlert->danger(lng('file_action.alert.rename.name_file_is_error', 'name', $entryFilenameModifier, 'validate', FileInfo::FILENAME_VALIDATE));

                            break;
                        }

                        if (countStringArray($forms['rename']['modifier_entrys'], strtolower($entryFilenameModifier), true) > 1 && $entryFilenameModifier != $entryFilename) {
                            $isFailed = true;

                            if ($entryIsTypeDirectory)
                                $appAlert->danger(lng('file_action.alert.rename.name_directory_is_exists_in_input_other', 'name', $entryFilenameModifier));
                            else
                                $appAlert->danger(lng('file_action.alert.rename.name_file_is_exists_in_input_other', 'name', $entryFilenameModifier));

                            break;
                        }

                        if (isInArray($listEntrys, strtolower($entryFilenameModifier), true) == false && FileInfo::fileExists($entryPathNew) == true) {
                            $isFailed = true;

                            if ($entryIsTypeDirectory)
                                $appAlert->danger(lng('file_action.alert.rename.name_directory_is_exists', 'name', $entryFilenameModifier));
                            else
                                $appAlert->danger(lng('file_action.alert.rename.name_file_is_exists', 'name', $entryFilenameModifier));

                            break;
                        }
                    }
                }

                if ($isHasModifier == false) {
                    $appAlert->danger(lng('file_action.alert.rename.nothing_changes'));
                } else if ($isFailed == false) {
                    $isFailed = true;
                    $symbol   = '_';
                    $rand     = md5(rand(10000, 99999) . $symbol . $appDirectory->getDirectory());
                    $rand     = substr($rand, 0, strlen($rand) >> 1);

                    foreach ($listEntrys AS $entryFilename) {
                        $entryPath = FileInfo::filterPaths($appDirectory->getDirectory() . SP . $entryFilename);
                        FileInfo::rename($entryPath, $entryPath . $symbol . $rand);
                    }

                    $isFailed = false;

                    foreach ($listEntrys AS $entryFilename) {
                        $entryFilenameRawEncode = AppDirectory::rawEncode($entryFilename);
                        $entryPath              = FileInfo::filterPaths($appDirectory->getDirectory() . SP . $entryFilename . $symbol . $rand);
                        $entryPathRename        = FileInfo::filterPaths($appDirectory->getDirectory() . SP . $forms['rename']['modifier_entrys'][$entryFilenameRawEncode]);
                        $entryIsTypeDirectory   = FileInfo::isTypeDirectory($entryPath);
                        $entryAlertMessage      = null;

                        if (FileInfo::rename($entryPath, $entryPathRename) == false) {
                            $isFailed = true;

                            if ($entryIsTypeDirectory)
                                $entryAlertMessage = 'rename_directory_failed';
                            else
                                $entryAlertMessage = 'rename_file_failed';

                            $appAlert->danger(lng('file_action.alert.rename.' . $entryAlertMessage, 'name', $entryFilename));
                        }
                    }

                    if ($isFailed == false)
                        $appAlert->success(lng('file_action.alert.rename.rename_success'), ALERT_INDEX, 'index.php' . $appParameter->toString());
                    else
                        $forms['rename']['modifier_entrys'] = stripslashesArray($forms['rename']['modifier_entrys']);
                } else {
                    $appAlert->danger(lng('file_action.alert.rename.list_entrys_modifier_is_zero'));
                }
            } else {
                $appAlert->danger(lng('file_action.alert.rename.list_entrys_modifier_is_zero'));
            }
        } else {
            $appAlert->danger(lng('file_action.alert.rename.list_entrys_modifier_is_zero'));
        }
    } else if (isset($_POST['copy_button'])) {
        $forms['copy']['path_copy']   = addslashes($_POST['path_copy']);
        $forms['copy']['mode']        = intval(addslashes($_POST['mode']));
        $forms['copy']['exists_func'] = intval(addslashes($_POST['exists_func']));

        if (empty($forms['copy']['path_copy'])) {
            $appAlert->danger(lng('file_action.alert.copy.not_input_path_copy'));
        } else if ($forms['copy']['mode'] !== FILE_ACTION_COPY_MULTI_MODE_COPY && $forms['copy']['mode'] !== FILE_ACTION_COPY_MULTI_MODE_MOVE) {
            $appAlert->danger(lng('file_action.alert.copy.mode_not_validate'));
        } else if ($forms['copy']['exists_func'] !== FILE_ACTION_COPY_MULTI_EXISTS_FUNC_OVERRIDE &&
                   $forms['copy']['exists_func'] !== FILE_ACTION_COPY_MULTI_EXISTS_FUNC_SKIP     &&
                   $forms['copy']['exists_func'] !== FILE_ACTION_COPY_MULTI_EXISTS_FUNC_RENAME)
        {
            $appAlert->danger(lng('file_action.alert.copy.exists_func_not_validate'));
        } else {
            $forms['copy']['path_copy'] = FileInfo::filterPaths($forms['copy']['path_copy']);

            if ($forms['copy']['path_copy'] == $appDirectory->getDirectory()) {
                if ($forms['copy']['mode'] === FILE_ACTION_COPY_MULTI_MODE_COPY)
                    $appAlert->danger(lng('file_action.alert.copy.path_copy_is_equal_path_current'));
                else
                    $appAlert->danger(lng('file_action.alert.copy.path_move_is_equal_path_current'));
            } else if (FileInfo::permissionDenyPath($forms['copy']['path_copy'])) {
                $appAlert->danger(lng('file_action.alert.copy.not_copy_file_to_directory_app'));
            } else {
                $isHasFileAppPermission = false;

                $callbackFileExists = function($directory, $filename, $isDirectory) {
                    global $forms;

                    if ($forms['copy']['exists_func'] === FILE_ACTION_COPY_MULTI_EXISTS_FUNC_SKIP) {
                        return null;
                    } else if ($forms['copy']['exists_func'] === FILE_ACTION_COPY_MULTI_EXISTS_FUNC_RENAME) {
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

                $isFailed     = false;
                $countSuccess = $countEntrys;

                foreach ($listEntrys AS $entryFilename) {
                    $entryPath            = FileInfo::filterPaths($appDirectory->getDirectory() . SP . $entryFilename);
                    $entryPathCopy        = FileInfo::filterPaths($forms['copy']['path_copy']   . SP . $entryFilename);
                    $entryIsTypeDirectory = FileInfo::isTypeDirectory($entryPath);

                    if (FileInfo::copy($entryPath, $entryPathCopy, true, $forms['copy']['mode'] === FILE_ACTION_COPY_MULTI_MODE_MOVE, $isHasFileAppPermission, $callbackFileExists) == false) {
                        if ($entryIsTypeDirectory)
                            $appAlert->danger(lng('file_action.alert.copy.copy_directory_failed', 'name', $entryFilename));
                        else
                            $appAlert->danger(lng('file_action.alert.copy.copy_file_failed', 'name', $entryFilename));

                        $isFailed = true;
                        $countSuccess--;
                    }
                }

                if ($isFailed == false) {
                    if ($isHasFileAppPermission)
                        $appAlert->warning(lng('file_action.alert.copy.has_file_app_not_permission_copy'), ALERT_INDEX);

                    $appAlert->success(lng('file_action.alert.copy.copy_success'), ALERT_INDEX, 'index.php' . $appParameter->toString());
                } else {
                    if ($isHasFileAppPermission)
                        $appAlert->warning(lng('file_action.alert.copy.has_file_app_not_permission_copy'));

                    if ($countEntrys > 1 && $countSuccess > 0)
                        $appAlert->success(lng('file_action.alert.copy.copy_some_items_success'));
                }
            }
        }

        $forms['copy']['path_copy'] = addslashes($forms['copy']['path_copy']);
    } else if (isset($_POST['delete_button'])) {
        $isFailed               = false;
        $countSuccess           = $countEntrys;
        $isHasFileAppPermission = false;

        foreach ($listEntrys AS $entryFilename) {
            $entryPath            = FileInfo::filterPaths($appDirectory->getDirectory() . SP . $entryFilename);
            $entryIsTypeDirectory = FileInfo::isTypeDirectory($entryPath);

            if (FileInfo::permissionDenyPath($entryPath)) {
                $isHasFileAppPermission = true;
            } else if ($entryIsTypeDirectory) {
                if (FileInfo::rrmdir($entryPath, null, $isHasFileAppPermission) == false) {
                    $isFailed = true;
                    $countSuccess--;
                    $appAlert->danger(lng('file_action.alert.delete.delete_directory_failed', 'name', $entryFilename));
                }
            } else if (FileInfo::unlink($entryPath) == false) {
                $isFailed = true;
                $countSuccess--;
                $appAlert->danger(lng('file_action.alert.delete.delete_file_failed', 'name', $entryFilename));
            }
        }

        if ($isFailed == false) {
            if ($isHasFileAppPermission)
                $appAlert->warning(lng('file_action.alert.delete.not_delete_file_app'), ALERT_INDEX);

            $appAlert->success(lng('file_action.alert.delete.delete_success'), ALERT_INDEX, 'index.php' . $appParameter->toString());
        } else {
            if ($isHasFileAppPermission)
                $appAlert->warning(lng('file_action.alert.delete.not_delete_file_app'));

            if ($countEntrys > 1 && $countSuccess > 0)
                $appAlert->success(lng('file_action.alert.delete.delete_success'));
        }
    } else if (isset($_POST['zip_button'])) {
        $forms['zip']['path_create_zip'] = addslashes($_POST['path_create_zip']);
        $forms['zip']['name_zip']        = addslashes($_POST['name_zip']);

        if (isset($_POST['delete_source']))
            $forms['zip']['delete_source'] = boolval(addslashes($_POST['delete_source']));

        if (isset($_POST['override_zip']))
            $forms['zip']['override_zip'] = boolval(addslashes($_POST['override_zip']));
        else
            $forms['zip']['override_zip'] = false;

        if (empty($forms['zip']['path_create_zip'])) {
            $appAlert->danger(lng('file_action.alert.zip.not_input_path_create_zip'));
        } else if (empty($forms['zip']['name_zip'])) {
            $appAlert->danger(lng('file_action.alert.zip.not_input_name_zip'));
        } else if (FileInfo::isNameValidate($forms['zip']['name_zip']) == false) {
            $appAlert->danger(lng('file_action.alert.zip.name_zip_not_validate', 'validate', FileInfo::FILENAME_VALIDATE));
        } else {
            $isFailed                        = false;
            $forms['zip']['path_create_zip'] = FileInfo::filterPaths($forms['zip']['path_create_zip']);
            $pathFileZip                     = FileInfo::filterPaths($forms['zip']['path_create_zip'] . SP . $forms['zip']['name_zip']);

            if (FileInfo::permissionDenyPath($forms['zip']['path_create_zip'])) {
                $isFailed = true;
                $appAlert->danger(lng('file_action.alert.zip.not_create_zip_to_path_app'));
            } else if ($forms['zip']['delete_source'] == true) {
                foreach ($listEntrys AS $entryFilename) {
                    $entryPath = FileInfo::filterPaths($appDirectory->getDirectory() . SP . $entryFilename);

                    if (FileInfo::isTypeDirectory($entryPath) && $entryPath == $forms['zip']['path_create_zip']) {
                        $isFailed = true;
                        $appAlert->danger(lng('file_action.alert.zip.path_create_zip_is_delete_source', 'path', $entryPath));

                        break;
                    }
                }
            } else if ($forms['zip']['override_zip'] == false && FileInfo::isTypeFile($pathFileZip)) {
                $isFailed = true;
                $appAlert->danger(lng('file_action.alert.zip.path_file_zip_is_exists', 'path', $pathFileZip));
            } else if (FileInfo::isTypeDirectory($pathFileZip)) {
                $isFailed = true;
                $appAlert->danger(lng('file_action.alert.zip.path_file_zip_is_exists_type_directory', 'path', $pathFileZip));
            } else if ($forms['zip']['override_zip'] && FileInfo::isTypeFile($pathFileZip) && FileInfo::unlink($pathFileZip) == false) {
                $isFailed = true;
                $appAlert->danger(lng('file_action.alert.zip.delete_file_zip_old_failed', 'path', $pathFileZip));
            }

            if ($isFailed == false) {
                $isHasFileAppPermission = false;
                $pclZip                 = new PclZip($pathFileZip);

                function callbackPreAdd($event, $header)
                {
                    if (FileInfo::permissionDenyPath(FileInfo::filterPaths($header['filename']))) {
                        $isHasFileAppPermission = true;
                        return 0;
                    }

                    return 1;
                }

                $countSuccess = $countEntrys;

                foreach ($listEntrys AS $entryFilename) {
                    $entryPath            = FileInfo::filterPaths($appDirectory->getDirectory() . SP . $entryFilename);
                    $entryIsTypeDirectory = FileInfo::isTypeDirectory($entryPath);

                    if ($pclZip->add($entryPath, PCLZIP_OPT_REMOVE_PATH, $appDirectory->getDirectory(), PCLZIP_CB_PRE_ADD, 'callbackPreAdd') == false) {
                        $isFailed = true;
                        $countSuccess--;

                        if ($isTypeDirectory)
                            $appAlert->danger(lng('file_action.alert.zip.zip_directory_failed', 'error', $pclZip->errorInfo(true)));
                    }
                }

                if ($forms['zip']['delete_source'])
                    FileInfo::rrmdir($listEntrys, $appDirectory->getDirectory(), $isHasFileAppPermission);

                if ($isFailed == false) {
                    if ($isHasFileAppPermission)
                        $appAlert->warning(lng('file_action.alert.zip.has_file_app_not_permission_zip'), ALERT_INDEX);

                    $appAlert->success(lng('file_action.alert.zip.zip_success'), ALERT_INDEX, 'index.php' . $appParameter->toString());
                } else {
                    if ($isHasFileAppPermission)
                        $appAlert->warning(lng('file_action.alert.zip.has_file_app_not_permission_zip'));

                    if ($countEntrys > 1 && $countSuccess > 0)
                        $appAlert->success(lng('file_action.alert.zip.zip_success'));
                }
            }
        }

        $forms['zip']['path_create_zip'] = stripslashes($forms['zip']['path_create_zip']);
        $forms['zip']['name_zip']        = stripslashes($forms['zip']['name_zip']);
    } else if (isset($_POST['chmod_button'])) {
        $forms['chmod']['directory'] = addslashes($_POST['chmod_directory']);
        $forms['chmod']['file']      = addslashes($_POST['chmod_file']);

        if (empty($forms['chmod']['directory'])) {
            $appAlert->danger(lng('file_action.alert.chmod.not_input_chmod_directory'));
        } else if (empty($forms['chmod']['file'])) {
            $appAlert->danger(lng('file_action.alert.chmod.not_input_chmod_file'));
        } else {
            $isFailed       = false;
            $countSuccess   = $countEntrys;
            $chmodDirectory = intval($forms['chmod']['directory'], 8);
            $chmodFile      = intval($forms['chmod']['file'],      8);

            foreach ($listEntrys AS $entryFilename) {
                $entryPath            = FileInfo::filterPaths($appDirectory->getDirectory() . SP . $entryFilename);
                $entryIsTypeDirectory = FileInfo::isTypeDirectory($entryPath);

                if ($entryIsTypeDirectory && FileInfo::chmod($entryPath, $chmodDirectory) == false) {
                    $isFailed = true;
                    $countSuccess--;
                    $appAlert->danger(lng('file_action.alert.chmod.chmod_directory_failed', 'name', $entryFilename));
                } else if ($entryIsTypeDirectory == false && FileInfo::chmod($entryPath, $chmodFile) == false) {
                    $isFailed = true;
                    $countSuccess--;
                    $appAlert->danger(lng('file_action.alert.chmod.chmod_file_failed', 'name', $entryFilename));
                }
            }

            if ($isFailed == false)
                $appAlert->success(lng('file_action.alert.chmod.chmod_success'), ALERT_INDEX, 'index.php' . $appParameter->toString());
            else if ($countEntrys > 1 && $countSuccess > 0)
                $appAlert->success(lng('file_action.alert.chmod.chmod_success'));
        }
    }

    $title   = lng('file_action.title.' . $title);
    require_once('incfiles' . DIRECTORY_SEPARATOR . 'header.php');
?>

    <?php $appAlert->display(); ?>
    <?php $appLocationPath->display(); ?>

    <form action="file_action.php<?php echo $appParameter->toString(); ?>" method="post" id="form-list-file">
        <input type="hidden" name="<?php echo $boot->getCFSRToken()->getName(); ?>" value="<?php echo $boot->getCFSRToken()->getToken(); ?>"/>
        <input type="hidden" name="action" value="<?php echo $nameAction; ?>"/>

        <div class="form-action">
            <div class="title">
                <span><?php echo $title; ?></span>
            </div>

            <ul class="file-list no-box-shadow">
                <?php $indexLoop      = 0; ?>
                <?php $countLoopEntry = 0; ?>

                <?php foreach ($listEntrys AS $entryFilename) { ?>
                    <?php $entryPath = FileInfo::filterPaths($appDirectory->getDirectory() . SP . $entryFilename); ?>

                    <?php if (FileInfo::permissionDenyPath($entryPath)) { ?>

                    <?php } else if (FileInfo::isTypeDirectory($entryPath)) { ?>
                        <li class="type-directory <?php if ($isOddEntrys && $indexLoop + 1 === $countEntrys) { ?> entry-odd<?php } ?>">
                            <?php $urlEntryDirectory = AppDirectory::PARAMETER_DIRECTORY_URL . '=' . AppDirectory::rawEncode($entryPath); ?>

                            <div class="icon">
                                <?php $id = 'folder-' . AppDirectory::rawEncode($entryFilename); ?>

                                <input
                                        type="checkbox"
                                        name="entrys[]"
                                        id="<?php echo $id; ?>"
                                        value="<?php echo AppDirectory::rawEncode($entryFilename); ?>"
                                        checked="checked"
                                        <?php if ($appConfig->get('enable_disable.count_checkbox_file_javascript')) { ?> onclick="javascript:CheckboxCheckAll.onCheckItem('<?php echo $id; ?>')"<?php } ?>/>

                                <label for="<?php echo $id; ?>" class="not-content"></label>
                                <a href="file_info.php?<?php echo $urlEntryDirectory; ?>">
                                    <span class="icomoon icon-folder"></span>
                                </a>
                            </div>
                            <a href="index.php?<?php echo $urlEntryDirectory; ?>" class="file-name">
                                <span><?php echo $entryFilename; ?></span>
                            </a>
                        </li>

                        <?php $countLoopEntry++; ?>
                    <?php } else if (FileInfo::isTypeFile($entryPath)) { ?>
                        <li class="type-file <?php if ($isOddEntrys && $indexLoop + 1 === $countEntrys) { ?> entry-odd<?php } ?>">
                            <div class="icon">
                                <?php $id = 'file-' . AppDirectory::rawEncode($entryFilename); ?>

                                <input
                                        type="checkbox"
                                        name="entrys[]"
                                        id="<?php echo $id; ?>"
                                        value="<?php echo AppDirectory::rawEncode($entryFilename); ?>"
                                        checked="checked"
                                        <?php if ($appConfig->get('enable_disable.count_checkbox_file_javascript')) { ?> onclick="javascript:CheckboxCheckAll.onCheckItem('<?php echo $id; ?>')"<?php } ?>/>

                                <label for="<?php echo $id; ?>" class="not-content"></label>
                                <span class="icomoon icon-file"></span>
                            </div>
                            <a href="file_info.php<?php echo $appParameter->toString(); ?>&<?php echo AppDirectory::PARAMETER_NAME_URL . '=' . AppDirectory::rawEncode($entryFilename); ?>" class="file-name">
                                <span><?php echo $entryFilename; ?></span>
                            </a>
                        </li>

                        <?php $countLoopEntry++; ?>
                    <?php } ?>

                    <?php $indexLoop++; ?>
                <?php } ?>

                <?php if ($countLoopEntry <= 0) { ?>
                    <?php $appAlert->danger(lng('file_action.alert.no_item_selected_exists'), ALERT_INDEX, 'index.php' . $appParameter->toString()); ?>
                <?php } ?>

                <li class="checkbox-all">
                    <input type="checkbox" name="checked_all_entry" id="checked-all-entry" onclick="javascript:CheckboxCheckAll.onCheckAll();" checked="checked"/>
                    <label for="checked-all-entry">
                        <span><?php echo lng('home.checkbox_all_entry'); ?></span>
                        <?php if ($appConfig->get('enable_disable.count_checkbox_file_javascript')) { ?>
                            <span id="checkall-count"></span>
                            <script type="text/javascript">
                                OnLoad.add(function() {
                                    CheckboxCheckAll.onInitForm('form-list-file', 'checked-all-entry', 'checkall-count');
                                });
                            </script>
                        <?php } ?>
                    </label>
                </li>
            </ul>

            <ul class="form-element">
                <?php if ($nameAction == FILE_ACTION_RENAME_MULTI) { ?>
                    <?php foreach ($listEntrys AS $entryFilename) { ?>
                        <?php $entryPath = FileInfo::filterPaths($appDirectory->getDirectory() . SP . $entryFilename); ?>

                        <li class="input">
                            <?php $valueModifierRename = $entryFilename; ?>

                            <?php if (isset($forms['rename']['modifier_entrys'][$valueModifierRename])) { ?>
                                <?php $valueModifierRename = $forms['rename']['modifier_entrys'][$valueModifierRename]; ?>
                            <?php } ?>

                            <?php if (FileInfo::isTypeDirectory($entryPath)) { ?>
                                <span><?php echo lng('file_action.form.input.rename.label_name_directory', 'name', $entryFilename); ?></span>
                                <input type="text" name="rename_entrys[<?php echo AppDirectory::rawEncode($entryFilename); ?>]" value="<?php echo htmlspecialchars($valueModifierRename); ?>" placeholder="<?php echo lng('file_action.form.placeholder.rename.input_name_directory'); ?>"/>
                            <?php } else { ?>
                                <span><?php echo lng('file_action.form.input.rename.label_name_file', 'name', $entryFilename); ?></span>
                                <input type="text" name="rename_entrys[<?php echo AppDirectory::rawEncode($entryFilename); ?>]" value="<?php echo htmlspecialchars($valueModifierRename); ?>" placeholder="<?php echo lng('file_action.form.placeholder.rename.input_name_file'); ?>"/>
                            <?php } ?>
                        </li>
                    <?php } ?>

                    <li class="button">
                        <button type="submit" name="rename_button" id="button-save-on-javascript">
                            <span><?php echo lng('file_action.form.button.rename'); ?></span>
                        </button>
                        <a href="index.php<?php echo $appParameter->toString(); ?>">
                            <span><?php echo lng('file_action.form.button.cancel'); ?></span>
                        </a>
                    </li>
                <?php } else if ($nameAction == FILE_ACTION_COPY_MULTI) { ?>
                    <li class="input">
                        <span><?php echo lng('file_action.form.input.copy.label_path_copy'); ?></span>
                        <input type="text" name="path_copy" value="<?php echo htmlspecialchars($forms['copy']['path_copy']); ?>" placeholder="<?php echo lng('file_action.form.placeholder.copy.input_path_copy'); ?>"/>
                    </li>
                    <li class="radio-choose">
                        <ul class="radio-choose-tab">
                            <li>
                                <input type="radio" name="mode" value="<?php echo FILE_ACTION_COPY_MULTI_MODE_COPY; ?>" id="mode-copy"<?php if ($forms['copy']['mode'] === FILE_ACTION_COPY_MULTI_MODE_COPY) { ?> checked="checked"<?php } ?>/>
                                <label for="mode-copy">
                                    <span><?php echo lng('file_action.form.input.copy.label_mode_copy'); ?></span>
                                </label>
                            </li>
                            <li>
                                <input type="radio" name="mode" value="<?php echo FILE_ACTION_COPY_MULTI_MODE_MOVE; ?>" id="mode-move"<?php if ($forms['copy']['mode'] === FILE_ACTION_COPY_MULTI_MODE_MOVE) { ?> checked="checked"<?php } ?>/>
                                <label for="mode-move">
                                    <span><?php echo lng('file_action.form.input.copy.label_mode_move'); ?></span>
                                </label>
                            </li>
                        </ul>
                    </li>
                    <li class="radio-choose">
                        <span><?php echo lng('file_action.form.input.copy.label_if_has_entry_is_exists'); ?></span>
                        <ul class="radio-choose-tab">
                            <li>
                                <input type="radio" name="exists_func" value="<?php echo FILE_ACTION_COPY_MULTI_EXISTS_FUNC_OVERRIDE; ?>" id="exists_func_override"<?php if ($forms['copy']['exists_func'] === FILE_ACTION_COPY_MULTI_EXISTS_FUNC_OVERRIDE) { ?> checked="checked"<?php } ?>/>
                                <label for="exists_func_override">
                                    <span><?php echo lng('file_action.form.input.copy.label_exists_func_override'); ?></span>
                                </label>
                            </li>
                            <li>
                                <input type="radio" name="exists_func" value="<?php echo FILE_ACTION_COPY_MULTI_EXISTS_FUNC_SKIP; ?>" id="exists_func_skip"<?php if ($forms['copy']['exists_func'] === FILE_ACTION_COPY_MULTI_EXISTS_FUNC_SKIP) { ?> checked="checked"<?php } ?>/>
                                <label for="exists_func_skip">
                                    <span><?php echo lng('file_action.form.input.copy.label_exists_func_skip'); ?></span>
                                </label>
                            </li>
                            <li>
                                <input type="radio" name="exists_func" value="<?php echo FILE_ACTION_COPY_MULTI_EXISTS_FUNC_RENAME; ?>" id="exists_func_rename"<?php if ($forms['copy']['exists_func'] == FILE_ACTION_COPY_MULTI_EXISTS_FUNC_RENAME) { ?> checked="checked"<?php } ?>/>
                                <label for="exists_func_rename">
                                    <span><?php echo lng('file_action.form.input.copy.label_exists_func_rename'); ?></span>
                                </label>
                            </li>
                        </ul>
                    </li>

                    <li class="button">
                        <button type="submit" name="copy_button" id="button-save-on-javascript">
                            <span><?php echo lng('file_action.form.button.copy'); ?></span>
                        </button>
                        <a href="index.php<?php echo $appParameter->toString(); ?>">
                            <span><?php echo lng('file_action.form.button.cancel'); ?></span>
                        </a>
                    </li>
                <?php } else if ($nameAction == FILE_ACTION_DELETE_MULTI) { ?>
                    <li class="accept">
                        <span><?php echo lng('file_action.form.input.delete.accept_message'); ?></span>
                    </li>

                    <li class="button">
                        <button type="submit" name="delete_button" id="button-save-on-javascript">
                            <span><?php echo lng('file_action.form.button.delete'); ?></span>
                        </button>
                        <a href="index.php<?php echo $appParameter->toString(); ?>">
                            <span><?php echo lng('file_action.form.button.cancel'); ?></span>
                        </a>
                    </li>
                <?php } else if ($nameAction == FILE_ACTION_ZIP_MULTI) { ?>
                    <li class="input">
                        <span><?php echo lng('file_action.form.input.zip.label_path_create_zip'); ?></span>
                        <input type="text" name="path_create_zip" value="<?php echo htmlspecialchars($forms['zip']['path_create_zip']); ?>" placeholder="<?php echo lng('file_action.form.placeholder.zip.input_path_create_zip'); ?>"/>
                    </li>
                    <li class="input">
                        <span><?php echo lng('file_action.form.input.zip.label_name_zip'); ?></span>
                        <input type="text" name="name_zip" value="<?php echo htmlspecialchars($forms['zip']['name_zip']); ?>" placeholder="<?php echo lng('file_action.form.placeholder.zip.input_name_zip'); ?>"/>
                    </li>
                    <li class="checkbox">
                        <span><?php echo lng('file_action.form.input.zip.label_more_options'); ?></span>
                        <ul>
                            <li>
                                <input type="checkbox" name="override_zip" value="1" id="override-zip"<?php if ($forms['zip']['override_zip'] == true) { ?> checked="checked"<?php } ?>/>
                                <label for="override-zip">
                                    <span><?php echo lng('file_action.form.input.zip.label_override_zip'); ?></span>
                                </label>
                            </li>
                            <li>
                                <input type="checkbox" name="delete_source" value="1" id="delete-source"<?php if ($forms['zip']['delete_source'] == true) { ?> checked="checked"<?php } ?>/>
                                <label for="delete-source">
                                    <span><?php echo lng('file_action.form.input.zip.label_delete_source'); ?></span>
                                </label>
                            </li>
                        </ul>
                    </li>

                    <li class="button">
                        <button type="submit" name="zip_button" id="button-save-on-javascript">
                            <span><?php echo lng('file_action.form.button.zip'); ?></span>
                        </button>
                        <a href="index.php<?php echo $appParameter->toString(); ?>">
                            <span><?php echo lng('file_action.form.button.cancel'); ?></span>
                        </a>
                    </li>
                <?php } else if ($nameAction == FILE_ACTION_CHMOD_MULTI) { ?>
                    <li class="input">
                        <span><?php echo lng('file_action.form.input.chmod.label_chmod_directory'); ?></span>
                        <input type="number" name="chmod_directory" value="<?php echo $forms['chmod']['directory']; ?>" placeholder="<?php echo lng('file_action.form.placeholder.chmod.input_chmod_directory'); ?>"/>
                    </li>
                    <li class="input">
                        <span><?php echo lng('file_action.form.input.chmod.label_chmod_file'); ?></span>
                        <input type="number" name="chmod_file" value="<?php echo $forms['chmod']['file']; ?>" placeholder="<?php echo lng('file_action.form.placeholder.chmod.input_chmod_file'); ?>"/>
                    </li>

                    <li class="button">
                        <button type="submit" name="chmod_button" id="button-save-on-javascript">
                            <span><?php echo lng('file_action.form.button.chmod'); ?></span>
                        </button>
                        <a href="index.php<?php echo $appParameter->toString(); ?>">
                            <span><?php echo lng('file_action.form.button.cancel'); ?></span>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>

        <ul class="action-multi">
            <?php if ($nameAction != FILE_ACTION_RENAME_MULTI) { ?>
                <li>
                    <button type="submit" name="action" value="<?php echo FILE_ACTION_RENAME_MULTI; ?>">
                        <span class="icomoon icon-edit"></span>
                        <span class="label"><?php echo lng('home.action_multi.rename'); ?></span>
                    </button>
                </li>
            <?php } ?>

            <?php if ($nameAction != FILE_ACTION_COPY_MULTI) { ?>
                <li>
                    <button type="submit" name="action" value="<?php echo FILE_ACTION_COPY_MULTI; ?>">
                        <span class="icomoon icon-copy"></span>
                        <span class="label"><?php echo lng('home.action_multi.copy'); ?></span>
                    </button>
                </li>
            <?php } ?>

            <?php if ($nameAction != FILE_ACTION_DELETE_MULTI) { ?>
                <li>
                    <button type="submit" name="action" value="<?php echo FILE_ACTION_DELETE_MULTI; ?>">
                        <span class="icomoon icon-trash"></span>
                        <span class="label"><?php echo lng('home.action_multi.delete'); ?></span>
                    </button>
                </li>
            <?php } ?>

            <?php if ($nameAction != FILE_ACTION_ZIP_MULTI) { ?>
                <li>
                    <button type="submit" name="action" value="<?php echo FILE_ACTION_ZIP_MULTI; ?>">
                        <span class="icomoon icon-archive"></span>
                        <span class="label"><?php echo lng('home.action_multi.zip'); ?></span>
                    </button>
                </li>
            <?php } ?>

            <?php if ($nameAction != FILE_ACTION_CHMOD_MULTI) { ?>
                <li>
                    <button type="submit" name="action" value="<?php echo FILE_ACTION_CHMOD_MULTI; ?>">
                        <span class="icomoon icon-key"></span>
                        <span class="label"><?php echo lng('home.action_multi.chmod'); ?></span>
                    </button>
                </li>
            <?php } ?>
        </ul>
    </form>

<?php require_once('incfiles' . SP . 'footer.php'); ?>