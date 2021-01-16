<?php
$ads1 = getAdsLeaderBoardTop();
if (!empty($ads1)) {
    ?>
    <div class="row text-center" style="padding: 10px;">
        <?php echo $ads1; ?>
    </div>
    <?php
}
?>

<h2 class="infoTitle" style=""><?php echo $video['title']; ?></h2>
<h4 class="infoDetails">
    <?php
    if (!empty($video['rate'])) {
        ?>
        <span class="label label-success"><i class="fab fa-imdb"></i> IMDb <?php echo $video['rate']; ?></span>
        <?php
    }
    ?>

    <?php
    if (!empty($advancedCustom) && empty($advancedCustom->doNotDisplayViews)) {
        ?>
        <span class="label label-default"><i class="fa fa-eye"></i> <?php echo $video['views_count']; ?></span>
    <?php } ?>
    <?php
    if (!empty($advancedCustom) && empty($advancedCustom->doNotDisplayLikes)) {
        ?>
        <span class="label label-success"><i class="fa fa-thumbs-up"></i> <?php echo $video['likes']; ?></span>
    <?php } ?>
    <?php
    if (!empty($advancedCustom) && empty($advancedCustom->doNotDisplayCategory)) {
        ?>
        <span class="label label-success"><a style="color: inherit;" class="tile__cat" cat="<?php echo $video['clean_category']; ?>" href="<?php echo $global['webSiteRootURL'] . "cat/" . $video['clean_category']; ?>"><i class="<?php echo $video['iconClass']; ?>"></i> <?php echo $video['category']; ?></a></span>
    <?php } ?>
    <?php
    foreach ($video['tags'] as $value2) {
        if (!empty($advancedCustom) && empty($advancedCustom->doNotDisplayGroupsTags)) {
            if (is_array($value2)) {
                $value2 = (object) $value2;
            }
            if ($value2->label === __("Group")) {
                ?>
                <span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span>
                <?php
            }
        }

        if ($advancedCustom->paidOnlyFreeLabel && !empty($value2->label) && $value2->label === __("Paid Content")) {
            ?><span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span><?php
        }
        if (!empty($advancedCustom) && empty($advancedCustom->doNotDisplayPluginsTags)) {
            if ($value2->label === "Plugin") {
                ?>
                <span class="label label-<?php echo $value2->type; ?>"><?php echo $value2->text; ?></span>
                <?php
            }
        }
    }
    ?>
    <?php
    if (!empty($video['rrating'])) {
        include $global['systemRootPath'] . 'view/rrating/rating-' . $video['rrating'] . '.php';
    } else if (!empty($advancedCustom) && $advancedCustom->showNotRatedLabel) {
        include $global['systemRootPath'] . 'view/rrating/notRated.php';
    }
    ?>
</h4>