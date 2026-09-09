<div class="dropdown" id="filterDropdown">
    <?php
    if (class_exists('TagsHasVideos')) {
        $global['doNotSearch'] = 1;
        $tags = TagsHasVideos::getAllWithVideo();
        $global['doNotSearch'] = 0;
    }
    ?>
    <div class="panel panel-default dropdown-menu searchFilterPanel" id="searchFilterPanel" role="region" aria-labelledby="filterButton" aria-hidden="true">
        <div class="panel-heading">
            <div class="searchFilterHeading">
                <strong><i class="fas fa-sliders-h" aria-hidden="true"></i> <?php echo __('Filters'); ?></strong>
                <button type="button" class="btn btn-default btn-sm searchFilterClose" aria-label="<?php echo __('Close'); ?>"><i class="fas fa-times" aria-hidden="true"></i></button>
            </div>
            <ul class="nav nav-pills filterTabs" role="tablist">
                <li class="active"><a data-toggle="tab" href="#search-tab" id="search-tab-label" role="tab" aria-controls="search-tab" aria-selected="true" tabindex="0" rel="nofollow"><i class="fas fa-search" aria-hidden="true"></i> <span><?php echo __('Search in'); ?></span><span class="badge filterTabCount" hidden></span></a></li>
                <li><a data-toggle="tab" href="#filter-tab" id="filter-tab-label" role="tab" aria-controls="filter-tab" aria-selected="false" tabindex="-1" rel="nofollow"><i class="fas fa-list" aria-hidden="true"></i> <span><?php echo __('Categories'); ?></span><span class="badge filterTabCount" hidden></span></a></li>
                <?php
                if (!empty($tags)) {
                ?>
                    <li><a data-toggle="tab" href="#filter-tags-tab" id="filter-tags-tab-label" role="tab" aria-controls="filter-tags-tab" aria-selected="false" tabindex="-1" rel="nofollow"><i class="fas fa-tags" aria-hidden="true"></i> <span><?php echo __('Tags'); ?></span><span class="badge filterTabCount" hidden></span></a></li>
                <?php
                }
                ?>
                <li><a data-toggle="tab" href="#filter-datetime-tab" id="filter-datetime-tab-label" role="tab" aria-controls="filter-datetime-tab" aria-selected="false" tabindex="-1" rel="nofollow"><i class="fas fa-calendar-alt" aria-hidden="true"></i> <span><?php echo __('Date within'); ?></span><span class="badge filterTabCount" hidden></span></a></li>
                <li><a data-toggle="tab" href="#filter-views-tab" id="filter-views-tab-label" role="tab" aria-controls="filter-views-tab" aria-selected="false" tabindex="-1" rel="nofollow"><i class="fas fa-eye" aria-hidden="true"></i> <span><?php echo __('Views'); ?></span><span class="badge filterTabCount" hidden></span></a></li>
            </ul>
        </div>
        <div class="panel-body searchFilterBody">
            <div class="tab-content">
                <div id="search-tab" role="tabpanel" aria-labelledby="search-tab-label" class="tab-pane fade in active">
                    <?php
                    Layout::getSearchOptionHTML();
                    ?>
                </div>
                <div id="filter-tab" role="tabpanel" aria-labelledby="filter-tab-label" class="tab-pane fade">
                    <?php
                    Layout::getSearchCategoriesHTML();
                    ?>
                </div>
                <div id="filter-tags-tab" role="tabpanel" aria-labelledby="filter-tags-tab-label" class="tab-pane fade">
                    <?php
                    Layout::getSearchTagsHTML();
                    ?>
                </div>
                <div id="filter-datetime-tab" role="tabpanel" aria-labelledby="filter-datetime-tab-label" class="tab-pane fade">
                    <?php
                    Layout::getSearchDateHTML();
                    ?>
                </div>
                <div id="filter-views-tab" role="tabpanel" aria-labelledby="filter-views-tab-label" class="tab-pane fade">
                    <?php
                    Layout::getSearchViewsHTML();
                    ?>
                </div>
            </div>
        </div>
        <div class="panel-footer searchFilterFooter">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search" aria-hidden="true"></i> <?php echo __('Search'); ?></button>
        </div>
    </div>
</div>
