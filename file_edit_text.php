<?php

    use Librarys\File\FileInfo;
    use Librarys\File\FileMime;
    use Librarys\App\AppDirectory;
    use Librarys\App\AppLocationPath;
    use Librarys\App\AppParameter;
    use Librarys\App\AppPaging;

    define('LOADED',              1);
    define('PARAMETER_PAGE_EDIT', 'page_edit_text');

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

    if ($fileMime->isFormatTextEdit() == false) {
        $appParameter->remove(AppDirectory::PARAMETER_NAME_URL);
        $appAlert->danger(lng('file_edit_text.alert.file_is_not_format_text_edit'), ALERT_INDEX, $appParameter->toString(true));
    }

    if ($fileMime->isFormatTextAsEdit() == false)
        $title = lng('file_edit_text.title_page');
    else
        $title = lng('file_edit_text.title_page_as');

    $themes = [ env('resource.filename.theme.file') ];
    $appAlert->setID(ALERT_FILE_EDIT_TEXT);
    require_once('incfiles' . SP . 'header.php');

    $edits = [
        'page' => [
            'total'      => 0,
            'current'    => 0,
            'begin_loop' => 0,
            'end_loop'   => 0,
            'max'        => $appConfig->get('paging.file_edit_text')
        ],

        'path'    => FileInfo::filterPaths($appDirectory->getDirectoryAndName()),
        'content' => null,

        'content_lines'      => null,
        'content_line_count' => 0
    ];

    if (isset($_GET[PARAMETER_PAGE_EDIT]) && empty($_GET[PARAMETER_PAGE_EDIT]) == false)
        $edits['page']['current'] = intval(addslashes($_GET[PARAMETER_PAGE_EDIT]));

    if ($edits['page']['current'] <= 0)
        $edits['page']['current'] = 1;

    $edits['page']['begin_loop'] = ($edits['page']['current'] * $edits['page']['max']) - $edits['page']['max'];
    $edits['page']['end_loop']   = 0;

    if ($edits['path'] != null)
        $edits['content'] = FileInfo::fileReadContents($edits['path']);

    if (isset($_POST['save'])) {
        if ($edits['page']['max'] > 0) {
            $edits['content'] = str_replace("\r\n", "\n", $edits['content']);
            $edits['content'] = str_replace("\n",   "\n", $edits['content']);

            if (strpos($edits['content'], "\n") !== false) {
                $edits['content_lines']      = explode("\n", $edits['content']);
                $edits['content_line_count'] = count($edits['content_lines']);
                $edits['content']            = null;

                if ($edits['page']['begin_loop'] + $edits['page']['max'] <= $edits['content_line_count'])
                    $edits['page']['end_loop'] = $edits['page']['begin_loop'] + $edits['page']['max'];
                else
                    $edits['page']['end_loop'] = $edits['content_line_count'];

                if ($edits['page']['begin_loop'] > 0) {
                    for ($begin = 0; $begin < $edits['page']['begin_loop']; ++$begin)
                        $edits['content'] .= $edits['content_lines'][$begin] . "\n";
                }

                $edits['content'] .= str_replace("\r", "\n",
                    str_replace("\r\n", "\n", $_POST['content'])
                );

                if ($edits['page']['current'] < ceil($edits['content_line_count'] / $edits['page']['max'])) {
                    for ($end = $edits['page']['end_loop']; $end < $edits['content_line_count']; ++$end)
                        $edits['content'] .= "\n" . $edits['content_lines'][$end];
                }
            } else {
                $edits['content'] = $_POST['content'];
            }
        } else {
            $edits['content'] = $_POST['content'];

            $edits['content'] = str_replace("\r\n", "\n", $edits['content']);
            $edits['content'] = str_replace("\r",   "\n", $edits['content']);
        }

        if (FileInfo::fileWriteContents($edits['path'], $edits['content']) !== false)
            $appAlert->success(lng('file_edit_text.alert.save_text_success'));
        else
            $appAlert->danger(lng('file_edit_text.alert.save_text_failed'));
    }

    if ($edits['content'] != null && empty($edits['content']) == false && strlen($edits['content']) > 0) {
        $edits['content'] = str_replace("\r\n", "\n", $edits['content']);
        $edits['content'] = str_replace("\n",   "\n", $edits['content']);

        if ($edits['page']['max'] > 0 && strpos($edits['content'], "\n") !== false) {
            $edits['content_lines']      = explode("\n", $edits['content']);
            $edits['content_line_count'] = count($edits['content_lines']);

            if ($edits['content_line_count'] > $edits['page']['max']) {
                $edits['content']       = null;
                $edits['page']['total'] = ceil($edits['content_line_count'] / $edits['page']['max']);

                if ($edits['page']['begin_loop'] + $edits['page']['max'] <= $edits['content_line_count'])
                    $edits['page']['end_loop'] = $edits['page']['begin_loop'] + $edits['page']['max'];
                else
                    $edits['page']['end_loop'] = $edits['content_line_count'];

                for ($i = $edits['page']['begin_loop']; $i < $edits['page']['end_loop']; ++$i) {
                    if ($i >= $edits['page']['end_loop'] - 1)
                        $edits['content'] .= $edits['content_lines'][$i];
                    else
                        $edits['content'] .= $edits['content_lines'][$i] . "\n";
                }
            }
        }
    }

    $appPaging = new AppPaging(
        'file_edit_text.php' . $appParameter->toString(),
        'file_edit_text.php' . $appParameter->toString() . '&' . PARAMETER_PAGE_EDIT . '='
    );
?>

    <?php $appAlert->display(); ?>
    <?php $appLocationPath->display(); ?>

    <div class="form-action">
        <div class="title">
            <?php if ($fileMime->isFormatTextAsEdit() == false) { ?>
                <span><?php echo lng('file_edit_text.title_page'); ?>: <?php echo $appDirectory->getName(); ?></span>
            <?php } else { ?>
                <span><?php echo lng('file_edit_text.title_page_as'); ?>: <?php echo $appDirectory->getName(); ?></span>
            <?php } ?>
        </div>
        <?php $appParameter->add(PARAMETER_PAGE_EDIT, $edits['page']['current'], $edits['page']['current'] > 1); ?>
        <form action="file_edit_text.php<?php echo $appParameter->toString(true); ?>" method="post" id="form-file-edit-javascript">
            <input type="hidden" name="<?php echo $boot->getCFSRToken()->getName(); ?>" value="<?php echo $boot->getCFSRToken()->getToken(); ?>"/>

            <ul class="form-element">
                <li class="textarea">
                    <span><?php echo lng('file_edit_text.form.input.content_file'); ?></span>
                    <textarea name="content" rows="20"><?php echo htmlspecialchars($edits['content']); ?></textarea>
                </li>
                <?php if ($edits['page']['max'] > 0 && $edits['page']['total'] > 1) { ?>
                    <li class="paging">
                        <?php echo $appPaging->display($edits['page']['current'], $edits['page']['total']); ?>
                    </li>
                <?php } ?>
                <li class="button">
                    <button type="submit" name="save" id="button-save-on-javascript">
                        <span><?php echo lng('file_edit_text.form.button.save'); ?></span>
                    </button>
                    <a href="index.php<?php echo $appParameter->toString(); ?>">
                        <span><?php echo lng('file_edit_text.form.button.cancel'); ?></span>
                    </a>
                </li>
            </ul>
        </form>
        <?php $appParameter->remove(PARAMETER_PAGE_EDIT); ?>
        <?php $appParameter->toString(true); ?>
    </div>

    <ul class="menu-action">
        <li>
            <a href="file_info.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-about"></span>
                <span><?php echo lng('file_info.menu_action.info'); ?></span>
            </a>
        </li>
        <?php if ($fileMime->isFormatTextEdit() && $fileMime->isFormatTextAsEdit() == false) { ?>
            <li>
                <a href="file_edit_text_line.php<?php echo $appParameter->toString(); ?>">
                    <span class="icomoon icon-edit"></span>
                    <span><?php echo lng('file_info.menu_action.edit_text_line'); ?></span>
                </a>
            </li>
        <?php } ?>
        <li>
            <a href="file_download.php<?php echo $appParameter->toString(); ?>">
                <span class="icomoon icon-download"></span>
                <span><?php echo lng('file_info.menu_action.download'); ?></span>
            </a>
        </li>
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