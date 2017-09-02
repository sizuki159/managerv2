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
    else if ($appDirectory->isPermissionDenyPath())
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
        $title = lng('file_delete.title_page_directory');
    else
        $title = lng('file_delete.title_page_file');

    $themes  = [ env('resource.filename.theme.file') ];
    $appAlert->setID(ALERT_FILE_DELETE);
    require_once('incfiles' . SP . 'header.php');

    if (isset($_POST['delete'])) {
        if ($isDirectory) {
            $isHasFileAppPermission = false;

            if (FileInfo::rrmdir(FileInfo::filterPaths($appDirectory->getDirectory() . SP . $appDirectory->getName()), null, $isHasFileAppPermission) && $isHasFileAppPermission == false) {
                $appParameter->remove(AppDirectory::PARAMETER_NAME_URL);
                $appAlert->success(lng('file_delete.alert.delete_directory_success', 'filename', $appDirectory->getName()), ALERT_INDEX, 'index.php' . $appParameter->toString(true));
            } else {
                if ($isHasFileAppPermission) {
                    $appParameter->remove(AppDirectory::PARAMETER_NAME_URL);
                    $appParameter->toString(true);

                    $appAlert->warning(lng('file_delete.alert.not_delete_file_app', 'filename', $appDirectory->getName()), ALERT_INDEX);
                    $appAlert->success(lng('file_delete.alert.delete_entry_in_directory_success', 'filename', $appDirectory->getName()), ALERT_INDEX, 'index.php' . $appParameter->toString());
                } else {
                    $appAlert->danger(lng('file_delete.alert.delete_directory_failed', 'filename', $appDirectory->getName()));
                }
            }
        } else {
            if (FileInfo::unlink(FileInfo::filterPaths($appDirectory->getDirectory() . SP . $appDirectory->getName()))) {
                $appParameter->remove(AppDirectory::PARAMETER_NAME_URL);
                $appAlert->success(lng('file_delete.alert.delete_file_success', 'filename', $appDirectory->getName()), ALERT_INDEX, 'index.php' . $appParameter->toString(true));
            } else {
                $appAlert->danger(lng('file_delete.alert.delete_file_failed', 'filename', $appDirectory->getName()));
            }
        }
    }
?>

    <?php $appAlert->display(); ?>
    <?php $appLocationPath->display(); ?>

    <div class="form-action">
        <div class="title">
            <?php if ($isDirectory) { ?>
                <span><?php echo lng('file_delete.title_page_directory'); ?>: <?php echo $appDirectory->getName(); ?></span>
            <?php } else { ?>
                <span><?php echo lng('file_delete.title_page_file'); ?>: <?php echo $appDirectory->getName(); ?></span>
            <?php } ?>
        </div>
        <form action="file_delete.php<?php echo $appParameter->toString(); ?>" method="post">
            <input type="hidden" name="<?php echo $boot->getCFSRToken()->getName(); ?>" value="<?php echo $boot->getCFSRToken()->getToken(); ?>"/>

            <ul class="form-element">
                <li class="accept">
                    <?php if ($isDirectory) { ?>
                        <span><?php echo lng('file_delete.form.accept_delete_directory', 'filename', $appDirectory->getName()); ?></span>
                    <?php } else { ?>
                        <span><?php echo lng('file_delete.form.accept_delete_file', 'filename', $appDirectory->getName()); ?></span>
                    <?php } ?>
                </li>
                <li class="button">
                    <button type="submit" name="delete" id="button-save-on-javascript">
                        <span><?php echo lng('file_delete.form.button.delete'); ?></span>
                    </button>
                    <a href="index.php<?php echo $appParameter->toString(); ?>">
                        <span><?php echo lng('file_delete.form.button.cancel'); ?></span>
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
            <a href="file_copy.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-copy"></span>
                <span><?php echo lng('file_info.menu_action.copy'); ?></span>
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
