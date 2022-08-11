<?php
require_once __DIR__.'/../../../videos/configuration.php';
$video = Video::getVideoLight($videos_id);
?>
<div class="dropdown">
    <button class="btn btn-dark btn-xs dropdown-toggle" type="button" data-toggle="dropdown">
        <i class="fas fa-ellipsis-v"></i>
    </button>
    <ul class="dropdown-menu">
        <?php
        if ((!empty($video['description'])) && !empty($obj->Description)) {
            $desc = nl2br(trim($video['description']));
            if (!isHTMLEmpty($desc)) {
                $duid = uniqid();
                $titleAlert = str_replace(array('"', "'"), array('``', "`"), $video['title']);
                ?>
                <li>
                    <button type="button" class="btn-link" onclick='avideoAlert("<?php echo $titleAlert; ?>", "<div style=\"max-height: 300px; overflow-y: scroll;overflow-x: hidden;\" id=\"videoDescriptionAlertContent<?php echo $duid; ?>\" ></div>", "");$("#videoDescriptionAlertContent<?php echo $duid; ?>").html($("#videoDescription<?php echo $duid; ?>").html());return false;' data-toggle="tooltip" title="<?php echo __("Description"); ?>"><i class="far fa-file-alt"></i> <span  class="hidden-md hidden-sm hidden-xs"><?php echo __("Description"); ?></span></a>
                        <div id="videoDescription<?php echo $duid; ?>" style="display: none;"><?php echo $desc; ?></div>
                    </button>
                </li>
                <?php
            }
        }
        ?>
        <?php if (!empty($video['trailer1'])) { ?>
            <li>
                <button type="button" class="btn-link" onclick="showTrailer('<?php echo parseVideos($video['trailer1'], 1); ?>'); return false;" class="cursorPointer" >
                    <i class="fa fa-video"></i> <?php echo __("Trailer"); ?>
                </button>

            </li>
        <?php }
        ?>
        <?php if (Video::canEdit($video['id'])) { ?>
            <li>
                <button type="button" class="btn-link" onclick="avideoModalIframe(webSiteRootURL + 'view/managerVideosLight.php?avideoIframe=1&videos_id=<?php echo $video['id']; ?>');return false;" data-toggle="tooltip" title="<?php echo __("Edit Video"); ?>">
                    <i class="fa fa-edit"></i> <span class="hidden-md hidden-sm hidden-xs"><?php echo __("Edit Video"); ?></span>
                </button>
            </li>
            <?php
            $suggestedBTN = Layout::getSuggestedButton($video['id'], 'btn-link');
            if (!empty($suggestedBTN)) {
                echo "<li>{$suggestedBTN}</li>";
            }
        }
        ?>
    </ul>
</div>