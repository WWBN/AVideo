<?php
/**
 * Shared watch-page panel for videos and live links.
 * $videoDetails contains HTML rendered by the caller: header, preview, creator,
 * actions, menus, metadata and comments. Keep content-specific actions there.
 *
 * @var array $videoDetails
 */
?>
<link href="<?php echo getURL('view/css/videoDetails.css'); ?>" rel="stylesheet" type="text/css" />
<div class="panel panel-default videoDetailsPanel">
    <div class="panel-body videoDetailsHeader">
        <?php echo $videoDetails['header']; ?>
    </div>
    <div class="panel-body">
        <div class="row divMainVideo videoDetailsSummary">
            <?php if (trim($videoDetails['preview'] ?? '') !== '') { ?>
                <div class="col-sm-4 col-md-4 hidden-xs videoDetailsPreview">
                    <?php echo $videoDetails['preview']; ?>
                </div>
            <?php } ?>
            <div class="col-xs-12 col-sm-8 col-md-8 videoDetailsCreator">
                <?php echo $videoDetails['creator']; ?>
            </div>
        </div>
    </div>
    <?php if (trim($videoDetails['actions'] ?? '') !== '') { ?>
        <div class="panel-footer videoDetailsActions">
            <div class="row">
                <div class="col-md-12 text-muted videoDetailsActionButtons">
                    <?php echo $videoDetails['actions']; ?>
                </div>
            </div>
        </div>
    <?php } ?>
    <?php if (trim($videoDetails['menus'] ?? '') !== '') { ?>
        <div class="panel-footer videoDetailsMenus">
            <?php echo $videoDetails['menus']; ?>
        </div>
    <?php } ?>
    <?php if (trim($videoDetails['metadata'] ?? '') !== '') { ?>
        <div class="panel-body videoDetailsMetadata" id="modeYoutubeBottomContentDetails">
            <?php echo $videoDetails['metadata']; ?>
        </div>
    <?php } ?>
    <?php if (trim($videoDetails['comments'] ?? '') !== '') { ?>
        <div class="panel-footer videoDetailsComments">
            <?php echo $videoDetails['comments']; ?>
        </div>
    <?php } ?>
</div>
