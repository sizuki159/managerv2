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
        $title = lng('file_info.title_page_directory');
    else
        $title = lng('file_info.title_page_file');

    $themes = [ env('resource.filename.theme.file') ];
    $appAlert->setID(ALERT_FILE_INFO);
    require_once('incfiles' . SP . 'header.php');

    $chownOwner = null;
    $chownGroup = null;

    if(function_exists('posix_getpwuid')) {
        $chownOwner = posix_getpwuid(FileInfo::fileOwner($fileInfo->getFilePath()));
        $chownGroup = posix_getpwuid(FileInfo::fileGroup($fileInfo->getFilePath()));

        if (is_array($chownOwner) && isset($chownOwner['name']))
            $chownOwner = trim($chownOwner['name']);
        else
            $chownOwner = null;

        if (is_array($chownGroup) && isset($chownGroup['name']))
            $chownGroup = trim($chownGroup['name']);
        else
            $chownGroup = null;
    }
?>

    <?php $appAlert->display(); ?>
    <?php $appLocationPath->display(); ?>
    <?php $imageSize = null; ?>

    <ul class="file-info">
        <li class="title">
            <?php if ($isDirectory) { ?>
                <span><?php echo lng('file_info.title_page_directory'); ?></span>
            <?php } else { ?>
                <span><?php echo lng('file_info.title_page_file'); ?></span>
            <?php } ?>
        </li>
        <?php if ($fileMime->isFormatImage()) { ?>
            <?php $imageSize = getImageSize($fileInfo->getFilePath()); ?>

            <li class="image">
                <?php if ($imageSize === false) { ?>
                    <span><?php echo lng('file_info.alert.image_error'); ?></span>
                <?php } else { ?>
                    <img
                        src="image.php<?php echo $appParameter->toString(); ?>"
                        alt="<?php echo $fileInfo->getFileName(); ?>"
                        height="auto"
                        class="<?php echo $imageClass; ?>"/>
                <?php } ?>
            </li>
        <?php } ?>
        <li class="label">
            <ul>
                <li><span><?php echo lng('file_info.label_name'); ?></span></li>
                <li><span><?php echo lng('file_info.label_format'); ?></span></li>
                <li><span><?php echo lng('file_info.label_size'); ?></span></li>
                <?php if (is_array($imageSize)) { ?>
                    <li><span><?php echo lng('file_info.label_image_size'); ?></span></li>
                <?php } ?>
                <li><span><?php echo lng('file_info.label_chmod'); ?></span></li>
                <li><span><?php echo lng('file_info.label_chown_owner'); ?></span></li>
                <li><span><?php echo lng('file_info.label_chown_group'); ?></span></li>
                <li><span><?php echo lng('file_info.label_time_modify'); ?></span></li>
            </ul>
        </li>
        <li class="value">
            <ul>
                <li>
                    <span><?php echo $fileInfo->getFileName(); ?></span>
                </li>
                <li>
                    <?php if ($isDirectory) { ?>
                        <span><?php echo lng('file_info.format_directory'); ?></span>
                    <?php } else { ?>
                        <?php if ($fileInfo->getFileExt() == null) { ?>
                            <span><?php echo lng('file_info.format_unknown'); ?></span>
                        <?php } else { ?>
                            <span><?php echo $fileInfo->getFileExt(); ?></span>
                        <?php } ?>
                    <?php } ?>
                </li>
                <li>
                    <span><?php echo FileInfo::sizeToString(filesize($fileInfo->getFilePath())); ?></span>
                </li>
                <?php if (is_array($imageSize)) { ?>
                    <li>
                        <span><?php echo $imageSize[0] . lng('file_info.value_image_size_split') . $imageSize[1]; ?></span>
                    </li>
                <?php } ?>
                <li>
                    <span><?php echo FileInfo::getChmodPermission($fileInfo->getFilePath()); ?></span>
                </li>
                <li>
                    <?php if ($chownOwner == null) { ?>
                        <span><?php echo lng('file_info.chown_unknown'); ?></span>
                    <?php } else { ?>
                        <span><?php echo $chownOwner; ?></span>
                    <?php } ?>
                </li>
                <li>
                    <?php if ($chownGroup == null) { ?>
                        <span><?php echo lng('file_info.chown_unknown'); ?></span>
                    <?php } else { ?>
                        <span><?php echo $chownGroup; ?></span>
                    <?php } ?>
                </li>
                <li>
                    <span><?php echo date('H:i - d.m.Y', filemtime($fileInfo->getFilePath())); ?></span>
                </li>
            </ul>
        </li>
    </ul>

    <ul class="menu-action">
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