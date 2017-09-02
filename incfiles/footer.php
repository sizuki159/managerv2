<?php if (!defined('LOADED')) exit(0); ?>

            </div>
            <div id="footer">
                <span>&copy; IzeroCs <?php echo date('Y', time()); ?> | <?php echo Librarys\File\FileInfo::sizeToString(@memory_get_usage() - $memoryUsageBegin); ?></span>
            </div>
        </div>
    </body>
</html>
<?php ob_end_flush(); ?>