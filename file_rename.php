<?php

    use Librarys\File\FileInfo;
    use Librarys\File\FileMime;
    use Librarys\App\AppDirectory;
    use Librarys\App\AppLocationPath;
    use Librarys\App\AppParameter;

    define('LOADED', 1);
    require_once('incfiles' . DIRECTORY_SEPARATOR . 'global.php');

    if ($appDirectory->isFileExistsDirectorySeparatorName() == false)
        $appAlert->danger(lng('home.alert.path_not_exists'), ALERT_INDEX, env('app.http.host'));
    else if ($appDirectory->isPermissionDenyPath($appDirectory->getDirectory()))
        $appAlert->danger(lng('home.alert.path_not_permission', 'path', $appDirectory->getDirectoryAndName()), ALERT_INDEX, env('app.http.host'));

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
        $title = lng('file_rename.title_page_directory');
    else
        $title = lng('file_rename.title_page_file');

    $themes  = [ env('resource.filename.theme.file') ];
    $appAlert->setID(ALERT_FILE_RENAME);
    require_once('incfiles' . SP . 'header.php');

    $forms = [
        'name'     => $appDirectory->getName(),
        'path_old' => null,
        'path_new' => null
    ];

    if (isset($_POST['rename'])) {
        $forms['name'] = addslashes($_POST['name']);

        if (empty($forms['name'])) {
            if ($isDirectory)
                $appAlert->danger(lng('file_rename.alert.not_input_name_directory'));
            else
                $appAlert->danger(lng('file_rename.alert.not_input_name_file'));
        } else if (FileInfo::isNameValidate($forms['name']) == false) {
            if ($isDirectory)
                $appAlert->danger(lng('file_rename.alert.name_directory_not_validate', 'validate', FileInfo::FILENAME_VALIDATE));
            else
                $appAlert->danger(lng('file_rename.alert.name_file_not_validate', 'validate', FileInfo::FILENAME_VALIDATE));
        } else if ($appDirectory->getName() == $forms['name']) {
            $appAlert->danger(lng('file_rename.alert.name_not_change'));
        } else {
            $forms['path_old'] = FileInfo::filterPaths($appDirectory->getDirectory() . SP . $appDirectory->getName());
            $forms['path_new'] = FileInfo::filterPaths($appDirectory->getDirectory() . SP . $forms['name']);

            if (FileInfo::rename($forms['path_old'], $forms['path_new']) == false) {
                if ($isDirectory)
                    $appAlert->danger(lng('file_rename.alert.rename_directory_failed', 'filename', $appDirectory->getName()));
                else
                    $appAlert->danger(lng('file_rename.alert.rename_file_failed', 'filename', $appDirectory->getName()));
            } else {
                $idAlert = null;
                $urlGoto = null;

                if ($appConfig->get('auto_redirect.file_rename', true)) {
                    $appParameter->remove(AppDirectory::PARAMETER_NAME_URL);
                    $appParameter->toString(true);

                    $idAlert = ALERT_INDEX;
                    $urlGoto = 'index.php' . $appParameter->toString();
                } else {
                    $appParameter->set(AppDirectory::PARAMETER_NAME_URL, AppDirectory::rawEncode($forms['name']), true);
                    $urlGoto = 'file_rename.php' . $appParameter->toString(true);
                }

                if ($isDirectory)
                    $appAlert->success(lng('file_rename.alert.rename_directory_success', 'filename', $appDirectory->getName()), $idAlert, $urlGoto);
                else
                    $appAlert->success(lng('file_rename.alert.rename_file_success', 'filename', $appDirectory->getName()), $idAlert, $urlGoto);
            }
        }

        $forms['name'] = stripslashes($forms['name']);
    }
?>

    <?php $appAlert->display(); ?>
    <?php $appLocationPath->display(); ?>

    <div class="form-action">
        <div class="title">
            <?php if ($isDirectory) { ?>
                <span><?php echo lng('file_rename.title_page_directory'); ?>: <?php echo $appDirectory->getName(); ?></span>
            <?php } else { ?>
                <span><?php echo lng('file_rename.title_page_file'); ?>: <?php echo $appDirectory->getName(); ?></span>
            <?php } ?>
        </div>
        <form action="file_rename.php<?php echo $appParameter->toString(); ?>" method="post">
            <input type="hidden" name="<?php echo $boot->getCFSRToken()->getName(); ?>" value="<?php echo $boot->getCFSRToken()->getToken(); ?>"/>

            <ul class="form-element">
                <li class="input">
                    <?php if ($isDirectory) { ?>
                        <span><?php echo lng('file_rename.form.input.name_directory'); ?></span>
                    <?php } else { ?>
                        <span><?php echo lng('file_rename.form.input.name_file'); ?></span>
                    <?php } ?>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($forms['name']); ?>" placeholder="<?php if ($isDirectory) echo lng('file_rename.form.placeholder.input_name_directory'); else echo lng('file_rename.form.placeholder.input_name_file'); ?>"/>
                </li>
                <li class="button">
                    <button type="submit" name="rename" id="button-save-on-javascript">
                        <span><?php echo lng('file_rename.form.button.rename'); ?></span>
                    </button>
                    <a href="index.php<?php echo $appParameter->toString(); ?>">
                        <span><?php echo lng('file_rename.form.button.cancel'); ?></span>
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
            <a href="file_copy.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-copy"></span>
                <span><?php echo lng('file_info.menu_action.copy'); ?></span>
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
