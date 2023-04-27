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

    #searchOptionsMenu>div {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
    }

    #searchFieldsNamesBelowNavbar-dropdown,
    #catNameBelowNavbar-dropdown,
    #tagNameBelowNavbar-dropdown {
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

    #catNameBelowNavbar-dropdown,
    #tagNameBelowNavbar-dropdown {
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

    @media (max-width: 767px) {

        #searchFieldsNamesBelowNavbar-dropdown,
        #catNameBelowNavbar-dropdown,
        #tagNameBelowNavbar-dropdown {
            min-width: auto;
        }

        #searchOptionsMenu .dropdown-menu-right {
            right: 0;
            left: 0;
            width: 100vw;
            position: fixed;
        }
    }
</style>
<div class="container" id="searchOptionsMenu">
    <div class="btn-group-justified">
        <div class="dropdown">
            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="searchFieldsNamesBelowNavbar-dropdown">
                <?php echo __('Search in'); ?>:</span> <span class="badge">0
                    <span class="caret"></span></button>
            <div class="dropdown-menu dropdown-menu-right">
                <?php
                Layout::getSearchOptionHTML();
                ?>
            </div>
        </div>
        <div class="dropdown">
            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="catNameBelowNavbar-dropdown">
                <i class="fas fa-list"></i> <?php echo __('All Categories'); ?>
                <span class="caret"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <?php
                Layout::getSearchCategoriesHTML();
                ?>
            </div>
        </div>
        <div class="dropdown">
            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="tagNameBelowNavbar-dropdown">
                <i class="fas fa-tags"></i> <?php echo __('Tags'); ?>
                <span class="caret"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <?php
                Layout::getSearchTagsHTML();
                ?>
            </div>
        </div>
        <button class="btn btn-default btn-outline-secondary border-right-0 faa-parent animated-hover" type="button" id="searchOptionsButton" onclick="searchOptionsButton();">
            <i class="fas fa-search faa-shake"></i>
        </button>
    </div>
</div>
<script>
    function searchOptionsButton() {
        var keyword = $('#searchFormInput').val();
        if (searchTotalSelectedSearchIn != 0 && empty(keyword)) {
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