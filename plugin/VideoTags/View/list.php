<?php
global $global;
?>
<!-- mainAreaTags start -->
<div class="mainAreaTags row">  
    <?php
    $tags_count = 0;
    $totalColumns = 4;
    $tagsTypes = TagsTypes::getAll();
    $subscribedTagsIds = Tags_subscriptions::getAllTagsIdsFromUsers_id(User::getId());
    foreach ($tagsTypes as $tagType) {
        $tags = Tags::getAllTagsWithTotalVideos($tagType['id']);
        if (empty($tags)) {
            continue;
        }
        $tags_count++;
        if($tags_count%$totalColumns===0){
            echo '<div class="clearfix"></div>';
        }
        ?>
        <div class="col-md-<?php echo 12/$totalColumns; ?>">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <?php
                    echo $tagType['name'];
                    ?>
                </div>
                <div class="panel-body">
                    <div class="list-group"><?php
                        foreach ($tags as $tag) {
                            $encryptedIdAndUser = encryptString(array('tags_id'=>$tag['id'], 'users_id'=> User::getId(), ));
                            $checked = '';
                            if(in_array($tag['id'], $subscribedTagsIds)){
                                $checked = 'checked';
                            }
                            
                            ?>
                            <label class="list-group-item">
                                <input class="form-check-input tagCheckBox" type="checkbox" value="<?php echo $encryptedIdAndUser; ?>" <?php echo $checked; ?>>
                                <?php echo $tag['name']; ?>
                                <a class="btn btn-xs btn-default pull-right" href="<?php echo VideoTags::getTagLink($tag['id']); ?>" style="margin: 0 10px;">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                <span class="badge"><?php echo $tag['total_videos'], ' ', __('videos'); ?></span>
                            </label>
                            <?php
                        }
                        ?>

                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('.tagCheckBox').change(function(){
            var encryptedIdAndUser = $(this).val();
            var is_checked = $(this).is(":checked");
            var data = {encryptedIdAndUser:encryptedIdAndUser, add: is_checked};
            var url = webSiteRootURL + 'plugin/VideoTags/subscribe.json.php';
            avideoAjax2(url, data, false);
        });
    });
</script>
<!-- mainAreaTags end -->