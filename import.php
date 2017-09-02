<?php

    use Librarys\File\FileInfo;
    use Librarys\File\FileCurl;
    use Librarys\App\AppDirectory;
    use Librarys\App\AppLocationPath;
    use Librarys\App\AppParameter;

    define('LOADED',               1);
    define('EXISTS_FUNC_OVERRIDE', 1);
    define('EXISTS_FUNC_SKIP',     2);
    define('EXISTS_FUNC_RENAME',   3);
    define('MODE_IMPORT_CURL',     1);
    define('MODE_IMPORT_SOCKET',   2);

    require_once('incfiles' . DIRECTORY_SEPARATOR . 'global.php');

    $title   = lng('import.title_page');
    $themes  = [ env('resource.filename.theme.file') ];
    $scripts = [ env('resource.filename.javascript.more_input_url') ];
    $appAlert->setID(ALERT_IMPORT);
    require_once('incfiles' . SP . 'header.php');

    if ($appDirectory->isDirectoryExists() == false)
        $appAlert->danger(lng('home.alert.path_not_exists'), ALERT_INDEX, env('app.http.host'));
    else if ($appDirectory->isPermissionDenyPath())
        $appAlert->danger(lng('home.alert.path_not_permission', 'path', $appDirectory->getDirectory()), ALERT_INDEX, env('app.http.host'));

    $appLocationPath = new AppLocationPath($appDirectory, 'import.php');
    $appLocationPath->setIsPrintLastEntry(true);

    $appParameter = new AppParameter();
    $appParameter->add(AppDirectory::PARAMETER_DIRECTORY_URL, $appDirectory->getDirectoryEncode(), true);
    $appParameter->add(AppDirectory::PARAMETER_PAGE_URL,      $appDirectory->getPage(),            $appDirectory->getPage() > 1);

    $forms = [
        'urls'        => null,
        'filenames'   => null,
        'is_empty'    => true,
        'urls_count'  => 0,
        'exists_func' => EXISTS_FUNC_OVERRIDE,
        'mode_import' => FileCurl::isSupportCurl() ? MODE_IMPORT_CURL : MODE_IMPORT_SOCKET
    ];

    if (isset($_POST['import'])) {
        $forms['exists_func'] = intval($_POST['exists_func']);
        $forms['mode_import'] = intval($_POST['mode_import']);

        if (isset($_POST['urls']) == false || is_array($_POST['urls']) == false) {
            $appAlert->danger(lng('import.alert.data_empty_or_not_validate'));
        } else if ($forms['exists_func'] !== EXISTS_FUNC_OVERRIDE && $forms['exists_func'] !== EXISTS_FUNC_SKIP && $forms['exists_func'] !== EXISTS_FUNC_RENAME) {
            $appAlert->danger(lng('import.alert.exists_func_not_validate'));
        } else if ($forms['mode_import'] !== MODE_IMPORT_CURL && $forms['mode_import'] !== MODE_IMPORT_SOCKET) {
            $appAlert->danger(lng('import.alert.mode_import_not_validate'));
        } else {
            $isFailed            = false;
            $forms['is_empty']   = true;
            $forms['urls_count'] = count($_POST['urls']);

            foreach ($_POST['urls'] AS $index => $url) {
                if (empty($url) == false) {
                    $forms['is_empty'] = false;
                    $forms['urls'][$index] = addslashes($_POST['urls'][$index]);

                    if (isValidateURL($forms['urls'][$index]) == false) {
                        $isFailed = true;
                        $appAlert->danger(lng('import.alert.url_import_not_validate', 'url', $url));
                    }
                }

                if (isset($_POST['filenames'][$index]) && empty($_POST['filenames'][$index]) == false) {
                    $forms['filenames'][$index] = addslashes($_POST['filenames'][$index]);

                    if ($isFailed == false && empty($url) == false && empty($forms['filenames'][$index]) == false && FileInfo::isNameValidate($forms['filenames'][$index]) == false)
                        $appAlert->danger(lng('import.alert.name_url_import_not_validate', 'name', $forms['filenames'][$index], 'validate', FileInfo::FILENAME_VALIDATE));
                }
            }

            if ($forms['is_empty']) {
                $appAlert->danger(lng('import.alert.not_input_urls'));
            } else if ($isFailed == false) {
                for ($i = 0; $i < $forms['urls_count']; ++$i) {
                    $url       = null;
                    $filename  = null;
                    $isSuccess = false;

                    if (isset($forms['urls'][$i]))
                        $url = $forms['urls'][$i];

                    if ($url != null) {
                        if (isset($forms['filenames'][$i]))
                            $filename = $forms['filenames'][$i];

                        $curl = new FileCurl($url);
                        $curl->setUseCurl($forms['mode_import'] === MODE_IMPORT_CURL);

                        if ($curl->curl() == false) {
                            $errorInt = $curl->getErrorInt();

                            if ($errorInt === FileCurl::ERROR_URL_NOT_FOUND)
                                $appAlert->danger(lng('import.alert.address_not_found', 'url', $url));
                            else if ($errorInt === FileCurl::ERROR_FILE_NOT_FOUND)
                                $appAlert->danger(lng('import.alert.file_not_found', 'url', $url));
                            else if ($errorInt === FileCurl::ERROR_AUTO_REDIRECT)
                                $appAlert->danger(lng('import.alert.auto_redirect_url_failed', 'url', $url));
                            else if ($errorInt === FileCurl::ERROR_CONNECT_FAILED)
                                $appAlert->danger(lng('import.alert.connect_url_failed', 'url', $url));
                            else
                                $appAlert->danger(lng('import.alert.error_unknown', 'url', $url));
                        } else {
                            if (empty($filename))
                                $filename = baseNameURL($curl->getURL());

                            if (empty($filename))
                                $filename = removePrefixHttpURL($curl->getURL());

                            $filename             = FileInfo::fileNameFix($filename);
                            $fileWritePath        = FileInfo::filterPaths($appDirectory->getDirectory() . SP . $filename);
                            $fileWriteIsDirectory = FileInfo::isTypeDirectory($fileWritePath);
                            $fileWriteIsFile      = FileInfo::isTypeFile($fileWritePath);
                            $fileSizeStr          = FileInfo::sizeToString($curl->getBufferLength());
                            $timeImport           = $curl->getTimeRun();

                            if ($timeImport < 60) {
                                $timeImport = $timeImport . 's';
                            } else if ($timeImport < 3600) {
                                $timeImportMinute = floor($timeImport / 60);
                                $timeImportSecond = $timeImport - ($timeImportMinute * 60);
                                $timeImport       = $timeImportMinute . ':' . $timeImportSecond . 's';
                            } else {
                                $timeImportHour   = floor($timeImport / 3600);
                                $timeImportMinute = floor(($timeImport - ($timeImportHour * 3600)) / 60);
                                $timeImportSecond = $timeImport - (($timeImportHour * 3600) + ($timeImportMinute * 60));
                                $timeImport       = $timeImportHour . ':' . $timeImportMinute . ':' . $timeImportSecond . 's';
                            }

                            if ($fileWriteIsDirectory && $forms['exists_func'] === EXISTS_FUNC_OVERRIDE) {
                                $appAlert->danger(lng('import.alert.path_file_error_is_directory', 'filename', $filename));
                            } else if ($fileWriteIsFile && $forms['exists_func'] === EXISTS_FUNC_SKIP) {
                                $appAlert->info(lng('import.alert.path_file_is_exists_and_skip', 'filename', $filename));
                            } else if ($fileWriteIsFile && $forms['exists_func'] === EXISTS_FUNC_OVERRIDE) {
                                if (FileInfo::unlink($fileWritePath)) {

                                    if (FileInfo::fileWriteContents($fileWritePath, $curl->getBuffer()) == true) {
                                        $appAlert->success(lng('import.alert.import_file_exists_override_is_success', 'filename', $filename, 'size', $fileSizeStr, 'time', $timeImport));
                                        $isSuccess = true;
                                    } else {
                                        $appAlert->danger(lng('import.alert.import_file_exists_override_is_failed', 'filename', $filename));
                                    }
                                } else {
                                    $appAlert->danger(lng('import.alert.error_delete_file_exists', 'filename', $filename));
                                }
                            } else if ($fileWriteIsFile && $forms['exists_func'] === EXISTS_FUNC_RENAME) {
                                $fileRename = null;
                                $pathRename = null;

                                for ($i = 0; $i < 50; ++$i) {
                                    $fileRename = rand(10000, 99999) . '_' . $filename;
                                    $pathRename = FileInfo::filterPaths($appDirectory->getDirectory() . SP . $fileRename);

                                    if (FileInfo::fileExists($pathRename) == false) {
                                        break;
                                    } else {
                                        $fileRename = null;
                                        $pathRename = null;
                                    }
                                }

                                if ($fileRename == null || $pathRename == null) {
                                    $appAlert->danger(lng('import.alert.create_new_filename_exists_rename_is_failed', 'filename', $filename));
                                } else if (FileInfo::fileWriteContents($pathRename, $curl->getBuffer())) {
                                    $appAlert->success(lng('import.alert.import_file_exists_rename_is_success', 'filename', $fileRename, 'size', $fileSizeStr, 'time', $timeImport));
                                    $isSuccess = true;
                                } else {
                                    $appAlert->danger(lng('import.alert.import_file_exists_rename_is_failed', 'filename', $fileRename));
                                }
                            } else if ($fileWriteIsFile || FileInfo::fileWriteContents($fileWritePath, $curl->getBuffer()) == false) {
                                $appAlert->danger(lng('import.alert.import_file_is_failed', 'filename', $filename));
                            } else {
                                $appAlert->success(lng('import.alert.import_file_is_success', 'filename', $filename, 'size', $fileSizeStr, 'time', $timeImport));
                                $isSuccess = true;
                            }
                        }
                    }

                    if ($isSuccess) {
                        $forms['urls'][$i]      = null;
                        $forms['filenames'][$i] = null;
                    }
                }
            }
        }

        $forms['urls']      = stripslashesArray($forms['urls']);
        $forms['filenames'] = stripslashesArray($forms['filenames']);
    }

    if ($forms['urls_count'] <= 0)
        $forms['urls_count'] = 1;
?>

    <?php $appAlert->display(); ?>
    <?php $appLocationPath->display(); ?>

    <div class="form-action">
        <div class="title">
            <span><?php echo lng('import.title_page'); ?></span>
        </div>
        <form action="import.php<?php echo $appParameter->toString(); ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="<?php echo $boot->getCFSRToken()->getName(); ?>" value="<?php echo $boot->getCFSRToken()->getToken(); ?>"/>

            <ul class="form-element">
                <?php for ($i = 0; $i < $forms['urls_count']; ++$i) { ?>
                    <li class="input"<?php if ($i === $forms['urls_count'] - 1) { ?> id="template-input-url"<?php } ?> name="url_<?php echo $i; ?>">
                        <span><?php echo lng('import.form.input.label_import', 'count', $i + 1); ?></span>
                        <input type="text" name="urls[]" value="<?php if ($forms['urls'] != null && isset($forms['urls'][$i])) echo htmlspecialchars($forms['urls'][$i]); ?>" placeholder="<?php echo lng('import.form.placeholder.input_url'); ?>"/>
                        <input type="text" name="filenames[]" value="<?php if ($forms['filenames'] != null && isset($forms['filenames'][$i])) echo htmlspecialchars($forms['filenames'][$i]); ?>" placeholder="<?php echo lng('import.form.placeholder.input_filename'); ?>"/>
                    </li>
                <?php } ?>

                <li class="radio-choose">
                    <span><?php echo lng('import.form.input.label_exists_func'); ?></span>
                    <ul class="radio-choose-tab">
                        <li>
                            <input type="radio" name="exists_func" value="<?php echo EXISTS_FUNC_OVERRIDE; ?>" id="exists_func_override"<?php if ($forms['exists_func'] === EXISTS_FUNC_OVERRIDE) { ?> checked="checked"<?php } ?>/>
                            <label for="exists_func_override">
                                <span><?php echo lng('import.form.input.exists_func_override'); ?></span>
                            </label>
                        </li>
                        <li>
                            <input type="radio" name="exists_func" value="<?php echo EXISTS_FUNC_SKIP; ?>" id="exists_func_skip"<?php if ($forms['exists_func'] === EXISTS_FUNC_SKIP) { ?> checked="checked"<?php } ?>/>
                            <label for="exists_func_skip">
                                <span><?php echo lng('import.form.input.exists_func_skip'); ?></span>
                            </label>
                        </li>
                        <li>
                            <input type="radio" name="exists_func" value="<?php echo EXISTS_FUNC_RENAME; ?>" id="exists_func_rename"<?php if ($forms['exists_func'] == EXISTS_FUNC_RENAME) { ?> checked="checked"<?php } ?>/>
                            <label for="exists_func_rename">
                                <span><?php echo lng('import.form.input.exists_func_rename'); ?></span>
                            </label>
                        </li>
                    </ul>
                </li>

                <li class="radio-choose">
                    <span><?php echo lng('import.form.input.label_mode_import'); ?></span>
                    <ul class="radio-choose-tab">
                        <li>
                            <input type="radio" name="mode_import" value="<?php echo MODE_IMPORT_CURL; ?>" id="mode-import-curl"<?php if ($forms['mode_import'] == MODE_IMPORT_CURL) { ?> checked="checked"<?php } ?><?php if (FileCurl::isSupportCurl() == false) { ?> disabled="disabled"<?php } ?>/>
                            <label for="mode-import-curl">
                                <span><?php echo lng('import.form.input.mode_import_curl'); ?></span>
                            </label>
                        </li>
                        <li>
                            <input type="radio" name="mode_import" value="<?php echo MODE_IMPORT_SOCKET; ?>" id="mode-import-socket"<?php if ($forms['mode_import'] == MODE_IMPORT_SOCKET) { ?> checked="checked"<?php } ?>/>
                            <label for="mode-import-socket">
                                <span><?php echo lng('import.form.input.mode_import_socket'); ?></span>
                            </label>
                        </li>
                    </ul>
                </li>

                <li class="button">
                    <button type="button" onclick="javascript:MoreInputUrl.onAddMoreInputUrl('template-input-url', 'url_', 'label_index');">
                        <span><?php echo lng('import.form.button.more'); ?></span>
                    </button>
                    <button type="submit" name="import" id="button-save-on-javascript">
                        <span><?php echo lng('import.form.button.import'); ?></span>
                    </button>
                    <a href="index.php<?php echo $appParameter->toString(); ?>">
                        <span><?php echo lng('import.form.button.cancel'); ?></span>
                    </a>
                </li>
            </ul>
        </form>
    </div>

    <ul class="alert">
        <li class="info">
            <span><?php echo lng('import.alert.tips'); ?></span>
        </li>
    </ul>

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
    </ul>

<?php require_once('incfiles' . SP . 'footer.php'); ?>

<?php
/* define('ACCESS', true);

    include_once 'function.php';

    if (IS_LOGIN) {
        $title = 'Tải lên tập tin';

        include_once 'header.php';

        echo '<div class="title">' . $title . '</div>';

        if ($dir == null || !is_dir(processDirectory($dir))) {
            echo '<div class="list"><span>Đường dẫn không tồn tại</span></div>
            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/list.png"/> <a href="index.php' . $pages['paramater_0'] . '">Danh sách</a></li>
            </ul>';
        } else {
            $dir = processDirectory($dir);

            if (isset($_POST['submit'])) {
                $isEmpty = true;

                foreach ($_POST['url'] AS $entry) {
                    if (!empty($entry)) {
                        $isEmpty = false;
                        break;
                    }
                }

                if ($isEmpty) {
                    echo '<div class="notice_failure">Chưa nhập url nào cả</div>';
                } else {
                    for ($i = 0; $i < count($_POST['url']); ++$i) {
                        if (!empty($_POST['url'][$i])) {
                            $_POST['url'][$i] = processImport($_POST['url'][$i]);

                            if (!isURL($_POST['url'][$i]))
                                echo '<div class="notice_failure">URL <strong class="url_import">' . $_POST['url'][$i] . '</strong> không hợp lệ</div>';
                            else if (import($_POST['url'][$i], $dir . '/' . basename($_POST['url'][$i])))
                                echo '<div class="notice_succeed">Nhập khẩu tập tin <strong class="file_name_import">' . basename($_POST['url'][$i]) . '</strong>, <span class="file_size_import">' . size(filesize($dir . '/' . basename($_POST['url'][$i]))) . '</span> thành công</div>';
                            else
                                echo '<div class="notice_failure">Nhập khẩu tập tin <strong class="file_name_import">' . basename($_POST['url'][$i]) . '</strong> thất bại</div>';
                        }
                    }
                }
            }

            echo '<div class="list">
                <span>' . printPath($dir, true) . '</span><hr/>
                <form action="import.php?dir=' . $dirEncode . $pages['paramater_1'] . '" method="post">
                    <span class="bull">&bull;</span>URL 1:<br/>
                    <input type="text" name="url[]" size="18"/><br/>
                    <span class="bull">&bull;</span>URL:<br/>
                    <input type="text" name="url[]" size="18"/><br/>
                    <span class="bull">&bull;</span>URL 3:<br/>
                    <input type="text" name="url[]" size="18"/><br/>
                    <span class="bull">&bull;</span>URL 4:<br/>
                    <input type="text" name="url[]" size="18"/><br/>
                    <span class="bull">&bull;</span>URL 5:<br/>
                    <input type="text" name="url[]" size="18"/><br/>
                    <input type="submit" name="submit" value="Nhập khẩu"/>
                </form>
            </div>

            <div class="tips"><img src="icon/tips.png"/> Không có http:// đứng trước cũng được, nếu có https:// phải nhập vào</div>

            <div class="title">Chức năng</div>
            <ul class="list">
                <li><img src="icon/create.png"/> <a href="create.php?dir=' . $dirEncode . $pages['paramater_1'] . '">Tạo mới</a></li>
                <li><img src="icon/upload.png"/> <a href="upload.php?dir=' . $dirEncode . $pages['paramater_1'] . '">Tải lên tập tin</a></li>
                <li><img src="icon/list.png"/> <a href="index.php?dir=' . $dirEncode . $pages['paramater_1'] . '">Danh sách</a></li>
            </ul>';
        }

        include_once 'footer.php';
    } else {
        goURL('login.php');
    }*/

?>
