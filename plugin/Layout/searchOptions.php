<div class="container" style=" position: relative;
    height: 50px;
  display: flex;
  align-items: center;    z-index: 1029;" id="searchOptionsMenu">
    <div style="
    position: absolute;
    left: 50%;
    transform: translateX(-50%);">
        <div class="btn-group-justified">
            <div class="dropdown">
                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="searchFieldsNamesBelowNavbar-dropdown" style="width: 150px;
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        border-right-width: 0;
                        border-top-right-radius: 0;
                        border-bottom-right-radius: 0;"><?php echo __('Search in'); ?>: <span class="badge">0</span>
                    <span class="caret"></span></button>
                <div class="panel panel-default dropdown-menu dropdown-menu-right" style="margin: 0;">
                    <div class="panel-body">
                        <?php
                        AVideoPlugin::loadPlugin('Layout');
                        foreach (Layout::$searchOptions as $key => $value) {
                        ?>
                            <div class="form-check" style="white-space: nowrap;">
                                <input class="form-check-input" type="checkbox" value="<?php echo $value['value']; ?>" id="filterCheck<?php echo $key; ?>" name="searchFieldsNamesBelowNavbar[]">
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
                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" id="catNameBelowNavbar-dropdown" style="width: 150px;
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        border-right-width: 0;
                        border-top-right-radius: 0;
                        border-bottom-right-radius: 0;
                        border-left-width: 0;
                        border-top-left-radius: 0;
                        border-bottom-left-radius: 0;">
                        <i class="fas fa-list"></i> <?php echo __('All Categories'); ?>
                    <span class="caret"></span>
                </button>
                <div class="panel panel-default dropdown-menu dropdown-menu-right" style="margin: 0;">
                    <div class="panel-body" style="max-height: 60vh; overflow: auto;">
                        <div class="form-check" style="white-space: nowrap;">
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
                            <div class="form-check" style="white-space: nowrap;">
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
            <button class="btn btn-default btn-outline-secondary border-right-0 faa-parent animated-hover" type="button" id="searchOptionsButton" onclick="searchOptionsButton();" style="
                    border-left-width: 0;
                    border-top-left-radius: 0;
                    border-bottom-left-radius: 0;">
                <i class="fas fa-search faa-shake"></i>
            </button>
        </div>
    </div>
</div>
<script>
    var searchFieldsNamesBelowNavbarChecked = 'input[name="searchFieldsNamesBelowNavbar[]"]:checked';
    var searchFieldsNamesBelowNavbarSearchFieldsNames = 'input[name="searchFieldsNamesBelowNavbar[]"], input[name="searchFieldsNames[]"]';
    var catNameBelowNavbarChecked = 'input[name="catNameBelowNavbar"]:checked';
    var catNameBelowNavbarCatName = 'input[name="catNameBelowNavbar"], input.form-check-input[type="radio"][name="catName"]';
    $(document).ready(function() {
        // Show the default button label for the searchFieldsNamesBelowNavbar dropdown
        var defaultText = $('#searchFieldsNamesBelowNavbar-dropdown').text().trim();

        $(searchFieldsNamesBelowNavbarSearchFieldsNames).on('change', function() {
            var val = $(this).val();
            var $otherCheckbox = $('input[value="' + val + '"]').not($(this));
            $otherCheckbox.prop('checked', $(this).prop('checked'));
            $('#searchFieldsNamesBelowNavbar-dropdown .badge').text($(searchFieldsNamesBelowNavbarChecked).length);
            saveSearchFiltersToCookie();
        });

        $(catNameBelowNavbarCatName).on('change', function() {
            var val = $(this).val();
            var $otherRadio = $('input[value="' + val + '"]').not($(this));
            $otherRadio.prop('checked', $(this).prop('checked'));
            var selectedText = $(catNameBelowNavbarChecked).parent().find('span.content').html();
            $('#catNameBelowNavbar-dropdown').html(selectedText+' <span class="caret"></span>');
            saveSearchCategoryToCookie();
        });

        $(searchFieldsNamesBelowNavbarSearchFieldsNames).on('click', function() {
            $(this).trigger('change');
        });
        $('#searchOptionsMenu label').click(function(e) {
            e.stopPropagation();
        });

        filterCheckboxes.trigger('change');
        categoryRadios.trigger('change');
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