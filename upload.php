<?php

    use Librarys\File\FileInfo;
    use Librarys\App\AppDirectory;
    use Librarys\App\AppLocationPath;
    use Librarys\App\AppParameter;

    define('LOADED',               1);
    define('EXISTS_FUNC_OVERRIDE', 1);
    define('EXISTS_FUNC_SKIP',     2);
    define('EXISTS_FUNC_RENAME',   3);

    set_time_limit(0);
    require_once('incfiles' . DIRECTORY_SEPARATOR . 'global.php');

    $title   = lng('upload.title_page');
    $themes  = [ env('resource.filename.theme.file') ];
    $scripts = [ env('resource.filename.javascript.custom_input_file') ];
    $appAlert->setID(ALERT_UPLOAD);
    require_once('incfiles' . SP . 'header.php');

    if ($appDirectory->isDirectoryExists() == false)
        $appAlert->danger(lng('home.alert.path_not_exists'), ALERT_INDEX, env('app.http.host'));
    else if ($appDirectory->isPermissionDenyPath())
        $appAlert->danger(lng('home.alert.path_not_permission', 'path', $appDirectory->getDirectory()), ALERT_INDEX, env('app.http.host'));

    $appLocationPath = new AppLocationPath($appDirectory, 'upload.php');
    $appLocationPath->setIsPrintLastEntry(true);

    $appParameter = new AppParameter();
    $appParameter->add(AppDirectory::PARAMETER_DIRECTORY_URL, $appDirectory->getDirectoryEncode(), true);
    $appParameter->add(AppDirectory::PARAMETER_PAGE_URL,      $appDirectory->getPage(),            $appDirectory->getPage() > 1);

    $forms = [
        'files'       => null,
        'is_empty'    => true,
        'files_count' => 0,
        'exists_func' => EXISTS_FUNC_OVERRIDE
    ];

    if (isset($_POST['upload'])) {
        $forms['exists_func'] = intval($_POST['exists_func']);

        if (isset($_FILES['files']) == false || isset($_FILES['files']['name']) == false) {
            $appAlert->danger(lng('upload.alert.data_empty_or_not_validate'));
        } else if ($forms['exists_func'] !== EXISTS_FUNC_OVERRIDE && $forms['exists_func'] !== EXISTS_FUNC_SKIP && $forms['exists_func'] !== EXISTS_FUNC_RENAME) {
            $appAlert->danger(lng('upload.alert.exists_func_not_validate'));
        } else {
            $forms['is_empty']    = true;
            $forms['files_count'] = count($_FILES['files']['name']);

            foreach ($_FILES['files']['name'] AS $index => $filename) {
                if (empty($filename) == false) {
                    $forms['is_empty'] = false;

                    $forms['files'][] = [
                        'name'     => $_FILES['files']['name'][$index],
                        'type'     => $_FILES['files']['type'][$index],
                        'size'     => $_FILES['files']['size'][$index],
                        'error'    => $_FILES['files']['error'][$index],
                        'tmp_name' => $_FILES['files']['tmp_name'][$index]
                    ];
                }
            }

            if ($forms['is_empty']) {
                $appAlert->danger(lng('upload.alert.not_choose_file'));
            } else {
                foreach ($forms['files'] AS $index => $file) {
                    if ($file['error'] == UPLOAD_ERR_INI_SIZE) {
                        $appAlert->danger(lng('upload.alert.file_error_max_size', 'filemame', $file['name']));
                    } else {
                        $path        = FileInfo::filterPaths($appDirectory->getDirectory() . SP . $file['name']);
                        $isDirectory = FileInfo::isTypeDirectory($path);
                        $isFile      = FileInfo::isTypeFile($path);
                        $fileSizeStr = FileInfo::sizeToString($file['size']);

                        if ($isDirectory && $forms['exists_func'] === EXISTS_FUNC_OVERRIDE) {
                            $appAlert->danger(lng('upload.alert.path_file_error_is_directory', 'filename', $file['name']));
                        } else if ($isFile && $forms['exists_func'] === EXISTS_FUNC_SKIP) {
                            $appAlert->info(lng('upload.alert.path_file_is_exists_and_skip', 'filename', $file['name']));
                        } else if ($isFile && $forms['exists_func'] === EXISTS_FUNC_OVERRIDE) {
                            if (FileInfo::unlink($path)) {

                                if (FileInfo::copy($file['tmp_name'], $path))
                                    $appAlert->success(lng('upload.alert.upload_file_exists_override_is_success', 'filename', $file['name'], 'size', $fileSizeStr));
                                else
                                    $appAlert->danger(lng('upload.alert.upload_file_exists_override_is_failed', 'filename', $file['name']));
                            } else {
                                $appAlert->danger(lng('upload.alert.error_delete_file_exists', 'filename', $file['name']));
                            }
                        } else if ($isFile && $forms['exists_func'] === EXISTS_FUNC_RENAME) {
                            $fileRename = null;
                            $pathRename = null;

                            for ($i = 0; $i < 50; ++$i) {
                                $fileRename = rand(10000, 99999) . '_' . $file['name'];
                                $pathRename = FileInfo::filterPaths($appDirectory->getDirectory() . SP . $fileRename);

                                if (FileInfo::fileExists($pathRename) == false) {
                                    break;
                                } else {
                                    $fileRename = null;
                                    $pathRename = null;
                                }
                            }

                            if ($fileRename == null || $pathRename == null)
                                $appAlert->danger(lng('upload.alert.create_new_filename_exists_rename_is_failed', 'filename', $file['name']));
                            else if (FileInfo::copy($file['tmp_name'], $pathRename))
                                $appAlert->success(lng('upload.alert.upload_file_exists_rename_is_success', 'filename', $fileRename, 'size', $fileSizeStr));
                            else
                                $appAlert->danger(lng('upload.alert.upload_file_exists_rename_is_failed', 'filename', $fileRename));
                        } else if ($isFile || FileInfo::copy($file['tmp_name'], $path) == false) {
                            $appAlert->danger(lng('upload.alert.upload_file_is_failed', 'filename', $file['name']));
                        } else {
                            $appAlert->success(lng('upload.alert.upload_file_is_success', 'filename', $file['name'], 'size', $fileSizeStr));
                        }
                    }
                }
            }
        }
    }

    if ($forms['files_count'] <= 0)
        $forms['files_count']++;
?>

    <?php $appAlert->display(); ?>
    <?php $appLocationPath->display(); ?>

    <div class="form-action">
        <div class="title">
            <span><?php echo lng('upload.title_page'); ?></span>
        </div>
        <form action="upload.php<?php echo $appParameter->toString(); ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="<?php echo $boot->getCFSRToken()->getName(); ?>" value="<?php echo $boot->getCFSRToken()->getToken(); ?>"/>

            <ul class="form-element">
                <?php for ($i = 0; $i < $forms['files_count']; ++$i) { ?>
                    <li class="input-file"<?php if ($i === $forms['files_count'] - 1) { ?> id="template-input-file"<?php } ?> name="file_<?php echo $i; ?>">
                        <input type="file" name="files[]" id="file_<?php echo $i; ?>"/>
                        <label for="file_<?php echo $i; ?>">
                            <span lng="<?php echo lng('upload.form.input.choose_file'); ?>"><?php echo lng('upload.form.input.choose_file'); ?></span>
                        </label>
                    </li>
                <?php } ?>

                <li class="radio-choose">
                    <ul class="radio-choose-tab">
                        <li>
                            <input type="radio" name="exists_func" value="<?php echo EXISTS_FUNC_OVERRIDE; ?>" id="exists_func_override"<?php if ($forms['exists_func'] === EXISTS_FUNC_OVERRIDE) { ?> checked="checked"<?php } ?>/>
                            <label for="exists_func_override">
                                <span><?php echo lng('upload.form.input.exists_func_override'); ?></span>
                            </label>
                        </li>
                        <li>
                            <input type="radio" name="exists_func" value="<?php echo EXISTS_FUNC_SKIP; ?>" id="exists_func_skip"<?php if ($forms['exists_func'] === EXISTS_FUNC_SKIP) { ?> checked="checked"<?php } ?>/>
                            <label for="exists_func_skip">
                                <span><?php echo lng('upload.form.input.exists_func_skip'); ?></span>
                            </label>
                        </li>
                        <li>
                            <input type="radio" name="exists_func" value="<?php echo EXISTS_FUNC_RENAME; ?>" id="exists_func_rename"<?php if ($forms['exists_func'] == EXISTS_FUNC_RENAME) { ?> checked="checked"<?php } ?>/>
                            <label for="exists_func_rename">
                                <span><?php echo lng('upload.form.input.exists_func_rename'); ?></span>
                            </label>
                        </li>
                    </ul>
                </li>

                <li class="button">
                    <button type="button" onclick="javascript:CustomInputFile.onAddMoreInputFile('template-input-file', 'file_', '<?php echo lng('upload.form.input.choose_file'); ?>');">
                        <span><?php echo lng('upload.form.button.more'); ?></span>
                    </button>
                    <button type="submit" name="upload" id="button-save-on-javascript">
                        <span><?php echo lng('upload.form.button.upload'); ?></span>
                    </button>
                    <a href="index.php<?php echo $appParameter->toString(); ?>">
                        <span><?php echo lng('upload.form.button.cancel'); ?></span>
                    </a>
                </li>
            </ul>
        </form>
    </div>

    <ul class="menu-action">
        <li>
            <a href="create.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-folder-create"></span>
                <span><?php echo lng('home.menu_action.create'); ?></span>
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
