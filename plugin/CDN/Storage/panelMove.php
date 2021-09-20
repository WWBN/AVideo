<div class="panel panel-default">
    <div class="panel-heading">
        <?php echo $video['title']; ?>
    </div>
    <div class="panel-body">
        <div class="btn-group btn-group-justified" role="group" aria-label="Basic example">
            <?php
            $totalL = $totalR = $totalFileSizeR = $totalFileSizeL = 0;
            $list = CDNStorage::getFilesListBoth($videos_id);
            $listString = array();
            foreach ($list as $key => $value) {
                if (!empty($value['local']['local_filesize']) && $value['local']['local_filesize'] > 20 && $value['local']['local_filesize'] > 0) {
                    $totalL++;
                    $totalFileSizeL += $value['local']['local_filesize'];
                    $humanSize = humanFileSize($value['local']['local_filesize']);
                    $listString[] = '<span class="label label-success"><i class="fas fa-map-marker-alt"></i> ' . __('Local') . '</span>';
                    $listString[] = '<span class="label label-primary">' . $humanSize . '</span>';
                }
                if (!empty($value['remote']['remote_filesize']) && $value['remote']['remote_filesize'] > 0) {
                    $totalR++;
                    $totalFileSizeR += $value['remote']['remote_filesize'];
                    $humanSize = humanFileSize($value['remote']['remote_filesize']);
                    $listString[] = '<span class="label label-warning"><i class="fas fa-project-diagram"></i> ' . __('Storage') . '</span>';
                    $listString[] = '<span class="label label-primary">' . $humanSize . '</span>';
                }

                $listString[] = " {$key} <br>";
            }

            if ($totalL) {
                ?>
                <button type="button" class="btn btn-warning" onclick="CDNStorageUpload();">
                    <i class="fas fa-project-diagram"></i>
                    <?php
                    printf(__('Upload %d files to storage'), $totalL);
                    $humanSize = humanFileSize($totalFileSizeL);
                    echo ' (' . $humanSize . ')';
                    ?>
                </button>
                <script>

                    function CDNStorageUpload() {
                        modal.showPleaseWait();
                        var url = webSiteRootURL + 'plugin/CDN/Storage/moveLocalToRemote.json.php';
                        $.ajax({
                            url: url,
                            method: 'POST',
                            data: {
                                'videos_id': <?php echo $videos_id; ?>
                            },
                            success: function (response) {
                                //modal.hidePleaseWait();
                                document.location.reload();
                                console.log('CDNStorageUpload', response);
                            }
                        });
                    }
                </script>
                <?php
            }
            if ($totalR) {
                ?>
                <button type="button" class="btn btn-success" onclick="CDNStorageDownload();">
                    <i class="fas fa-project-diagram"></i>
                    <?php
                    printf(__('Download %d files to disk'), $totalR);
                    $humanSize = humanFileSize($totalFileSizeR);
                    echo ' (' . $humanSize . ')';
                    ?>
                </button>
                <script>

                    function CDNStorageDownload() {
                        modal.showPleaseWait();
                        var url = webSiteRootURL + 'plugin/CDN/Storage/moveRemoteToLocal.json.php';
                        $.ajax({
                            url: url,
                            method: 'POST',
                            data: {
                                'videos_id': <?php echo $videos_id; ?>
                            },
                            success: function (response) {
                                //modal.hidePleaseWait();
                                document.location.reload();
                                console.log('CDNStorageDownload', response);
                            }
                        });
                    }
                </script>
                <?php
            }
            ?>
        </div>
    </div>
    <div class="panel-footer" style="max-height: 440px; overflow: auto;">
        <?php
        echo implode('', $listString);
        ?>
    </div>
</div>