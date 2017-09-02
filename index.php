<?php

    use Librarys\App\AppDirectory;
    use Librarys\App\AppPaging;
    use Librarys\App\AppLocationPath;
    use Librarys\App\AppParameter;
    use Librarys\App\AppFileCopy;

    use Librarys\File\FileInfo;
    use Librarys\File\FileMime;

    define('LOADED', 1);
    require_once('incfiles' . DIRECTORY_SEPARATOR . 'global.php');
    requireDefine('file_action');

    $title   = lng('home.title_page_root');
    $themes  = [ env('resource.filename.theme.file') ];
    $scripts = [ env('resource.filename.javascript.checkbox_checkall') ];
    $appAlert->setID(ALERT_INDEX);

    require_once('incfiles' . SP . 'header.php');

    $handler              = null;
    $isPermissionDenyPath = $appDirectory->isPermissionDenyPath();

    if ($isPermissionDenyPath)
        $appAlert->danger(lng('home.alert.path_not_permission', 'path', $appDirectory->getDirectory()));

    if ($isPermissionDenyPath || FileInfo::isReadable($appDirectory->getDirectory()) == false || FileInfo::isTypeDirectory($appDirectory->getDirectory()) == false) {
        $appDirectory->setDirectory(env('SERVER.DOCUMENT_ROOT'));
        $handler = FileInfo::scanDirectory($appDirectory->getDirectory());

        if ($isPermissionDenyPath == false)
            $appAlert->danger(lng('home.alert.path_not_exists'));
    } else {
        $handler = FileInfo::scanDirectory($appDirectory->getDirectory());
    }

    if ($handler === false || $handler == null) {
        $appDirectory->setDirectory(env('SERVER.DOCUMENT_ROOT'));
        $handler = FileInfo::scanDirectory($appDirectory->getDirectory());
        $appAlert->danger(lng('home.alert.path_not_receiver_list'));
    }

    if (is_array($handler) == false)
        $handler = array();

    $handlerCount = count($handler);
    $handlerList  = array();

    $arrayFolder = array();
    $arrayFile   = array();

    foreach ($handler AS $entry) {
        if ($entry != '.' && $entry != '..') {
            if ($entry == env('application.directory') && $appDirectory->isAccessParentPath())
                ;
            else if (FileInfo::isTypeDirectory($appDirectory->getDirectory() . SP . $entry))
                $arrayFolder[] = $entry;
            else
                $arrayFile[] = $entry;
        }
    }

    if (count($arrayFolder) > 0) {
        asort($arrayFolder);

        foreach ($arrayFolder AS $entry)
            $handlerList[] = [ 'name' => $entry, 'is_directory' => true ];
    }

    if (count($arrayFile) > 0) {
        asort($arrayFile);

        foreach ($arrayFile AS $entry)
            $handlerList[] = [ 'name' => $entry, 'is_directory' => false ];
    }

    $handlerIsOdd = false;
    $handlerCount = count($handlerList);
    $handlerPage  = array(
        'current'       => $appDirectory->getPage(),
        'begin'         => 0,
        'end'           => $handlerCount,
        'total'         => 0,
        'entry_on_page' => $handlerCount,
        'list_max'      => $appConfig->get('paging.file_home_list')
    );

    if ($handlerCount > 0 && $handlerPage['list_max'] > 0 && $handlerCount > $handlerPage['list_max']) {
        $handlerPage['total'] = ceil($handlerCount / $handlerPage['list_max']);

        if ($handlerPage['total'] <= 0 || $handlerPage['current'] > $handlerPage['total'])
            $handlerPage['current'] = 1;

        $handlerPage['begin'] = ($handlerPage['current'] * $handlerPage['list_max']) - $handlerPage['list_max'];
        $handlerPage['end']   = ($handlerPage['begin'] + $handlerPage['list_max']);

        if ($handlerPage['end'] > $handlerCount)
            $handlerPage['end'] = $handlerCount;

        if (($handlerPage['end'] - $handlerPage['begin']) % 2 !== 0)
            $handlerIsOdd = true;

        $handlerPage['entry_on_page'] = $handlerPage['end'] - $handlerPage['begin'];
    }

    if ($handlerPage['entry_on_page'] % 2 !== 0)
        $handlerIsOdd = true;

    $bufferBack = null;

    if (preg_replace('|[a-zA-Z]+:|', '', FileInfo::filterPaths($appDirectory->getDirectory())) != SP) {
        $backPath      = strrchr($appDirectory->getDirectory(), SP);
        $backDirectory = $backPath;

        if ($backPath !== false) {
            $backPath = substr($appDirectory->getDirectory(), 0, strlen($appDirectory->getDirectory()) - strlen($backPath));
            $backPath = 'index.php?' . AppDirectory::PARAMETER_DIRECTORY_URL . '=' . AppDirectory::rawEncode($backPath);

            if (strpos($backDirectory, SP) !== false)
                $backDirectory = str_replace(SP, null, $backDirectory);
        } else {
            $backPath      = 'index.php';
            $backDirectory = $appDirectory->getDirectory();
        }

        $bufferBack .= '<li class="back">';
            $bufferBack .= '<a href="' . $backPath . '">';
                $bufferBack .= '<span class="icomoon icon-folder-open"></span>';
                $bufferBack .= '<strong>' . $backDirectory . '</strong>';
            $bufferBack .= '</a>';
        $bufferBack .= '</li>';
    }

    $appLocationPath = new AppLocationPath($appDirectory, 'index.php');
    $pagePaging      = new AppPaging(
        'index.php?' . AppDirectory::PARAMETER_DIRECTORY_URL . '=' . $appDirectory->getDirectoryEncode(),

        'index.php?' . AppDirectory::PARAMETER_DIRECTORY_URL . '=' . $appDirectory->getDirectoryEncode() .
                 '&' . AppDirectory::PARAMETER_PAGE_URL      . '='
    );

    $appParameter = new AppParameter();
    $appParameter->add(AppDirectory::PARAMETER_DIRECTORY_URL, $appDirectory->getDirectoryEncode(), true);
    $appParameter->add(AppDirectory::PARAMETER_PAGE_URL,      $handlerPage['current'],            $handlerPage['current'] > 1);

    $appFileCopy = new AppFileCopy();

    if ($appFileCopy->isSession()) {
        $isDirectoryCopy         = FileInfo::isTypeDirectory(FileInfo::filterPaths($appFileCopy->getDirectory() . SP . $appFileCopy->getName()));
        $isDirectoryCurrentEqual = false;

        if (isset($_GET['cancel_copy_move'])) {
            if (addslashes($_GET['cancel_copy_move']) === md5(date('H', time()))) {
                $appFileCopy->clearSession();

                if ($isDirectoryCopy) {
                    if ($appFileCopy->isMove() == false)
                        $appAlert->setLangMsg('file_copy.alert.cancel_copy_directory_success', 'filename', $appFileCopy->getName());
                    else
                        $appAlert->setLangMsg('file_copy.alert.cancel_move_directory_success', 'filename', $appFileCopy->getName());
                } else {
                    if ($appFileCopy->isMove() == false)
                        $appAlert->setLangMsg('file_copy.alert.cancel_copy_file_success', 'filename', $appFileCopy->getName());
                    else
                        $appAlert->setLangMsg('file_copy.alert.cancel_move_file_success', 'filename', $appFileCopy->getName());
                }

                $appAlert->success(null, ALERT_INDEX, 'index.php' . $appParameter->toString());
            } else {
                if ($isDirectoryCopy) {
                    if ($appFileCopy->isMove() == false)
                        $appAlert->setLangMsg('file_copy.alert.cancel_copy_directory_failed', 'filename', $appFileCopy->getName());
                    else
                        $appAlert->setLangMsg('file_copy.alert.cancel_move_directory_failed', 'filename', $appFileCopy->getName());
                } else {
                    if ($appFileCopy->isMove() == false)
                        $appAlert->setLangMsg('file_copy.alert.cancel_copy_file_failed', 'filename', $appFileCopy->getName());
                    else
                        $appAlert->setLangMsg('file_copy.alert.cancel_move_file_failed', 'filename', $appFileCopy->getName());
                }

                $appAlert->danger(null);
            }
        }

        $appFileCopyHrefParamater = new AppParameter();
        $appFileCopyHrefParamater->add(AppDirectory::PARAMETER_DIRECTORY_URL, $appFileCopy->getDirectory(), true);
        $appFileCopyHrefParamater->add(AppDirectory::PARAMETER_NAME_URL,      $appFileCopy->getName(),      true);
        $appFileCopyHref = 'file_copy.php' . $appFileCopyHrefParamater->toString(true);

        if (FileInfo::filterPaths($appDirectory->getDirectory() . SP . $appFileCopy->getName()) == FileInfo::filterPaths($appFileCopy->getDirectory() . SP . $appFileCopy->getName())) {
            if ($appFileCopy->isMove() == false)
                $appAlert->danger(lng('file_copy.alert.path_copy_is_equal_path_current'));
            else
                $appAlert->danger(lng('file_copy.alert.path_move_is_equal_path_current'));

            $isDirectoryCurrentEqual = true;
        }

        $appParameter->add('cancel_copy_move', md5(date('H', time())), true);
        $appFileCopyHrefCancel = 'index.php' . $appParameter->toString(true);

        if ($appFileCopy->isMove() == false) {
            if ($isDirectoryCurrentEqual) {
                if ($isDirectoryCopy)
                    $appAlert->info(lng(
                        'file_copy.alert.choose_directory_path_for_copy_directory_cancel',
                        'filename', $appFileCopy->getName(),
                        'href_cancel', $appFileCopyHrefCancel
                    ), ALERT_INDEX);
                else
                    $appAlert->info(lng(
                        'file_copy.alert.choose_directory_path_for_copy_file_cancel',
                        'filename', $appFileCopy->getName(),
                        'href_cancel', $appFileCopyHrefCancel
                    ), ALERT_INDEX);
            } else {
                if ($isDirectoryCopy)
                    $appAlert->info(lng(
                        'file_copy.alert.choose_directory_path_for_copy_directory_href',
                        'filename', $appFileCopy->getName(),
                        'href', $appFileCopyHref,
                        'href_cancel', $appFileCopyHrefCancel
                    ), ALERT_INDEX);
                else
                    $appAlert->info(lng('file_copy.alert.choose_directory_path_for_copy_file_href',
                        'filename', $appFileCopy->getName(),
                        'href', $appFileCopyHref,
                        'href_cancel', $appFileCopyHrefCancel
                    ), ALERT_INDEX);
            }
        } else {
            if ($isDirectoryCurrentEqual) {
                if ($isDirectoryCopy)
                    $appAlert->info(lng('file_copy.alert.choose_directory_path_for_move_directory_href',
                        'filename', $appFileCopy->getName(),
                        'href_cancel', $appFileCopyHrefCancel
                    ), ALERT_INDEX);
                else
                    $appAlert->info(lng('file_copy.alert.choose_directory_path_for_move_file_href',
                        'filename', $appFileCopy->getName(),
                        'href_cancel', $appFileCopyHrefCancel
                    ), ALERT_INDEX);
            } else {
                if ($isDirectoryCopy)
                    $appAlert->info(lng('file_copy.alert.choose_directory_path_for_move_directory_href',
                        'filename', $appFileCopy->getName(),
                        'href', $appFileCopyHref,
                        'href_cancel', $appFileCopyHrefCancel
                    ), ALERT_INDEX);
                else
                    $appAlert->info(lng('file_copy.alert.choose_directory_path_for_move_file_href',
                        'filename', $appFileCopy->getName(),
                        'href', $appFileCopyHref,
                        'href_cancel', $appFileCopyHrefCancel
                    ), ALERT_INDEX);
            }
        }

        $appParameter->remove('cancel_copy_move');
        $appParameter->toString(true);

        $appFileCopy->setPath($appDirectory->getDirectory());
        $appFileCopy->flushSession();
    }
?>

    <?php echo $appAlert->display(); ?>
    <?php echo $appLocationPath->display(); ?>

    <form action="file_action.php<?php echo $appParameter->toString(); ?>" method="post" id="form-list-file-home">
        <input type="hidden" name="<?php echo $boot->getCFSRToken()->getName(); ?>" value="<?php echo $boot->getCFSRToken()->getToken(); ?>"/>

        <ul class="file-list">
            <?php echo $bufferBack; ?>

            <?php if ($handlerCount > 0) { ?>
                <?php for ($i = $handlerPage['begin']; $i < $handlerPage['end']; ++$i) { ?>
                    <?php $entry        = $handlerList[$i]; ?>
                    <?php $entryPath    = FileInfo::filterPaths($appDirectory->getDirectory() . SP . $entry['name']); ?>
                    <?php $chmodPerms   = FileInfo::getChmodPermission($entryPath); ?>
                    <?php $urlParameter = $appParameter->toString() . '&' . AppDirectory::PARAMETER_NAME_URL . '=' . AppDirectory::rawEncode($entry['name']); ?>

                    <?php if ($entry['is_directory']) { ?>
                        <li class="type-directory <?php if ($handlerIsOdd && $i + 1 === $handlerPage['end']) { ?> entry-odd<?php } ?><?php if ($handlerPage['entry_on_page'] === 1) { ?> entry-only-one<?php } ?>">
                            <div class="icon">
                                <?php $id = 'folder-' . AppDirectory::rawEncode($entry['name']); ?>

                                <input
                                        type="checkbox"
                                        name="entrys[]"
                                        id="<?php echo $id; ?>"
                                        value="<?php echo AppDirectory::rawEncode($entry['name']); ?>"
                                        <?php if ($appConfig->get('enable_disable.count_checkbox_file_javascript')) { ?> onclick="javascript:CheckboxCheckAll.onCheckItem('<?php echo $id; ?>')"<?php } ?>/>

                                <label for="<?php echo $id; ?>" class="not-content"></label>
                                <a href="file_info.php<?php echo $urlParameter; ?>">
                                    <span class="icomoon icon-folder"></span>
                                </a>
                            </div>
                            <a href="index.php?<?php echo AppDirectory::PARAMETER_DIRECTORY_URL . '=' . AppDirectory::rawEncode($entryPath); ?>" class="file-name">
                                <span><?php echo $entry['name']; ?></span>
                            </a>
                            <a href="file_chmod.php<?php echo $urlParameter; ?>" class="chmod-permission">
                                <span><?php echo $chmodPerms; ?></span>
                            </a>
                        </li>
                    <?php } else { ?>
                        <?php $info     = new FileInfo($entryPath); ?>
                        <?php $mime     = new FileMime($info); ?>
                        <?php $icon     = null; ?>
                        <?php $isEdit   = false; ?>
                        <?php $editHref = 'file_edit_text.php' . $urlParameter; ?>

                        <?php
                            if ($mime->isFormatText()) {
                                $icon   = 'icon-file-text';
                                $isEdit = true;
                            } else if ($mime->isFormatCode()) {
                                $icon   = 'icon-file-code';
                                $isEdit = true;
                            } else if ($mime->isFormatArchive()) {
                                $icon   = 'icon-file-archive';
                                $isEdit = false;

                                if ($mime->isFormatArchiveZip()) {
                                    $isEdit = true;
                                    $editHref = 'file_unzip.php' . $urlParameter;
                                }
                            } else if ($mime->isFormatAudio()) {
                                $icon   = 'icon-file-audio';
                                $isEdit = false;
                            } else if ($mime->isFormatVideo()) {
                                $icon   = 'icon-file-video';
                                $isEdit = false;
                            } else if ($mime->isFormatDocument()) {
                                $icon   = 'icon-file-document';
                                $isEdit = false;
                            } else if ($mime->isFormatImage()) {
                                $icon   = 'icon-file-image';
                                $isEdit = false;
                            } else if ($mime->isFormatSource()) {
                                $icon   = 'icon-file-code';
                                $isEdit = true;
                            } else {
                                $icon   = 'icon-file';
                                $isEdit = true;
                            }
                        ?>

                        <li class="type-file <?php if ($handlerIsOdd && $i + 1 === $handlerPage['end']) { ?> entry-odd<?php } ?>">
                            <div class="icon">
                                <?php $id = 'file-' . AppDirectory::rawEncode($entry['name']); ?>

                                <input
                                        type="checkbox"
                                        name="entrys[]"
                                        id="<?php echo $id; ?>"
                                        value="<?php echo AppDirectory::rawEncode($entry['name']); ?>"
                                        <?php if ($appConfig->get('enable_disable.count_checkbox_file_javascript')) { ?> onclick="javascript:CheckboxCheckAll.onCheckItem('<?php echo $id; ?>')"<?php } ?>/>

                                <label for="<?php echo $id; ?>" class="not-content"></label>

                                <?php if ($isEdit) { ?><a href="<?php echo $editHref; ?>"><?php } ?>
                                    <span class="icomoon <?php echo $icon; ?>"></span>
                                <?php if ($isEdit) { ?></a><?php } ?>
                            </div>
                            <a href="file_info.php<?php echo $urlParameter; ?>" class="file-name">
                                <span><?php echo $entry['name']; ?></span>
                            </a>
                            <div class="chmod-size">
                                <span class="size"><?php echo FileInfo::sizeToString($info->getFileSize()); ?></span><span>,</span>
                                <a href="file_chmod.php<?php echo $urlParameter; ?>" class="chmod-permission">
                                    <span><?php echo $chmodPerms; ?></span>
                                </a>
                            </div>
                        </li>
                    <?php } ?>
                <?php } ?>

                <li class="checkbox-all">
                    <input type="checkbox" name="checked_all_entry" id="checked-all-entry" onclick="javascript:CheckboxCheckAll.onCheckAll();"/>
                    <label for="checked-all-entry">
                        <span><?php echo lng('home.checkbox_all_entry'); ?></span>
                        <?php if ($appConfig->get('enable_disable.count_checkbox_file_javascript')) { ?>
                            <span id="checkall-count"></span>
                            <script type="text/javascript">
                                OnLoad.add(function() {
                                    CheckboxCheckAll.onInitForm('form-list-file-home', 'checked-all-entry', 'checkall-count');
                                });
                            </script>
                        <?php } ?>
                    </label>
                </li>

                <?php if ($appConfig->get('paging.file_home_list') > 0 && $handlerPage['total'] > 1) { ?>
                    <li class="paging">
                        <?php echo $pagePaging->display($handlerPage['current'], $handlerPage['total']); ?>
                    </li>
                <?php } ?>
            <?php } else { ?>
                <li class="empty entry-odd">
                    <span class="icomoon icon-folder-o"></span>
                    <span><?php echo lng('home.directory_empty'); ?></span>
                </li>
            <?php } ?>
        </ul>

        <?php if ($handlerCount > 0) { ?>
            <ul class="action-multi">
                <li>
                    <button type="submit" name="action" value="<?php echo FILE_ACTION_RENAME_MULTI; ?>">
                        <span class="icomoon icon-edit"></span>
                        <span class="label"><?php echo lng('home.action_multi.rename'); ?></span>
                    </button>
                </li>
                <li>
                    <button type="submit" name="action" value="<?php echo FILE_ACTION_COPY_MULTI; ?>">
                        <span class="icomoon icon-copy"></span>
                        <span class="label"><?php echo lng('home.action_multi.copy'); ?></span>
                    </button>
                </li>
                <li>
                    <button type="submit" name="action" value="<?php echo FILE_ACTION_DELETE_MULTI; ?>">
                        <span class="icomoon icon-trash"></span>
                        <span class="label"><?php echo lng('home.action_multi.delete'); ?></span>
                    </button>
                </li>
                <li>
                    <button type="submit" name="action" value="<?php echo FILE_ACTION_ZIP_MULTI; ?>">
                        <span class="icomoon icon-archive"></span>
                        <span class="label"><?php echo lng('home.action_multi.zip'); ?></span>
                    </button>
                </li>
                <li>
                    <button type="submit" name="action" value="<?php echo FILE_ACTION_CHMOD_MULTI; ?>">
                        <span class="icomoon icon-key"></span>
                        <span class="label"><?php echo lng('home.action_multi.chmod'); ?></span>
                    </button>
                </li>
            </ul>
        <?php } ?>
    </form>

    <ul class="menu-action">
        <li>
            <a href="create.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-folder-create"></span>
                <span><?php echo lng('home.menu_action.create'); ?></span>
            </a>
        </li>
        <li>
            <a href="upload.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-folder-upload"></span>
                <span><?php echo lng('home.menu_action.upload'); ?></span>
            </a>
        </li>
        <li>
            <a href="import.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-folder-download"></span>
                <span><?php echo lng('home.menu_action.import'); ?></span>
            </a>
        </li>
    </ul>

<?php require_once('incfiles' . SP . 'footer.php'); ?>
