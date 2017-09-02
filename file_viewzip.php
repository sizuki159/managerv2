<?php

    use Librarys\File\FileInfo;
    use Librarys\File\FileMime;
    use Librarys\App\AppDirectory;
    use Librarys\App\AppLocationPath;
    use Librarys\App\AppParameter;
    use Librarys\App\AppPaging;
    use Librarys\Zip\PclZip;

    define('LOADED',             1);
    define('PARAMETER_ZIP_PATH', 'directory_zip');
    define('PARAMETER_ZIP_PAGE', 'page_zip');

    require_once('incfiles' . DIRECTORY_SEPARATOR . 'global.php');

    if ($appDirectory->isFileSeparatorNameExists() == false)
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

    if ($isDirectory) {
        $appParameter->remove(AppDirectory::PARAMETER_NAME_URL);
        $appAlert->danger(lng('home.alert.path_not_exists'), ALERT_INDEX, $appParameter->toString(true));
    }

    if ($fileMime->isFormatArchiveZip() == false) {
        $appParameter->remove(AppDirectory::PARAMETER_NAME_URL);
        $appAlert->danger(lng('file_viewzip.alert.file_is_not_format_archive_zip'), ALERT_INDEX, $appParameter->toString(true));
    }

    $title   = lng('file_viewzip.title_page');
    $themes  = [ env('resource.filename.theme.file') ];
    $appAlert->setID(ALERT_FILE_VIEWZIP);
    require_once('incfiles' . SP . 'header.php');


    $pclZip = new PclZip(FileInfo::filterPaths($appDirectory->getDirectoryAndName()));
    $pclZipListContent = $pclZip->listContent();
    $pclZipSeparator   = '/';

    $pclZipDirectoryOrigin = null;
    $pclZipDirectory       = null;
    $pclZipArrayEntrys     = null;
    $pclZipCountArrayEntry = 0;

    if (isset($_GET[PARAMETER_ZIP_PATH]) && empty($_GET[PARAMETER_ZIP_PATH]) == false) {
        $pclZipDirectoryOrigin = separator(FileInfo::filterPaths(AppDirectory::rawDecode($_GET[PARAMETER_ZIP_PATH])), $pclZipSeparator);
        $pclZipDirectory       = separator($pclZipDirectoryOrigin . $pclZipSeparator, $pclZipSeparator);
    }

    if ($pclZipListContent !== false && is_array($pclZipListContent)) {
        $pclZipArrayFolders = [];
        $pclZipArrayFiles   = [];

        foreach ($pclZipListContent AS $pclZipEntry) {
            $pclZipFileName = $pclZipEntry['filename'];
            $pclZipFileSize = $pclZipEntry['size'];

            if (strpos($pclZipFileName, $pclZipSeparator) === false && $pclZipDirectory != null) {
                $pclZipArrayFiles[$pclZipFileName] = [
                    'entry_path'   => $pclZipFileName,
                    'entry_name'   => $pclZipFileName,
                    'entry_is_dir' => false,
                    'entry_size'   => $pclZipFileSize
                ];
            } else if (preg_match('#(' . $pclZipDirectory . '(.+?))(' . $pclZipSeparator . '|$)+#', $pclZipFileName, $entryMatches)) {
                if ($entryMatches[3] == $pclZipSeparator && isset($pclZipArrayFolders[$entryMatches[2]]) == false) {
                    $pclZipArrayFolders[$entryMatches[2]] = [
                        'entry_path'   => $entryMatches[1],
                        'entry_name'   => $entryMatches[2],
                        'entry_is_dir' => true,
                        'entry_size'   => 0
                    ];
                } else if ($entryMatches[3] != $pclZipSeparator && $pclZipEntry['folder'] == false) {
                    $pclZipArrayFiles[$entryMatches[2]] = [
                        'entry_path'   => $entryMatches[1],
                        'entry_name'   => $entryMatches[2],
                        'entry_is_dir' => false,
                        'entry_size'   => $pclZipFileSize
                    ];
                }
            }
        }

        $pclZipArrayEntrys      = array();
        $pclZipCountArrayFolder = count($pclZipArrayFolders);
        $pclZipCountArrayFile   = count($pclZipArrayFiles);

        if ($pclZipCountArrayFolder > 0) {
            ksort($pclZipArrayFolders);

            foreach ($pclZipArrayFolders AS $entry)
                $pclZipArrayEntrys[] = $entry;
        }

        if ($pclZipCountArrayFile > 0) {
            ksort($pclZipArrayFiles);

            foreach ($pclZipArrayFiles AS $entry)
                $pclZipArrayEntrys[] = $entry;
        }

        array_splice($pclZipArrayFolders, 0, $pclZipCountArrayFolder);
        array_splice($pclZipArrayFiles,   0, $pclZipCountArrayFile);

        $pclZipCountArrayEntry = count($pclZipArrayEntrys);
    } else {
        $pclZipArrayEntrys     = null;
        $pclZipCountArrayEntry = 0;
    }

    if ($pclZipDirectoryOrigin != null) {
        $pclZipLocationPath = explode($pclZipSeparator, $pclZipDirectoryOrigin);
        $appLocationPath->addEntry($appDirectory->getName(), null);

        if (is_array($pclZipLocationPath) && count($pclZipLocationPath) > 0)
            foreach ($pclZipLocationPath AS $pclZipLocation)
                $appLocationPath->addEntry($pclZipLocation, null);
    }

    $pclzipPage = [
        'total'      => 0,
        'current'    => 0,
        'begin_loop' => 0,
        'end_loop'   => 0,
        'max'        => $appConfig->get('paging.file_view_zip')
    ];

    if (isset($_GET[PARAMETER_ZIP_PAGE]) && empty($_GET[PARAMETER_ZIP_PAGE]) == false)
        $pclzipPage['current'] = intval(addslashes($_GET[PARAMETER_ZIP_PAGE]));

    if ($pclzipPage['current'] <= 0)
        $pclzipPage['current'] = 1;

    if ($pclZipCountArrayEntry > 0 && $pclzipPage['max'] > 0) {
        $pclzipPage['total']      = ceil($pclZipCountArrayEntry / $pclzipPage['max']);
        $pclzipPage['begin_loop'] = ($pclzipPage['current'] * $pclzipPage['max']) - $pclzipPage['max'];
        $pclzipPage['end_loop']   = 0;

        if ($pclzipPage['begin_loop'] + $pclzipPage['max'] <= $pclZipCountArrayEntry)
            $pclzipPage['end_loop'] =  $pclzipPage['begin_loop'] + $pclzipPage['max'];
        else
            $pclzipPage['end_loop'] = $pclZipCountArrayEntry;
    } else {
        $pclzipPage['total'] = 1;
    }

    $appParameter->add(PARAMETER_ZIP_PATH, $pclZipDirectoryOrigin, $pclZipDirectoryOrigin != null);
    $appParameter->toString(true);

    $appPaging = new AppPaging(
        'file_viewzip.php' . $appParameter->toString(),
        'file_viewzip.php' . $appParameter->toString() . '&' . PARAMETER_ZIP_PAGE . '='
    );

    $appParameter->remove(PARAMETER_ZIP_PATH);
    $appParameter->toString(true);

    $bufferBack = null;

    if ($pclZipDirectoryOrigin != null && preg_replace('|[a-zA-Z]+:|', '', separator($pclZipDirectoryOrigin, $pclZipSeparator)) != $pclZipSeparator) {
        $backPath      = strrchr($pclZipDirectoryOrigin, $pclZipSeparator);
        $backDirectory = $backPath;

        if ($backPath !== false) {
            $backPath = substr($pclZipDirectoryOrigin, 0, strlen($pclZipDirectoryOrigin) - strlen($backPath));
            $backPath = 'file_viewzip.php' . $appParameter->toString() . '&' . PARAMETER_ZIP_PATH . '=' . AppDirectory::rawEncode($backPath);

            if (strpos($backDirectory, $pclZipSeparator) !== false)
                $backDirectory = str_replace($pclZipSeparator, null, $backDirectory);
        } else {
            $backPath      = 'file_viewzip.php' . $appParameter->toString();
            $backDirectory = $pclZipDirectoryOrigin;
        }

        $bufferBack .= '<li class="back">';
            $bufferBack .= '<a href="' . $backPath . '">';
                $bufferBack .= '<span class="icomoon icon-folder-open"></span>';
                $bufferBack .= '<strong>' . $backDirectory . '</strong>';
            $bufferBack .= '</a>';
        $bufferBack .= '</li>';
    }
?>

    <?php $appAlert->display(); ?>
    <?php $appLocationPath->display(); ?>

    <ul class="file-list">
        <?php if ($pclZipArrayEntrys == null || $pclZipCountArrayEntry <= 0) { ?>
            <li class="empty">
                <span class="icomoon icon-folder-o"></span>
                <span><?php echo lng('home.directory_empty'); ?></span>
            </li>
        <?php } else { ?>
            <?php echo $bufferBack; ?>

            <?php for ($i = $pclzipPage['begin_loop']; $i < $pclzipPage['end_loop']; ++$i) { ?>
                <?php $pclZipEntry = $pclZipArrayEntrys[$i]; ?>

                <?php if ($pclZipEntry['entry_is_dir']) { ?>
                    <li class="type-directory">
                        <div class="icon">
                            <a href="#">
                                <span class="icomoon icon-folder"></span>
                            </a>
                        </div>
                        <a href="file_viewzip.php<?php echo $appParameter->toString(); ?>&<?php echo PARAMETER_ZIP_PATH; ?>=<?php echo AppDirectory::rawEncode($pclZipEntry['entry_path']); ?>" class="file-name">
                            <span><?php echo $pclZipEntry['entry_name']; ?></span>
                        </a>
                    </li>
                <?php } else { ?>
                    <?php $info = new FileInfo($pclZipEntry['entry_path'], false); ?>
                    <?php $mime = new FileMime($info); ?>
                    <?php $icon = null; ?>

                    <?php
                        if ($mime->isFormatText())
                            $icon   = 'icon-file-text';
                        else if ($mime->isFormatCode())
                            $icon   = 'icon-file-code';
                        else if ($mime->isFormatArchive())
                            $icon   = 'icon-file-archive';
                        else if ($mime->isFormatAudio())
                            $icon   = 'icon-file-audio';
                        else if ($mime->isFormatVideo())
                            $icon   = 'icon-file-video';
                        else if ($mime->isFormatDocument())
                            $icon   = 'icon-file-document';
                        else if ($mime->isFormatImage())
                            $icon   = 'icon-file-image';
                        else if ($mime->isFormatSource())
                            $icon   = 'icon-file-code';
                        else
                            $icon   = 'icon-file';
                    ?>

                    <li class="type-file">
                        <div class="icon">
                            <span class="icomoon <?php echo $icon; ?>"></span>
                        </div>
                        <a href="" class="file-name">
                            <span><?php echo $pclZipEntry['entry_name']; ?></span>
                        </a>
                        <div class="chmod-size">
                            <span class="size"><?php echo FileInfo::sizeToString($pclZipEntry['entry_size']); ?></span>
                        </div>
                    </li>
                <?php } ?>
            <?php } ?>
            <?php if ($pclzipPage['total'] > 1 && $pclzipPage['max'] > 0) { ?>
                <li class="paging">
                    <?php echo $appPaging->display($pclzipPage['current'], $pclzipPage['total']); ?>
                </li>
            <?php } ?>
        <?php } ?>
    </ul>

    <ul class="menu-action">
        <li>
            <a href="file_info.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-about"></span>
                <span><?php echo lng('file_info.menu_action.info'); ?></span>
            </a>
        </li>
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

<?php

/*
define('ACCESS', true);

    include_once 'function.php';

    if (IS_LOGIN) {
        $title = 'Xem tập tin nén';
        $format = getFormat($name);

        if ($dir == null || $name == null || !is_file(processDirectory($dir . '/' . $name))) {
            include_once 'header.php';

            echo '<div class="title">' . $title . '</div>
            <div class="list"><span>Đường dẫn không tồn tại</span></div>
            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/list.png"/> <a href="index.php' . $pages['paramater_0'] . '">Danh sách</a></li>
            </ul>';
        } else if (!in_array($format, array('zip', 'jar'))) {
            include_once 'header.php';

            echo '<div class="title">' . $title . '</div>
            <div class="list"><span>Tập tin không phải zip</span></div>
            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/list.png"/> <a href="index.php?dir=' . $dirEncode . $pages['paramater_1'] . '">Danh sách</a></li>
            </ul>';
        } else {
            $title .= ':' . $name;

            include_once 'header.php';
            include_once 'pclzip.class.php';

            $path = isset($_GET['path']) && !empty($_GET['path']) ? processPathZip($_GET['path']) : null;
            $dir = processDirectory($dir);
            $format = getFormat($name);
            $zip = new PclZIP($dir . '/' . $name);
            $lists = $zip->listContent();
            $arrays = array('folders' => array(), 'files' => array());

            if (!$lists) {
                echo '<div class="title">' . $title . '</div>
                <div class="list">
                    <span>' . printPath($dir . '/' . $name) . '</span><hr/>
                    <span>Tập tin nén bị lỗi không mở được</span>
                </div>';
            } else {
                $base = $path == null || empty($path) ? null : $path . '/';

                foreach ($lists AS $entry) {
                    $filename = $entry['filename'];

                    if (strpos($filename, '/') === false && $base == null) {
                        $arrays['files'][$filename] = array('path' => $filename, 'name' => $filename, 'folder' => false, 'size' => $entry['size']);
                    } else if (preg_match('#(' . $base . '(.+?))(/|$)+#', $filename, $matches)) {
                        if ($matches[3] == '/' && !isset($arrays['folders'][$matches[2]]))
                            $arrays['folders'][$matches[2]] = array('path' => $matches[1], 'name' => $matches[2], 'folder' => true);
                        else if ($matches[3] != '/' && !$entry['folder'])
                            $arrays['files'][$matches[2]] = array('path' => $matches[1], 'name' => $matches[2], 'folder' => false, 'size' => $entry['size']);
                    }
                }

                $sorts = array();

                if (count($arrays['folders']) > 0) {
                    ksort($arrays['folders']);

                    foreach ($arrays['folders'] AS $entry)
                        $sorts[] = $entry;
                }

                if (count($arrays['files']) > 0) {
                    ksort($arrays['files']);

                    foreach ($arrays['files'] AS $entry)
                        $sorts[] = $entry;
                }

                array_splice($arrays, 0, count($arrays));

                $arrays = $sorts;
                $count = count($arrays);
                $root = 'root';
                $html = null;

                array_splice($sorts, 0, count($sorts));
                unset($sorts);

                if ($path != null && strpos($path, '/') !== false) {
                    $array = explode('/', preg_replace('|^/(.*?)$|', '\1', $path));
                    $html = '/<a href="file_viewzip.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">' . $root . '</a>';
                    $item = null;
                    $url = null;

                    foreach ($array AS $key => $entry) {
                        if ($key === 0) {
                            $seperator = preg_match('|^\/(.*?)$|', $path) ? '/' : null;
                            $item = $seperator . $entry;
                        } else {
                            $item = '/' . $entry;
                        }

                        if ($key < count($array) - 1)
                            $html .= '/<a href="file_viewzip.php?dir=' . $dirEncode . '&name=' . $name . '&path=' . rawurlencode($url . $item) . $pages['paramater_1'] . '">';
                        else
                            $html .= '/';

                        $url .= $item;

                        if (strlen($entry) <= 8)
                            $html .= $entry;
                        else
                            $html .= substr($entry, 0, 8) . '...';

                        if ($key < count($array) - 1)
                            $html .= '</a>';
                    }
                } else {
                    if ($path == null)
                        $html = '/' . $root;
                    else
                        $html = '/<a href="file_viewzip.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">' . $root . '</a>/' . $path;
                }

                echo '<script language="javascript" src="checkbox.js"></script>';
                echo '<div class="title">' . $html . '</div>';
                echo '<ul class="list_file">';
                echo '<li class="normal">
                    <span>' . printPath($dir . '/' . $name) . '</span>
                </li>';

                if ($path != null) {
                    $back = strrchr($path, '/');

                    if ($back !== false)
                        $back = 'file_viewzip.php?dir=' . $dirEncode . '&name=' . $name . '&path=' . rawurlencode(substr($path, 0, strlen($path) - strlen($back))) . $pages['paramater_1'];
                    else
                        $back = 'file_viewzip.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'];

                    echo '<li class="normal">
                        <img src="icon/back.png" style="margin-left: 5px; margin-right: 5px"/>
                        <a href="' . $back . '">
                            <strong class="back">...</strong>
                        </a>
                    </li>';
                }

                if ($count <= 0) {
                    echo '<li class="normal"><img src="icon/empty.png"/> <span class="empty">Không có thư mục hoặc tập tin</span></li>';
                } else {
                    foreach ($arrays AS $key => $value) {
                        $pathEncode = rawurlencode($value['path']);

                        if ($value['folder']) {
                            echo '<li class="folder">
                                <div>
                                    <img src="icon/folder.png" style="margin-left: 5px"/>
                                    <a href="file_viewzip.php?dir=' . $dirEncode . '&name=' . $name . '&path=' . $pathEncode . $pages['paramater_1'] . '">' . $value['name'] . '</a>
                                </div>
                            </li>';
                        } else {
                            $icon = 'unknown';
                            $type = getFormat($value['name']);

                            if (in_array($type, $formats['other']))
                                $icon = $type;
                            else if (in_array($type, $formats['text']))
                                $icon = $type;
                            else if (in_array($type, $formats['archive']))
                                $icon = $type;
                            else if (in_array($type, $formats['audio']))
                                $icon = $type;
                            else if (in_array($type, $formats['font']))
                                $icon = $type;
                            else if (in_array($type, $formats['binary']))
                                $icon = $type;
                            else if (in_array($type, $formats['document']))
                                $icon = $type;
                            else if (in_array($type, $formats['image']))
                                $icon = 'image';
                            else if (in_array(strtolower(strpos($name, '.') !== false ? substr($name, 0, strpos($name, '.')) : $name), $formats['source']))
                                $icon = strtolower(strpos($name, '.') !== false ? substr($name, 0, strpos($name, '.')) : $name);

                            echo '<li class="file">
                                <p>
                                    <img src="icon/mime/' . $icon . '.png" style="margin-left: 5px"/>
                                    <span>' . $value['name'] . '</span>
                                </p>
                                <p>
                                    <span class="size">' . size($value['size']) . '</span>
                                </p>
                            </li>';
                        }
                    }
                }

                echo '</ul>';
            }

            echo '<div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/info.png"/> <a href="file.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Thông tin</a></li>
                <li><img src="icon/unzip.png"/> <a href="file_unzip.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Giải nén</a></li>
                <li><img src="icon/download.png"/> <a href="file_download.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Tải về</a></li>
                <li><img src="icon/rename.png"/> <a href="file_rename.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Đổi tên</a></li>
                <li><img src="icon/copy.png"/> <a href="file_copy.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Sao chép</a></li>
                <li><img src="icon/move.png"/> <a href="file_move.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Di chuyển</a></li>
                <li><img src="icon/delete.png"/> <a href="file_delete.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Xóa</a></li>
                <li><img src="icon/access.png"/> <a href="file_chmod.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Chmod</a></li>
                <li><img src="icon/list.png"/> <a href="index.php?dir=' . $dirEncode . $pages['paramater_1'] . '">Danh sách</a></li>
            </ul>';
        }

        include_once 'footer.php';
    } else {
        goURL('login.php');
    }*/

?>