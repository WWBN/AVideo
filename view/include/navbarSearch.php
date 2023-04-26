<?php
$global['doNotSearch'] = 1;
$tags = TagsHasVideos::getAllWithVideo();
$global['doNotSearch'] = 0;
?>
<li class="nav-item" style="margin-right: 0px; " id="searchNavItem">
    <div class="navbar-header">

        <div class="navbar-header">
            <button type="button" class="visible-xs navbar-toggle btn btn-default navbar-btn faa-parent animated-hover animate__animated animate__bounceIn" data-toggle="collapse" data-target="#mysearch" style="padding: 6px 12px;">
                <span class="fa fa-search faa-shake"></span>
            </button>
        </div>
        <div class="input-group" id="mysearch">
            <form class="navbar-form form-inline input-group" role="search" id="searchForm" method="get" action="<?php echo $global['webSiteRootURL']; ?>">
                <span class="input-group-prepend">
                    <button type="button" id="filterButton" 
                            class="btn btn-default navbar-btn dropdown-toggle faa-parent animated-hover animate__animated animate__bounceIn" 
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-sort-down"></i>
                    </button>
                </span>
                <input class="form-control globalsearchfield" type="text" value="<?php
                if (!empty($_GET['search'])) {
                    echo htmlentities($_GET['search']);
                }
                ?>" name="search" placeholder="<?php echo __("Search"); ?>" id="searchFormInput">
                <span class="input-group-append">
                    <button class="btn btn-default btn-outline-secondary border-right-0 border py-2 faa-parent animated-hover" type="submit" id="buttonSearch" data-toggle="collapse" data-target="#mysearch">
                        <i class="fas fa-search faa-shake"></i>
                    </button>
                </span>
                <div class="dropdown" id="filterDropdown">
                    <div class="panel panel-default dropdown-menu" aria-labelledby="filterButton" style="margin: 0;">
                        <div class="panel-heading  tabbable-line">
                            <ul class="nav nav-tabs">
                                <li class="active"><a data-toggle="tab" href="#search-tab"><?php echo __('Search in'); ?></a></li>
                                <li><a data-toggle="tab" href="#filter-tab"><?php echo __('Categories'); ?></a></li>
                                <?php
                                if (!empty($tags)) {
                                    ?>
                                    <li><a data-toggle="tab" href="#filter-tags-tab"><?php echo __('Tags'); ?></a></li>
                                    <?php
                                }
                                ?>
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
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>

    </div>
</li>
<script>
    var searchTotalSelectedSearchIn = 0;
    var searchSelectedCategoryVal = '';
    var searchSelectedTagVal = '';
    var searchSelectedCategory = '';
    var searchSelectedTag = '';
    
    function updateSearchSelectedValues(){
        searchTotalSelectedSearchIn = $('#search-tab .form-check-input:checked').length;
        searchSelectedCategoryVal = $('#filter-tab .form-check-input:checked').val();
        searchSelectedTagVal = $('#filter-tags-tab .form-check-input:checked').val();
        searchSelectedCategory = $('#filter-tab .form-check-input:checked').parent().find('.form-check-label').html();
        searchSelectedTag = $('#filter-tags-tab .form-check-input:checked').parent().find('.form-check-label').html();
    }
    
    $(document).ready(function () {
        $('#filterButton').click(function () {
            $('#filterDropdown').toggleClass('show');
        });
        setSearchFilterIcon();
    });
    function setSearchFilterIcon() {
        updateSearchSelectedValues();
        $('#searchFieldsNamesBelowNavbar-dropdown .badge').text(searchTotalSelectedSearchIn);
        $('#catNameBelowNavbar-dropdown').html(searchSelectedCategory);
        $('#tagNameBelowNavbar-dropdown').html(searchSelectedTag);
        // check if no filter checkboxes are checked and search_category0 is checked and search_tag0 is checked
        if (searchTotalSelectedSearchIn === 0 && empty(searchSelectedCategoryVal) &&  empty(searchSelectedTagVal)) {
            // add the text-muted icon to the filterButton
            $('#filterButton i').removeClass('fa-filter');
            $('#filterButton i').addClass('fa-sort-down');
        } else {
            // remove the text-muted icon from the filterButton
            $('#filterButton i').removeClass('fa-sort-down');
            $('#filterButton i').addClass('fa-filter');
        }
    }

</script>