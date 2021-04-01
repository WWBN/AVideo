<?php
require_once '../../../videos/configuration.php';
AVideoPlugin::loadPlugin("UploadQuotaPlan");
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manage this plugin"));
    exit;
}
require_once $global['systemRootPath'] . 'objects/Channel.php';
require_once $global['systemRootPath'] . 'objects/playlist.php';
require_once $global['systemRootPath'] . 'plugin/CombineSites/Objects/CombineSitesDB.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <?php 
        echo getHTMLTitle( __("Combine Sites"));
        ?>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="panel panel-default">
                            <div class="panel-heading"><i class="fas fa-sitemap"></i> Sites</div>
                            <div class="panel-body">
                                <form class="form-inline" id="addSiteForm" >
                                    <div class="form-group">
                                        <input type="url" class="form-control" id="site_url" placeholder="Add site">
                                    </div>
                                    <button type="submit" class="btn btn-default">Add Site</button>
                                </form>
                                <hr>
                                <ul class="list-group">
                                    <?php
                                    $sites = CombineSitesDB::getAll();
                                    foreach ($sites as $value) {
                                        ?>
                                        <li class="list-group-item site" site="<?php echo $value['site_url']; ?>">
                                            <div class="material-switch pull-left" style="margin-right: 10px;">
                                                <input class="sitesSwitch" data-toggle="toggle" type="checkbox" combine_sites_id="<?php echo $value['id']; ?>" id="sitesSwitch<?php echo $value['id']; ?>" <?php
                                                if ($value['status'] === 'a') {
                                                    echo 'checked';
                                                }
                                                ?>>
                                                <label for="sitesSwitch<?php echo $value['id']; ?>" class="label-success"></label>
                                            </div>
                                            <?php echo $value['site_url']; ?>
                                            <div class="pull-right">
                                                <button class="btn btn-xs btn-default" onclick="combineSiteID =<?php echo $value['id']; ?>;editSite('<?php echo $value['site_url']; ?>');return false;"> <i class="fas fa-edit"></i> Edit</button>
                                                <button class="btn btn-xs btn-danger" onclick="deleteSite('<?php echo $value['id']; ?>');"> <i class="fas fa-trash"></i> Delete</button>
                                            </div>
                                        </li>
                                        <?php
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="panel panel-default">
                            <div class="panel-heading" id="permissionsHead"><i class="fas fa-lock"></i> <span id="sitesPermissions"></span> <span class="label label-success" style="display: none;">Enabled</span> <span class="label label-danger" style="display: none;">Disabled</span></div>
                            <div class="panel-body" id="sitesPermissionsBody" style="display: none;">
                                <ul class="nav nav-tabs">
                                    <li class="active"><a data-toggle="tab" href="#IWantAllow"><i class="fas fa-sign-out-alt"></i> I allow from my site</a></li>
                                    <li><a data-toggle="tab" href="#IWantGet"><i class="fas fa-sign-in-alt"></i> I want to get</a></li>
                                </ul>
                                <div class="tab-content">
                                    <div id="IWantAllow" class="tab-pane fade in active tabbable-line">
                                        <ul class="nav nav-tabs">
                                            <li class="active"><a data-toggle="tab" href="#IWantAllowchannels"><i class="fas fa-user"></i> channels</a></li>
                                            <li><a data-toggle="tab" href="#IWantAllowcategories"><i class="fas fa-list"></i> categories</a></li>
                                            <li><a data-toggle="tab" href="#IWantAllowprograms"><i class="fas fa-play-circle"></i> programs (PlayLists)</a></li>
                                        </ul>
                                        <div class="tab-content">
                                            <div id="IWantAllowchannels" class="tab-pane fade in active">
                                                <?php
                                                $channels = Channel::getChannels();
                                                ?>
                                                <ul class="list-group"> 
                                                    <?php
                                                    foreach ($channels as $value) {
                                                        ?>
                                                        <li class="list-group-item">
                                                            <div class="material-switch pull-left" style="margin-right: 10px;">
                                                                <input class="channelsSwitch" data-toggle="toggle" type="checkbox" id="givechannelsSwitch<?php echo $value['id']; ?>" value="<?php echo $value['id']; ?>">
                                                                <label for="givechannelsSwitch<?php echo $value['id']; ?>" class="label-primary"></label>
                                                            </div>
                                                            <img src="<?php echo User::getPhoto($value['id']); ?>"
                                                                 class="img img-responsive img-circle pull-left" style="max-height: 25px; margin: 0 10px;" alt="User Photo" />
                                                            <a href="<?php echo User::getChannelLink($value['id']); ?>" class="btn btn-default btn-xs">
                                                                <i class="fas fa-play-circle"></i>
                                                                <?php
                                                                echo User::getNameIdentificationById($value['id']);
                                                                ?>
                                                            </a>
                                                            <?php echo combineEditorGiveLabels("channels", $value['id']); ?>
                                                        </li>
                                                        <?php
                                                    }
                                                    ?>
                                                </ul>
                                            </div>
                                            <div id="IWantAllowcategories" class="tab-pane fade">
                                                <?php
                                                $categories = Category::getAllcategories();
                                                array_multisort(array_column($categories, 'hierarchyAndName'), SORT_ASC, $categories);
                                                ?>
                                                <ul class="list-group"> 
                                                    <?php
                                                    foreach ($categories as $value) {
                                                        ?>
                                                        <li class="list-group-item">
                                                            <div class="material-switch pull-left" style="margin-right: 10px;">
                                                                <input class="categoriesSwitch" data-toggle="toggle" type="checkbox" id="givecategoriesSwitch<?php echo $value['id']; ?>" value="<?php echo $value['id']; ?>">
                                                                <label for="givecategoriesSwitch<?php echo $value['id']; ?>" class="label-primary"></label>
                                                            </div>
                                                            <i class="<?php echo (empty($value['iconClass']) ? "fa fa-folder" : $value['iconClass']); ?>"></i>
                                                            <?php
                                                            echo $value['hierarchyAndName'];
                                                            ?>
                                                            <span class="badge"><?php
                                                                echo $value['fullTotal'];
                                                                ?></span>
                                                            <?php echo combineEditorGiveLabels("categories", $value['id']); ?>
                                                        </li>
                                                        <?php
                                                    }
                                                    ?>
                                                </ul>
                                            </div>
                                            <div id="IWantAllowprograms" class="tab-pane fade">
                                                <ul class="list-group"> 
                                                    <?php
                                                    $playlists = PlayList::getAll();
                                                    foreach ($playlists as $key => $playlist) {

                                                        $videosArrayId = PlayList::getVideosIdFromPlaylist($playlist['id']);

                                                        //getAllVideos($status = "viewable", $showOnlyLoggedUserVideos = false, $ignoreGroup = false, $videosArrayId = array(), $getStatistcs = false, $showUnlisted = false, $activeUsersOnly = true)
                                                        if (empty($videosArrayId) || $playlist['status'] == "favorite" || $playlist['status'] == "watch_later") {
                                                            unset($playlists[$key]);
                                                            continue;
                                                        }
                                                        $link = PlayLists::getLink($playlist['id']);
                                                        ?>

                                                        <li class="list-group-item">
                                                            <div class="material-switch pull-left" style="margin-right: 10px;">
                                                                <input class="programsSwitch" data-toggle="toggle" type="checkbox" id="giveprogramsSwitch<?php echo $playlist['id']; ?>" value="<?php echo $playlist['id']; ?>">
                                                                <label for="giveprogramsSwitch<?php echo $playlist['id']; ?>" class="label-primary"></label>
                                                            </div>
                                                            <img src="<?php echo User::getPhoto($playlist['users_id']); ?>"
                                                                 class="img img-responsive img-circle pull-left" style="max-height: 25px; margin: 0 10px;" alt="User Photo" />
                                                            <a href="<?php echo User::getChannelLink($playlist['users_id']); ?>" class="btn btn-default btn-xs">
                                                                <i class="fas fa-play-circle"></i>
                                                                <?php
                                                                echo User::getNameIdentificationById($playlist['users_id']);
                                                                ?>
                                                            </a>
                                                            <a href="<?php echo $link; ?>" class="btn btn-default btn-xs">
                                                                <span class="fa fa-play"></span>
                                                                <?php echo $playlist['name']; ?> 
                                                            </a>
                                                            <?php echo combineEditorGiveLabels("programs", $playlist['id']); ?>
                                                        </li>
                                                        <?php
                                                    }
                                                    ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="IWantGet" class="tab-pane fade tabbable-line">
                                        <ul class="nav nav-tabs">
                                            <li class="active"><a data-toggle="tab" href="#IWantGetchannels"><i class="fas fa-user"></i> channels</a></li>
                                            <li><a data-toggle="tab" href="#IWantGetcategories"><i class="fas fa-list"></i> categories</a></li>
                                            <li><a data-toggle="tab" href="#IWantGetprograms"><i class="fas fa-play-circle"></i> programs (PlayLists)</a></li>
                                        </ul>
                                        <div class="tab-content">
                                            <div id="IWantGetchannels" class="tab-pane fade in active">

                                                <ul class="list-group" id="IWantGetchannelsUL"> 
                                                    <li class="list-group-item hidden" id="IWantGetchannelsTemplate">
                                                        <div class="material-switch pull-left" style="margin-right: 10px;">
                                                            <input class="channelsSwitchGet" data-toggle="toggle" type="checkbox" id="getchannelsSwitch{id}" value="{id}">
                                                            <label for="getchannelsSwitch{id}" class="label-primary"></label>
                                                        </div>
                                                        <img src="{photo}"
                                                             class="img img-responsive img-circle pull-left" style="max-height: 25px; margin: 0 10px;"  alt="User Photo" />
                                                        <a href="{channelsLink}" class="btn btn-default btn-xs">
                                                            <i class="fas fa-play-circle"></i>
                                                            {name}
                                                        </a>
                                                        <?php
                                                        echo combineEditorGetLabels("channels");
                                                        ?>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div id="IWantGetcategories" class="tab-pane fade">
                                                <ul class="list-group" id="IWantGetcategoriesUL"> 
                                                    <li class="list-group-item hidden" id="IWantGetcategoriesTemplate">
                                                        <div class="material-switch pull-left" style="margin-right: 10px;">
                                                            <input class="categoriesSwitchGet" data-toggle="toggle" type="checkbox" id="getcategoriesSwitch{id}" value="{id}">
                                                            <label for="getcategoriesSwitch{id}" class="label-primary"></label>
                                                        </div>
                                                        <i class="{iconClass}"></i>
                                                        {hierarchyAndName}
                                                        <span class="badge">{fullTotal}</span>
                                                        <?php
                                                        echo combineEditorGetLabels("categories");
                                                        ?>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div id="IWantGetprograms" class="tab-pane fade">
                                                <ul class="list-group" id="IWantGetprogramsUL"> 
                                                    <li class="list-group-item hidden" id="IWantGetprogramsTemplate">
                                                        <div class="material-switch pull-left" style="margin-right: 10px;">
                                                            <input class="programsSwitchGet" data-toggle="toggle" type="checkbox" id="getprogramsSwitch{id}" value="{id}">
                                                            <label for="getprogramsSwitch{id}" class="label-primary"></label>
                                                        </div>
                                                        <img src="{photo}"
                                                             class="img img-responsive img-circle pull-left" style="max-height: 25px; margin: 0 10px;"  alt="User Photo" />
                                                        <a href="{channelsLink}" class="btn btn-default btn-xs">
                                                            <i class="fas fa-play-circle"></i>
                                                            {username}
                                                        </a>
                                                        <a href="{link}" class="btn btn-default btn-xs">
                                                            <span class="fa fa-play"></span>
                                                            {name}
                                                        </a>
                                                        <?php
                                                        echo combineEditorGetLabels("programs");
                                                        ?>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
        <script>
            var combineSiteURL = "";
            var combineSitePermissions = [];
            var combineSiteID = "";

            function checkSiteInfo() {
                modal.showPleaseWait();
                $('#permissionsHead .label').fadeOut();
                combineSitePermissions = [];
                $.ajax({
                    url: combineSiteURL + 'plugin/CombineSites/siteInfo.json.php?site_url=<?php echo urlencode($global['webSiteRootURL']); ?>',
                    success: function (response) {
                        if (!response.error) {
                            if (!response.site_is_enabled) {
                                $('#permissionsHead .label-danger').fadeIn();
                                avideoAlert("Sorry!", "Site " + combineSiteURL + " needs to enable your site  before you get content from them ", "warning");
                            } else {
                                $('#permissionsHead .label-success').fadeIn();
                            }

                            $('#sitesPermissions').html(" <img src='" + response.site_logo + "' class='img img-responsive pull-left' style='max-height:20px;' /> " + response.site_title);
                            checkItemGive("channels", "users_id");
                            checkItemGive("categories", "categories_id");
                            checkItemGive("programs", "playlists_id");
                            loadItemGet("channels", "users_id");
                            loadItemGet("categories", "categories_id");
                            loadItemGet("programs", "playlists_id");
                        } else {
                            $("#sitesPermissionsBody").slideUp();
                            avideoAlert("Sorry!", "Site " + combineSiteURL + " said: " + response.msg, "error");
                        }
                        modal.hidePleaseWait();
                    },
                    error: function (ajaxContext) {
                        $("#sitesPermissionsBody").slideUp();
                        avideoAlert("Sorry!", "This site is NOT a streamer Site", "error");
                        modal.hidePleaseWait();
                    }
                });
            }

            function isConnectedGet(name, id, mode) {
                return $('#' + name + 'Connected' + mode + id).is(":visible");
            }
            function isRequestedGet(name, id, mode) {
                return $('#' + name + 'Requested' + mode + id).is(":visible");
            }
            function isPreApprovedGet(name, id, mode) {
                return $('#' + name + 'PreApproved' + mode + id).is(":visible");
            }

            function processLabelCheck(name, id, isChecked, mode, isApproved) {
                if (isNaN(id)) {
                    return false;
                }
                name = name.replace(/\./g, '');
                name = name.replace(/SwitchGet/g, '');
                name = name.replace(/Switch/g, '');
                var isConnected = isConnectedGet(name, id, mode);
                var isRequested = isRequestedGet(name, id, mode);
                var isPreApproved = isPreApprovedGet(name, id, mode);
                $('.' + name + mode + 'Label' + id).hide();
                if (isChecked) {
                    if (mode == 'Give') {
                        if (isApproved || isRequested || isPreApproved || isConnected) {
                            $('#' + name + 'Connected' + mode + id).fadeIn();
                        } else {
                            $('#' + name + 'PreApproved' + mode + id).fadeIn();
                        }
                    } else { // Get
                        if (isApproved || isRequested || isPreApproved || isConnected) {
                            $('#' + name + 'Connected' + mode + id).fadeIn();
                        } else {
                            $('#' + name + 'Requested' + mode + id).fadeIn();
                        }
                    }
                } else {
                    if (mode == 'Give') {
                        if (isApproved || isConnected) {
                            $('#' + name + 'Requested' + mode + id).fadeIn();
                        }
                    } else { // Get
                        if (isApproved || isPreApproved || isConnected) {
                            $('#' + name + 'PreApproved' + mode + id).fadeIn();
                        }
                    }
                }
            }

            function labelGetApproved(name, item) {
                $.ajax({
                    url: combineSiteURL + "plugin/CombineSites/page/give/checked.json.php?site_url=<?php echo urlencode($global['webSiteRootURL']); ?>&type=" + item,
                    success: function (data) {
                        if (data.error) {
                            avideoAlert("Sorry!", data.msg, "error");
                            modal.hidePleaseWait();
                        } else {
                            for (x in data.response) {
                                eval("var id = data.response[x]." + item + ";");
                                var isChecked = $('#get' + name + 'Switch' + id).is(':checked');
                                processLabelCheck(name, id, isChecked, 'Get', true);
                            }
                        }
                    }
                });
            }

            function checkItemGet(name, item) {
                $("#getLabels" + name + " .label").hide();
                $('.' + name + 'Switch').prop('checked', false);
                $.ajax({
                    url: "<?php echo $global['webSiteRootURL']; ?>plugin/CombineSites/page/get/checked.json.php?site_url=" + encodeURIComponent(combineSiteURL) + "&type=" + item,
                    success: function (data) {
                        if (data.error) {
                            avideoAlert("Sorry!", data.msg, "error");
                            modal.hidePleaseWait();
                        } else {
                            for (x in data.response) {
                                eval("var id = data.response[x]." + item + ";");
                                if (!isNaN(id)) {
                                    $('#get' + name + 'Switch' + id).prop('checked', true);
                                    processLabelCheck(name, id, true, 'Get', false);
                                }
                            }
                            labelGetApproved(name, item);
                        }
                    }
                });
            }

            function loadItemGet(name, item) {
                modal.showPleaseWait();
                var li = $("#IWantGet" + name + "Template").clone();
                $("#IWantGet" + name + "UL").empty();
                $("#IWantGet" + name + "UL").append(li);
                $.ajax({
                    url: combineSiteURL + 'plugin/API/get.json.php?APIName=' + name,
                    success: function (response) {
                        if (!response.error) {
                            for (x in response.response) {
                                if (typeof response.response[x] !== 'object') {
                                    continue;
                                }
                                li = $("#IWantGet" + name + "Template").clone();
                                li.attr("id", "");
                                li.removeClass("hidden");
                                var liString = li.prop('outerHTML');
                                eval("liString = _replace_" + name + "(liString, response);");
                                $("#IWantGet" + name + "UL").append(liString);
                            }
                            checkItemGet(name, item);
                            $("#sitesPermissionsBody").slideDown();
                            _switchItem("." + name + "SwitchGet", "plugin/CombineSites/page/get/" + name + "Switch.json.php", "Get");
                        } else {
                            $("#sitesPermissionsBody").slideUp();
                            avideoAlert("Sorry!", "Site " + combineSiteURL + " said: " + response.message, "error");
                        }
                        modal.hidePleaseWait();
                    },
                    error: function (ajaxContext) {
                        $("#sitesPermissionsBody").slideUp();
                        avideoAlert("Sorry!", "This site is NOT a streamer Site", "error");
                        modal.hidePleaseWait();
                    }
                });
            }

            function labelGetRequested(name, item) {
                $.ajax({
                    url: combineSiteURL + "plugin/CombineSites/page/get/checked.json.php?site_url=<?php echo urlencode($global['webSiteRootURL']); ?>&type=" + item,
                    success: function (data) {
                        if (data.error) {
                            avideoAlert("Sorry!", data.msg, "error");
                            modal.hidePleaseWait();
                        } else {
                            for (x in data.response) {
                                eval("var id = data.response[x]." + item + ";");
                                if (!isNaN(id)) {
                                    var isChecked = $('#give' + name + 'Switch' + id).is(':checked');
                                    processLabelCheck(name, id, isChecked, "Give", true);
                                }
                            }
                        }
                    }
                });
            }

            function checkItemGive(name, item) {
                $('.' + name + 'Switch').prop('checked', false);
                $.ajax({
                    url: "<?php echo $global['webSiteRootURL']; ?>plugin/CombineSites/page/give/checked.json.php?site_url=" + encodeURIComponent(combineSiteURL) + "&type=" + item,
                    success: function (data) {
                        if (data.error) {
                            avideoAlert("Sorry!", data.msg, "error");
                            modal.hidePleaseWait();
                        } else {
                            for (x in data.response) {
                                eval("var id = data.response[x]." + item + ";");
                                if (!isNaN(id)) {
                                    $('#give' + name + 'Switch' + id).prop('checked', true);
                                    processLabelCheck(name, id, true, "Give", false);
                                }
                            }
                            labelGetRequested(name, item);
                        }
                    }
                });
            }

            function _replace_channels(liString, response) {
                liString = liString.replace(/\{id\}/gi, response.response[x].id);
                liString = liString.replace(/\{photo\}/gi, response.response[x].photo);
                liString = liString.replace(/\{channelsLink\}/gi, response.response[x].channelsLink);
                liString = liString.replace(/\{name\}/gi, response.response[x].name);
                return liString;
            }

            function _replace_categories(liString, response) {
                liString = liString.replace(/\{id\}/gi, response.response[x].id);
                liString = liString.replace(/\{iconClass\}/gi, response.response[x].iconClass);
                liString = liString.replace(/\{hierarchyAndName\}/gi, response.response[x].hierarchyAndName);
                liString = liString.replace(/\{fullTotal\}/gi, response.response[x].fullTotal);
                return liString;
            }

            function _replace_programs(liString, response) {
                liString = liString.replace(/\{id\}/gi, response.response[x].id);
                liString = liString.replace(/\{photo\}/gi, response.response[x].photo);
                liString = liString.replace(/\{channelsLink\}/gi, response.response[x].channelsLink);
                liString = liString.replace(/\{name\}/gi, response.response[x].name);
                liString = liString.replace(/\{username\}/gi, response.response[x].username);
                liString = liString.replace(/\{link\}/gi, response.response[x].link);
                return liString;
            }
            
            function editSite(site) {
                $(".site").removeClass("active");
                $("li[site='" + site + "']").addClass("active");
                combineSiteURL = site;
                checkSiteInfo();
                return false;
            }

            function deleteSite(id) {
                swal({
                    title: "<?php echo __("Are you sure?"); ?>",
                    text: "<?php echo __("You will not be able to recover this action!"); ?>",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                        .then(function(willDelete) {
                            if (willDelete) {
                                modal.showPleaseWait();
                                $.ajax({
                                    url: "<?php echo $global['webSiteRootURL']; ?>plugin/CombineSites/page/siteDelete.json.php?id=" + id,
                                    success: function (data) {
                                        if (data.error) {
                                            avideoAlert("Sorry!", data.msg, "error");
                                            modal.hidePleaseWait();
                                        } else {
                                            location.reload();
                                        }
                                    }
                                });
                            } else {

                            }
                        });
            }

            function _switchItem(name, url, mode) {
                $(name).change(function (e) {
                    modal.showPleaseWait();
                    e.preventDefault(); // avoid to execute the actual submit of the form.
                    var t = $(this);
                    var combine_sites_id = $(this).attr('combine_sites_id');
                    if (!combine_sites_id) {
                        combine_sites_id = combineSiteID;
                    }
                    var id = $(this).val();
                    var isChecked = $(this).is(":checked");
                    $.ajax({
                        url: "<?php echo $global['webSiteRootURL']; ?>" + url + "?combine_sites_id=" + combine_sites_id + "&status=" + (isChecked ? "a" : "i") + "&id=" + id,
                        success: function (data) {
                            if (data.error) {
                                t.prop('checked', !t.is(":checked"));
                                avideoAlert("Sorry!", data.msg, "error");
                            } else {
                                processLabelCheck(name, id, isChecked, mode, false);
                            }
                            modal.hidePleaseWait();
                        }
                    });
                });
            }
            $(document).ready(function () {
                $("#addSiteForm").submit(function (e) {
                    e.preventDefault(); // avoid to execute the actual submit of the form.
                    var site_url = $('#site_url').val();
                    if (!site_url) {
                        avideoAlert("Sorry!", "You need a valid URL", "error");
                        return false;
                    }
                    modal.showPleaseWait();
                    $.ajax({
                        url: "<?php echo $global['webSiteRootURL']; ?>plugin/CombineSites/page/addSite.json.php?site_url=" + site_url,
                        success: function (data) {
                            if (data.error) {
                                avideoAlert("Sorry!", data.msg, "error");
                                modal.hidePleaseWait();
                            } else if (data.response && data.response.error) {
                                avideoAlert("Sorry!", data.response.msg, "error");
                                modal.hidePleaseWait();
                            } else {
                                location.reload();
                            }
                        }
                    });
                });
                _switchItem(".sitesSwitch", "plugin/CombineSites/page/siteSwitch.json.php", "Give");
                _switchItem(".channelsSwitch", "plugin/CombineSites/page/give/channelsSwitch.json.php", "Give");
                _switchItem(".categoriesSwitch", "plugin/CombineSites/page/give/categoriesSwitch.json.php", "Give");
                _switchItem(".programsSwitch", "plugin/CombineSites/page/give/programsSwitch.json.php", "Give");

            });
        </script>
    </body>
</html>
<?php

function combineEditorGetLabels($name) {
    $str = '<span class="GetLabels' . $name . '">
                <span class="label label-warning  ' . $name . 'GetLabel{id}" style="display:none;" id="' . $name . 'RequestedGet{id}" ><i class="far fa-question-circle"></i> ' . __("Waiting connection approval") . '</span>
                <span class="label label-success ' . $name . 'GetLabel{id}" style="display:none;" id="' . $name . 'ConnectedGet{id}" ><i class="fas fa-link"></i> ' . __("Connected") . '</span>
                <span class="label label-primary ' . $name . 'GetLabel{id}" style="display:none;" id="' . $name . 'PreApprovedGet{id}" ><i class="fas fa-unlink"></i> ' . __("Connection pre approved") . '</span>
            </span>';
    return $str;
}

function combineEditorGiveLabels($name, $id) {
    $str = '<span class="GiveLabels' . $name . '">
                <span class="label label-warning ' . $name . 'GiveLabel' . $id . '" style="display:none;" id="' . $name . 'RequestedGive' . $id . '" ><i class="far fa-question-circle"></i> ' . __("Request to approve connection") . '</span>
                <span class="label label-success ' . $name . 'GiveLabel' . $id . '" style="display:none;" id="' . $name . 'ConnectedGive' . $id . '" ><i class="fas fa-link"></i> ' . __("Connected") . '</span>
                <span class="label label-primary ' . $name . 'GiveLabel' . $id . '" style="display:none;" id="' . $name . 'PreApprovedGive' . $id . '" ><i class="fas fa-unlink"></i> ' . __("Pre approved connection") . '</span>
            </span>';
    return $str;
}
?>