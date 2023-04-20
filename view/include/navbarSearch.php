<li class="nav-item" style="margin-right: 0px; " id="searchNavItem">
    <div class="navbar-header">

        <div class="navbar-header">
            <button type="button" class="visible-xs navbar-toggle btn btn-default navbar-btn faa-parent animated-hover animate__animated animate__bounceIn" data-toggle="collapse" data-target="#mysearch" style="padding: 6px 12px;">
                <span class="fa fa-search faa-shake"></span>
            </button>
        </div>
        <div class="input-group" id="mysearch">
            <form class="navbar-form form-inline input-group" role="search" id="searchForm" method="get" action="<?php echo $global['webSiteRootURL']; ?>" >
                <span class="input-group-prepend">
                    <button type="button" id="filterButton" class="btn btn-default navbar-btn dropdown-toggle faa-parent animated-hover animate__animated animate__bounceIn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
                        <div class="panel-heading">
                            Search in:
                        </div>
                        <div class="panel-body">
                            <?php
                            AVideoPlugin::loadPlugin('Layout');
                            foreach (Layout::$searchOptions as $key => $value) {
                            ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="<?php echo $value['value']; ?>" id="filterCheck<?php echo $key; ?>" name="searchFieldsNames[]">
                                    <label class="form-check-label" for="filterCheckTitle">
                                        <?php echo $value['text']; ?>
                                    </label>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                        <div class="panel-heading">
                            Filter by category:
                        </div>
                        <div class="panel-body">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" id="search_category0" name="catName" checked value="">
                                <label class="form-check-label" for="search_category0">
                                    <i class="fas fa-list"></i> All Categories
                                </label>
                            </div>
                            <?php
                            $global['doNotSearch'] = 1;
                            $categories_edit = Category::getAllCategories(false, true);
                            $global['doNotSearch'] = 0;
                            foreach ($categories_edit as $key => $value) {
                            ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" value="<?php echo $value['clean_name']; ?>" id="search_category<?php echo $value['id']; ?>" name="catName">
                                    <label class="form-check-label" for="search_category<?php echo $value['id']; ?>">
                                        <i class="<?php echo $value['iconClass']; ?>"></i> <?php echo __($value['hierarchyAndName']); ?>
                                    </label>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </form>

        </div>

    </div>
</li>
<script>
    var filterCheckboxes;
    var categoryRadios;

    $(document).ready(function() {
        // get references to the checkboxes and radio buttons
        filterCheckboxes = $('input[name="searchFieldsNames[]"]');
        categoryRadios = $('input.form-check-input[type="radio"][name="catName"]');

        // add event listeners to the checkboxes and radio buttons
        filterCheckboxes.on('change', function() {
            console.log('filterCheckboxes.filter change');
            // save the checked values to the cookie
            saveSearchFiltersToCookie();

            setSearchFilterIcon();
        });

        categoryRadios.on('change', function() {
            // save the checked value to the cookie
            saveSearchCategoryToCookie();

            setSearchFilterIcon();
        });

        // load the saved search filters from the cookies
        const savedFilters = Cookies.get('searchFilters');
        const savedCategory = Cookies.get('searchCategory');

        if (savedFilters) {
            // parse the saved filters from JSON and check the corresponding checkboxes
            const checkedValues = JSON.parse(savedFilters);

            filterCheckboxes.each(function() {
                this.checked = checkedValues.includes(this.value);
            });
        }

        if (savedCategory) {
            // check the corresponding radio button
            categoryRadios.filter(`[value="${savedCategory}"]`).prop('checked', true);
        } else {
            // check the default radio button
            categoryRadios.filter('#search_category0').prop('checked', true);
        }
        setSearchFilterIcon();

        $('#filterButton').click(function() {
            $('#filterDropdown').toggleClass('show');
        });
    });

    function saveSearchFiltersToCookie() {
        const checkedValues = filterCheckboxes.filter(':checked').map(function() {
            return this.value;
        }).get();
        Cookies.set('searchFilters', JSON.stringify(checkedValues), {
            expires: 365,
            path: '/'
        });
    }

    function saveSearchCategoryToCookie() {
        const checkedValue = categoryRadios.filter(':checked').val();
        Cookies.set('searchCategory', checkedValue, {
            expires: 365,
            path: '/'
        });
    }

    function setSearchFilterIcon() {
        // check if no filter checkboxes are checked and search_category0 is checked
        if (filterCheckboxes.filter(':checked').length === 0 && $('#search_category0').is(':checked')) {
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