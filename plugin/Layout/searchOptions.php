<?php
if (!class_exists('TagsHasVideos')) {
    return;
}
$global['doNotSearch'] = 1;
$timeLogName = TimeLogStart("SearchOptions");
$tags = TagsHasVideos::getAllWithVideo();
$global['doNotSearch'] = 0;
?>
<style>
    #searchOptionsMenu .btn-group-justified {
        width: auto;
    }

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

        #searchOptionsMenu .btn-group-justified {
            width: 100%;
        }
    }
</style>
<div class="container" id="searchOptionsMenu">
    <div class="btn-group-justified">
        <div class="dropdown">
            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="searchFieldsNamesBelowNavbar-dropdown">
                <span class="hidden-sm hidden-xs">
                    <?php echo __('Search in'); ?>:</span>
                </span>
                <span class="badge">0
                    <span class="caret"></span></button>
            <div class="dropdown-menu dropdown-menu-right">
                <?php
                TimeLogEnd($timeLogName, __LINE__);
                Layout::getSearchOptionHTML();
                TimeLogEnd($timeLogName, __LINE__);
                ?>
            </div>
        </div>
        <div class="dropdown">
            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="catNameBelowNavbar-dropdown">
                <i class="fas fa-list"></i>
                <span class="hidden-sm hidden-xs">
                    <?php echo __('All Categories'); ?>
                    <span class="caret"></span>
                </span>
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <?php
                TimeLogEnd($timeLogName, __LINE__);
                Layout::getSearchCategoriesHTML();
                TimeLogEnd($timeLogName, __LINE__);
                ?>
            </div>
        </div>
        <div class="dropdown">
            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="tagNameBelowNavbar-dropdown">
                <i class="fas fa-tags"></i>
                <span class="hidden-sm hidden-xs">
                    <?php echo __('Tags'); ?>
                    <span class="caret"></span>
                </span>
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <?php
                TimeLogEnd($timeLogName, __LINE__);
                Layout::getSearchTagsHTML();
                TimeLogEnd($timeLogName, __LINE__);
                ?>
            </div>
        </div>
        <div class="dropdown">
            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="tagNameBelowNavbar-dropdown">
                <i class="far fa-calendar-alt"></i>
                <span class="hidden-sm hidden-xs">
                    <?php echo __('Date within'); ?>
                    <span class="caret"></span>
                </span>
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <?php
                TimeLogEnd($timeLogName, __LINE__);
                Layout::getSearchDateHTML();
                TimeLogEnd($timeLogName, __LINE__);
                ?>
            </div>
        </div>
        <div class="dropdown">
            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="tagNameBelowNavbar-dropdown">
                <i class="fas fa-eye"></i>
                <span class="hidden-sm hidden-xs">
                    <?php echo __('Views'); ?>
                    <span class="caret"></span>
                </span>
            </button>
            <div class="dropdown-menu dropdown-menu-right">
                <?php
                TimeLogEnd($timeLogName, __LINE__);
                Layout::getSearchViewsHTML();
                TimeLogEnd($timeLogName, __LINE__);
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

    $(document).ready(function() {
        $(document).on('click', '#searchOptionsMenu .dropdown-menu', function(e) {
            e.stopPropagation();
        });
    });
</script>