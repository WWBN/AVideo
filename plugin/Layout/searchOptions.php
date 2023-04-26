<?php
$global['doNotSearch'] = 1;
$tags = TagsHasVideos::getAllWithVideo();
$global['doNotSearch'] = 0;
?>
<style>
    #searchOptionsMenu {
        position: relative;
        height: 50px;
        display: flex;
        align-items: center;
        z-index: 1029;
    }

    #searchOptionsMenu > div {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
    }

    #searchFieldsNamesBelowNavbar-dropdown , #catNameBelowNavbar-dropdown , #tagNameBelowNavbar-dropdown {
        min-width: 150px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #searchFieldsNamesBelowNavbar-dropdown {
        border-right-width: 0;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
    #catNameBelowNavbar-dropdown,  #tagNameBelowNavbar-dropdown{
        border-left-width: 0;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        border-right-width: 0;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
    #searchOptionsButton {
        border-left-width: 0;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    #searchOptionsMenu .panel {
        margin: 0;
    }

    #searchOptionsMenu .panel-body {
        max-height: 60vh;
        overflow: auto;
    }

    #searchOptionsMenu .form-check {
        white-space: nowrap;
    }

    #searchOptionsMenu .form-check-input {
        margin-right: 5px;
    }

    #searchOptionsMenu .form-check-label {
        display: inline-block;
        margin-bottom: 0;
        vertical-align: middle;
    }

    #searchOptionsMenu .dropdown-menu-right {
        right: 0;
        left: auto;
    }

</style>
<div class="container" id="searchOptionsMenu">
    <div>
        <div class="btn-group-justified">
            <div class="dropdown">
                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" 
                        id="searchFieldsNamesBelowNavbar-dropdown" ><?php echo __('Search in'); ?>: <span class="badge">0</span>
                    <span class="caret"></span></button>
                <div class="panel panel-default dropdown-menu dropdown-menu-right">
                    <div class="panel-body">
                        <?php
                        AVideoPlugin::loadPlugin('Layout');
                        foreach (Layout::$searchOptions as $key => $value) {
                            ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="<?php echo $value['value']; ?>" 
                                       id="filterCheck<?php echo $key; ?>" name="searchFieldsNamesBelowNavbar[]">
                                <label class="form-check-label" for="filterCheck<?php echo $key; ?>">
                                    <?php echo $value['text']; ?>
                                </label>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="dropdown">
                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="catNameBelowNavbar-dropdown">
                    <i class="fas fa-list"></i> <?php echo __('All Categories'); ?>
                    <span class="caret"></span>
                </button>
                <div class="panel panel-default dropdown-menu dropdown-menu-right">
                    <div class="panel-body">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="" id="catNameBelowNavbar" name="catNameBelowNavbar">
                            <label class="form-check-label" for="catNameBelowNavbar">
                                <span class="content"><i class="fas fa-list"></i> <?php echo __('All Categories'); ?></span>
                            </label>
                        </div>
                        <?php
                        $global['doNotSearch'] = 1;
                        $categories_edit = Category::getAllCategories(false, true);
                        $global['doNotSearch'] = 0;
                        foreach ($categories_edit as $key => $value) {
                            ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" value="<?php echo $value['clean_name']; ?>" id="catNameBelowNavbar<?php echo $value['id']; ?>" name="catNameBelowNavbar">
                                <label class="form-check-label" for="catNameBelowNavbar<?php echo $value['id']; ?>">
                                    <span class="content"><i class="<?php echo $value['iconClass']; ?>"></i> <?php echo __($value['hierarchyAndName']); ?></span>
                                </label>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="dropdown">
                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="tagNameBelowNavbar-dropdown">
                    <i class="fas fa-tags"></i> <?php echo __('Tags'); ?>
                    <span class="caret"></span>
                </button>
                <div class="panel panel-default dropdown-menu dropdown-menu-right">
                    <div class="panel-body">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="" id="filterTag0" checked name="tags_idBelowNavbar">
                            <label class="form-check-label" for="filterTag0">
                                <i class="fas fa-tags"></i> <?php echo __('All'); ?>
                            </label>
                        </div>
                        <?php
                        foreach ($tags as $key => $value) {
                            ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" value="<?php echo $value['id']; ?>" 
                                id="filterTag<?php echo $value['id']; ?>" name="tags_idBelowNavbar">
                                <label class="form-check-label" for="filterTag<?php echo $value['id']; ?>">
                                    <i class="fas fa-tag"></i> <?php echo __($value['name']); ?>
                                </label>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
            <button class="btn btn-default btn-outline-secondary border-right-0 faa-parent animated-hover" type="button" id="searchOptionsButton" onclick="searchOptionsButton();" >
                <i class="fas fa-search faa-shake"></i>
            </button>
        </div>
    </div>
</div>
<script>
    var searchFieldsNamesBelowNavbarChecked = 'input[name="searchFieldsNamesBelowNavbar[]"]:checked';
    var searchFieldsNamesBelowNavbarSearchFieldsNames = 'input[name="searchFieldsNamesBelowNavbar[]"], input[name="searchFields[]"]';
    var catNameBelowNavbarChecked = 'input[name="catNameBelowNavbar"]:checked';
    var catNameBelowNavbarCatName = 'input[name="catNameBelowNavbar"], input.form-check-input[type="radio"][name="catName"]';
    var tags_idBelowNavbarChecked = 'input[name="tags_idBelowNavbar"]:checked';
    var tags_idBelowNavbar = 'input[name="tags_idBelowNavbar"]';
    $(document).ready(function () {

        $(searchFieldsNamesBelowNavbarSearchFieldsNames).on('change', function () {            
            checkAllSearchFilter('#searchNavItem', $(this).val(), $(this).prop('checked'));
            saveSearchFiltersToCookie();
        });

        $(catNameBelowNavbarCatName).on('change', function () {  
            checkAllSearchFilter('#searchNavItem', $(this).val(), $(this).prop('checked'));
            saveSearchCategoryToCookie();
        });

        $(tags_idBelowNavbar).on('change', function () {  
            checkAllSearchFilter('#searchNavItem', $(this).val(), $(this).prop('checked'));
            saveSearchTagToCookie();
        });

        $(searchFieldsNamesBelowNavbarSearchFieldsNames).on('click', function () {
            $(this).trigger('change');
        });
        $('#searchOptionsMenu label').click(function (e) {
            e.stopPropagation();
        });
    });

    function searchOptionsButton() {
        var keyword = $('#searchFormInput').val();
        if ($(searchFieldsNamesBelowNavbarChecked).length != 0 && empty(keyword)) {
            var userInput = prompt('Please enter a search keyword:');
            if (userInput !== null) {
                $('#searchFormInput').val(userInput);
                $('#searchForm').submit();
            }

            avideoToastInfo('Keyword required');
            return;
        }

        $('#searchForm').submit();
    }

</script>