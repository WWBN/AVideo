<li class="nav-item" style="margin-right: 0px; " id="searchNavItem">
    <div class="navbar-header">

        <div class="navbar-header">
            <button type="button" class="visible-xs navbar-toggle btn btn-default navbar-btn faa-parent animated-hover" data-toggle="collapse" data-target="#mysearch" style="padding: 6px 12px;">
                <span class="fa fa-search faa-shake"></span>
            </button>
        </div>
        <div class="input-group" id="mysearch">
            <form class="navbar-form form-inline input-group" role="search" id="searchForm" method="get" action="<?php echo $global['webSiteRootURL']; ?>">
                <span class="input-group-prepend">
                    <button type="button" id="filterButton"
                            class="btn btn-default navbar-btn dropdown-toggle"
                            aria-controls="searchFilterPanel" aria-label="<?php echo __('Filters'); ?>" aria-expanded="false">
                        <i class="fas fa-sliders-h"></i>
                    </button>
                </span>
                <input class="form-control globalsearchfield" type="text" name="search" placeholder="<?php echo __("Search"); ?>" id="searchFormInput">
                <span class="input-group-append">
                    <button class="btn btn-default btn-outline-secondary border-right-0 border py-2 faa-parent animated-hover" type="submit" id="buttonSearch" data-toggle="collapse" data-target="#mysearch">
                        <i class="fas fa-search faa-shake notLoadingIcon"></i>
                        <i class="fa-solid fa-sync fa-spin loadingIcon"></i>
                        <i class="fa-solid fa-circle-exclamation fa-beat-fade text-danger notFoundIcon"></i>
                    </button>
                </span>
                <?php
                echo getIncludeFileContent("{$global['systemRootPath']}view/include/navbarSearchDropdown.php", [], 'navbarSearchDropdown');
                ?>
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

    function updateSearchSelectedValues() {
        searchTotalSelectedSearchIn = $('#search-tab .form-check-input:checked').length;
        searchSelectedCategoryVal = $('#filter-tab .form-check-input:checked').val();
        searchSelectedTagVal = $('#filter-tags-tab .form-check-input:checked').val();
        searchSelectedCategory = $('#filter-tab .form-check-input:checked').closest('.form-check-label').text().trim();
        searchSelectedTag = $('#filter-tags-tab .form-check-input:checked').closest('.form-check-label').text().trim();
    }

    $(document).ready(function () {
        setSearchFilterIcon();
        $("#searchFormInput").val(getSearchParam("search"));
    });
    function setSearchFilterIcon() {
        updateSearchSelectedValues();
        $('#searchFieldsNamesBelowNavbar-dropdown .badge').text(searchTotalSelectedSearchIn);
        $('#catNameBelowNavbar-dropdown .searchOptionLabel').text(searchSelectedCategory);
        $('#tagNameBelowNavbar-dropdown .searchOptionLabel').text(searchSelectedTag);
        var active = false;
        $('#searchFilterPanel .tab-pane').each(function() {
            var $inputs = $(this).find('.form-check-input');
            var count = $inputs.filter(':checked').length;
            var changed = $inputs.filter(function() {
                return this.checked !== this.defaultChecked;
            }).length > 0;
            active = active || changed;
            $('.filterTabs a[href="#' + this.id + '"] .filterTabCount')
                .text(count).prop('hidden', !changed || !count);
        });
        $('#filterButton').toggleClass('active', active);
    }

</script>
