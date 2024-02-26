<div class="dropdown" id="filterDropdown">
    <?php
    if (class_exists('TagsHasVideos')) {
        $global['doNotSearch'] = 1;
        $tags = TagsHasVideos::getAllWithVideo();
        $global['doNotSearch'] = 0;
    }
    ?>
    <div class="panel panel-default dropdown-menu" aria-labelledby="filterButton" style="margin: 0;">
        <div class="panel-heading  tabbable-line">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#search-tab" rel="nofollow"><?php echo __('Search in'); ?></a></li>
                <li><a data-toggle="tab" href="#filter-tab" rel="nofollow"><?php echo __('Categories'); ?></a></li>
                <?php
                if (!empty($tags)) {
                ?>
                    <li><a data-toggle="tab" href="#filter-tags-tab" rel="nofollow"><?php echo __('Tags'); ?></a></li>
                <?php
                }
                ?>
                <li><a data-toggle="tab" href="#filter-datetime-tab" rel="nofollow"><?php echo __('Date within'); ?></a></li>
                <li><a data-toggle="tab" href="#filter-views-tab" rel="nofollow"><?php echo __('Views'); ?></a></li>
            </ul>
        </div>
        <div class="panel-body">
            <div class="tab-content">
                <div id="search-tab" class="tab-pane fade in active">
                    <?php
                    Layout::getSearchOptionHTML();
                    ?>
                </div>
                <div id="filter-tab" class="tab-pane fade">
                    <?php
                    Layout::getSearchCategoriesHTML();
                    ?>
                </div>
                <div id="filter-tags-tab" class="tab-pane fade">
                    <?php
                    Layout::getSearchTagsHTML();
                    ?>
                </div>
                <div id="filter-datetime-tab" class="tab-pane fade">
                    <?php
                    Layout::getSearchDateHTML();
                    ?>
                </div>
                <div id="filter-views-tab" class="tab-pane fade">
                    <?php
                    Layout::getSearchViewsHTML();
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>