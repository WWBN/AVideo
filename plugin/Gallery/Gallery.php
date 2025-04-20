<?php
use OpenApi\Attributes as OA;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';
require_once $global['systemRootPath'] . 'objects/Channel.php';

class Gallery extends PluginAbstract
{
    const BigVideoLiveDisabled = 0;
    const BigVideoLiveShowLiveAndVOD = 1;
    const BigVideoLiveShowLiveOnly = 2;
    const BigVideoLiveShowVODOnly = 3;

    public function getTags()
    {
        return array(
            PluginTags::$RECOMMENDED,
            PluginTags::$FREE,
            PluginTags::$GALLERY,
            PluginTags::$LAYOUT,
        );
    }

    public function getDescription()
    {
        return "Make the first page works as a gallery";
    }

    public function getName()
    {
        return "Gallery";
    }

    public function getUUID()
    {
        return "a06505bf-3570-4b1f-977a-fd0e5cab205d";
    }

    public function getPluginVersion()
    {
        return "1.0";
    }

    public function getHeadCode()
    {
        global $global;
        $obj = $this->getDataObject();
        // preload image
        $js = "<script>var img1 = new Image();img1.src=\"" . ImagesPlaceHolders::getVideoPlaceholder(ImagesPlaceHolders::$RETURN_URL) . "\";</script>";
        $css = '<link href="' . getURL('plugin/Gallery/style.css') . '" rel="stylesheet" type="text/css"/>';

        if (!empty($obj->playVideoOnFullscreenOnIframe)) {
            if (canFullScreen()) {
                $css .= '<link href="' . getURL('plugin/YouPHPFlix2/view/css/fullscreen.css') . '" rel="stylesheet" type="text/css"/>';
                $css .= '<style>.container-fluid {overflow: visible;padding: 0;}#mvideo{padding: 0 !important; position: absolute; top: 0;}</style>';
                $css .= '<style>body.fullScreen{overflow: hidden;}</style>';
            }
            $js .= '<script>var playVideoOnFullscreen = true</script>';
        } else if (!empty($obj->playVideoOnFullscreen) && canFullScreen()) {
            $css .= '<link href="' . getCDN() . 'plugin/Gallery/fullscreen.css" rel="stylesheet" type="text/css"/>';
        }
        if (!empty($obj->playVideoOnFullscreen)) {
            $css .= '<style>body.fullScreen{overflow: hidden;}</style>';
        }

        return $js . $css;
    }

    public function getEmptyDataObject()
    {
        global $global;
        $obj = new stdClass();
        $obj->MainContainerFluid = true;
        $obj->hidePrivateVideos = false;
        $obj->BigVideo = true;
        $obj->showLivesAboveBigVideo = false;



        $o = new stdClass();
        $o->type = [
            Gallery::BigVideoLiveDisabled => 'Disable',
            Gallery::BigVideoLiveShowLiveAndVOD => 'Show Live and VOD',
            Gallery::BigVideoLiveShowLiveOnly => 'Show Live Only'
        ];
        $o->value = Gallery::BigVideoLiveShowLiveAndVOD;
        $obj->BigVideoLive = $o;
        self::addDataObjectHelper('BigVideoLive', 'Big Video Live');
        $obj->BigVideoLiveOnFirstPageOnly = true;
        self::addDataObjectHelper('BigVideoLiveOnFirstPageOnly', 'Big Video Live on First Page Only');
        $obj->BigVideoLiveForLoggedUsersOnly = false;
        self::addDataObjectHelper('BigVideoLiveForLoggedUsersOnly', 'Big Video Live will appear only if the user is logged');

        $obj->useSuggestedVideosAsCarouselInBigVideo = true;
        $obj->GifOnBigVideo = true;
        $obj->Description = false;
        $obj->CategoryDescription = false;

        $obj->Shorts = true;
        $obj->ShortsCustomTitle = "";
        $obj->ShortsRowCount = 12;
        $obj->ShortsOrder = 1;

        $obj->Suggested = true;
        $obj->SuggestedCustomTitle = "";
        $obj->SuggestedRowCount = 12;
        $obj->SuggestedOrder = 1;

        $obj->PlayLists = true;
        $obj->PlayListsRowCount = 12;
        $obj->PlayListsOrder = 2;

        $obj->Trending = true;
        $obj->TrendingCustomTitle = "";
        $obj->TrendingRowCount = 12;
        $obj->TrendingOrder = 2;

        $obj->DateAdded = true;
        $obj->DateAddedCustomTitle = "";
        $obj->DateAddedRowCount = 12;
        $obj->DateAddedOrder = 3;

        $obj->PrivateContent = true;
        $obj->PrivateContentCustomTitle = "";
        $obj->PrivateContentRowCount = 12;
        $obj->PrivateContentOrder = 4;

        $obj->MostWatched = true;
        $obj->MostWatchedCustomTitle = "";
        $obj->MostWatchedRowCount = 12;
        $obj->MostWatchedOrder = 4;

        $obj->MostPopular = true;
        $obj->MostPopularCustomTitle = "";
        $obj->MostPopularRowCount = 12;
        $obj->MostPopularOrder = 5;

        $obj->SortByName = false;
        $obj->SortByNameCustomTitle = "";
        $obj->SortByNameRowCount = 12;
        $obj->SortByNameOrder = 6;

        $obj->SubscribedChannels = true;
        $obj->SubscribedChannelsRowCount = 12;
        $obj->SubscribedChannelsOrder = 7;

        $obj->SubscribedTags = true;
        $obj->SubscribedTagsRowCount = 12;
        $obj->SubscribedTagsOrder = 7;

        $obj->Categories = true;
        $obj->CategoriesCustomTitle = "";
        $obj->CategoriesRowCount = 12;
        $obj->CategoriesOrder = 7;
        $obj->CategoriesShowOnlySuggested = false;

        $obj->sortReverseable = false;
        $obj->SubCategorys = false;
        self::addDataObjectHelper('SubCategorys', 'Show Subcategories', 'When you are in the categories page, show the subcategories link at the top');
        $obj->showTags = true;
        $obj->showCategoryTag = true;
        $obj->showCategoryLiveRow = false;
        $obj->searchOnChannels = true;
        $obj->searchOnChannelsRowCount = 12;
        $obj->playVideoOnFullscreen = false;
        $obj->playVideoOnFullscreenOnIframe = false;
        $obj->playVideoOnBrowserFullscreen = false;
        $obj->filterUserChannel = false;
        $obj->screenColsLarge = 6;
        $obj->screenColsMedium = 3;
        $obj->screenColsSmall = 2;
        $obj->screenColsXSmall = 1;
        $obj->allowSwitchTheme = true;
        self::addDataObjectHelper('allowSwitchTheme', 'Show Switch theme button');
        $themes = getThemes();
        foreach ($themes as $value) {
            $name = ucfirst($value);
            eval('$obj->SwitchThemeShow' . $name . ' = true;');
            self::addDataObjectHelper('SwitchThemeShow' . $name, 'Show ' . $name . ' Option', 'Uncheck this button to not show the ' . $name . ' in your themes list');
            eval('$obj->SwitchThemeLabel' . $name . ' = "' . $name . '";');
            self::addDataObjectHelper('SwitchThemeLabel' . $name, $name . ' Theme Label', 'Change the label name to the theme ' . $name . ' in your themes list');
        }

        return $obj;
    }

    public function navBarProfileButtons()
    {
        global $global;
        $navBarButtons = 0;
        $obj = $this->getDataObject();
        if ($obj->allowSwitchTheme) {
            include $global['systemRootPath'] . 'plugin/Gallery/view/themeSwitcher.php';
        }
    }

    public function navBarButtons()
    {
        global $global;
        $navBarButtons = 1;
        $obj = $this->getDataObject();
        if (!empty($obj->allowSwitchTheme)) {
            include $global['systemRootPath'] . 'plugin/Gallery/view/themeSwitcher.php';
        }
    }

    public function getHelp()
    {
        if (User::isAdmin()) {
            return "<h2 id='Gallery help'>" . __('Gallery options (admin)') . "</h2><table class='table'><thead><th>" . __('Option-name') . "</th><th>" . __('Default') . "</th><th>" . __('Description') . "</th></thead><tbody><tr><td>BigVideo</td><td>" . __('checked') . "</td><td>" . __('Create a big preview with a direct description on top') . "</td></tr><tr><td>DateAdded,MostPopular,MostWatched,SortByName</td><td>" . __('checked') . "," . __('checked') . "," . __('checked') . "," . __('unchecked') . "</td><td>" . __('Metacategories') . "</td></tr><tr><td>SubCategorys</td><td>" . __('unchecked') . "</td> <td>" . __('Enable a view for subcategories on top') . "</td></tr><tr><td>Description</td><td>" . __('unchecked') . "</td><td>" . __('Enable a small button for show the description') . "</td></tr></tbody></table>";
        }
        return "";
    }

    public function getFirstPage()
    {
        global $global;
        if (!AVideoPlugin::isEnabledByName("YouPHPFlix2")) {
            return $global['systemRootPath'] . 'plugin/Gallery/view/modeGallery.php';
        }
    }

    public function getFooterCode()
    {
        $obj = $this->getDataObject();
        global $global;

        $js = '';
        if (!empty($obj->playVideoOnFullscreenOnIframe)) {
            $js = '<script src="' . getURL('plugin/YouPHPFlix2/view/js/fullscreen.js') . '"></script>';
            $js .= '<script>$(function () { if(typeof linksToFullscreen === \'function\'){ linksToFullscreen(\'a.galleryLink\'); } });</script>';
        } else
        if (!empty($obj->playVideoOnFullscreen)) {
            $js = '<script src="' . getURL('plugin/YouPHPFlix2/view/js/fullscreen.js') . '"></script>';
            $js .= '<script>$(function () { if(typeof linksToEmbed === \'function\'){ linksToEmbed(\'a.galleryLink\'); } });</script>';
        } else
        if (!empty($obj->playVideoOnBrowserFullscreen)) {
            $js = '<script src="' . getURL('plugin/YouPHPFlix2/view/js/fullscreen.js') . '"></script>';
            $js .= '<script>$(function () { if(typeof linksToEmbed === \'function\'){ linksToEmbed(\'a.galleryLink\'); } });</script>';
            $js .= '<script src="' . getURL('plugin/Gallery/fullscreen.js') . '"></script>';
            $js .= '<script>var playVideoOnBrowserFullscreen = 1;</script>';
        }
        $js .= '<script src="' . getURL('plugin/Gallery/script.js') . '" type="text/javascript"></script>';
        return $js;
    }

    static function getThemes()
    {
        $obj = AVideoPlugin::getDataObject("Gallery");
        if (empty($obj->allowSwitchTheme)) {
            return false;
        }
        $themes = getThemes();
        $selectedThemes = array();
        foreach ($themes as $value) {
            $name = ucfirst($value);
            eval('$t = $obj->SwitchThemeShow' . $name . ';');
            if (!empty($t)) {
                $l = '';
                eval('$l = $obj->SwitchThemeLabel' . $name . ';');
                $selectedThemes[] = array('name' => $value, 'label' => $l);
            }
        }
        return $selectedThemes;
    }

    static function getSectionsOrder()
    {
        $obj = AVideoPlugin::getObjectData('Gallery');
        $sections = array();
        foreach ($obj as $key => $value) {
            if (preg_match('/(.*)Order$/', $key, $matches)) {
                $index = $value;
                while (isset($sections[$index])) {
                    $index++;
                }
                $sections[$index] = array('name' => $matches[1], 'active' => $obj->{$matches[1]});
            }
        }
        ksort($sections);
        return $sections;
    }

    public function getPluginMenu()
    {
        global $global;
        return '<button onclick="avideoModalIframeSmall(webSiteRootURL+\'plugin/Gallery/view/sections.php\')" class="btn btn-primary btn-sm btn-xs btn-block"><i class="fas fa-sort-numeric-down"></i> ' . __('Sort Sections') . '</button>';
    }

    public static function getAddChannelToGalleryButton($users_id)
    {
        global $global, $config;
        $filePath = $global['systemRootPath'] . 'plugin/Gallery/buttonChannelToGallery.php';
        $varsArray = array('users_id' => $users_id);
        $button = getIncludeFileContent($filePath, $varsArray);
        return $button;
    }

    public static function setAddChannelToGallery($users_id, $add)
    {
        global $global, $config;
        $users_id = intval($users_id);
        $add = intval($add);

        $obj = AVideoPlugin::getObjectData('Gallery');

        $parameterName = "Channel_{$users_id}_";

        if (!empty($add)) {
            $obj->{$parameterName} = true;
            $obj->{"{$parameterName}CustomTitle"} = User::getNameIdentificationById($users_id);
            $obj->{"{$parameterName}RowCount"} = 12;
            $obj->{"{$parameterName}Order"} = 1;
        } else {
            unset($obj->{$parameterName});
            unset($obj->{"{$parameterName}CustomTitle"});
            unset($obj->{"{$parameterName}RowCount"});
            unset($obj->{"{$parameterName}Order"});
        }
        return AVideoPlugin::setObjectData('Gallery', $obj);
    }

    public static function isChannelToGallery($users_id)
    {
        $obj = AVideoPlugin::getObjectData('Gallery');

        $parameterName = "Channel_{$users_id}_";

        return !empty($obj->{$parameterName});
    }

    static function getVideoDropdownMenu($videos_id)
    {
        global $global;
        $varsArray = array('videos_id' => $videos_id);
        $filePath = $global['systemRootPath'] . 'plugin/Gallery/view/videoDropDownMenu.php';
        return getIncludeFileContent($filePath, $varsArray);
    }

    static function getContaierClass($addClass = '')
    {
        global $global, $objGallery;
        if (!isset($objGallery)) {
            $objGallery = AVideoPlugin::getObjectData('Gallery');
        }

        $class = 'container gallery';
        if ($objGallery->MainContainerFluid) {
            $class = 'container-fluid gallery';
        }

        return $class . " {$addClass}";
    }

    /**
     * @param string $parameters
     * This will return the configuration of the first page, also the URL to retreive the videos list from each section
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIPlugin={APIPlugin}&APIName={APIName}
     * @return \ApiObject
     */
    #[OA\Get(
        path: "/api/Gallery/firstPage",
        summary: 'Get the homepage configuration and video list endpoints',
        description: 'Returns homepage sections, each with an endpoint to retrieve a video list. Sections include Suggested, Trending, Shorts, Date Added, etc.',
        tags: ['Gallery'],
        security: [['APISecret' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response with the first page configuration and endpoints',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'error',
                            type: 'boolean',
                            example: false
                        ),
                        new OA\Property(
                            property: 'msg',
                            type: 'string',
                            example: ''
                        ),
                        new OA\Property(
                            property: 'response',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'sections',
                                    type: 'array',
                                    items: new OA\Items(
                                        type: 'object',
                                        properties: [
                                            new OA\Property(property: 'name', type: 'string'),
                                            new OA\Property(property: 'title', type: 'string'),
                                            new OA\Property(property: 'endpoint', type: 'string'),
                                            new OA\Property(property: 'totalRows', type: 'integer')
                                        ]
                                    )
                                ),
                                new OA\Property(property: 'countSections', type: 'integer', example: 5),
                                new OA\Property(property: 'countVideos', type: 'integer', example: 25),
                                new OA\Property(property: 'responseTime', type: 'number', format: 'float', example: 0.123),
                                new OA\Property(property: 'responseCacheTime', type: 'number', format: 'float', example: 0.002)
                            ]
                        )
                    ]
                )
            )
        ]
    )]

    static function API_get_firstPage($parameters){
        global $global;
        $start = microtime(true);
        $cacheName = 'Gallery_API_get_firstPage_'.md5(json_encode($parameters)).'_'.User::getId();
        $object = ObjectYPT::getCache($cacheName, 3600); // 1 hour
        if(empty($object)){
            $obj = AVideoPlugin::getObjectData("Gallery");
            $sections = Gallery::getSectionsOrder();
            $response = new stdClass();
            $response->type = 'Gallery';
            $response->sections = array();
            $countSections = 0;
            $countVideos = 0;
            foreach ($sections as $value) {
                if (empty($value['active'])) {
                    continue;
                }
                $countSections++;
                if (preg_match('/Channel_([0-9]+)_/', $value['name'], $matches)) {
                    $users_id = intval($matches[1]);
                    $title = User::_getUserChannelName($users_id);
                    $rowCount = getRowCount();
                    $endpoint = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=video&channelName={$title}";
                    $section = new SectionFirstPage($value['name'], $title, $endpoint, $rowCount);
                    $countVideos += $section->totalRows;
                    $response->sections[] = $section;
                } else
                if ($value['name'] == 'Suggested') {

                    $title = !empty($obj->SuggestedCustomTitle) ? $obj->SuggestedCustomTitle : __("Suggested");
                    $rowCount = intval($obj->SuggestedRowCount);
                    $endpoint = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=video&sort[suggested]=1";
                    $section = new SectionFirstPage($value['name'], $title, $endpoint, $rowCount);
                    $countVideos += $section->totalRows;
                    $response->sections[] = $section;
                } else
                if ($value['name'] == 'Trending') {
                    $title = !empty($obj->TrendingCustomTitle) ? $obj->TrendingCustomTitle : __("Trending");
                    $rowCount = intval($obj->TrendingRowCount);
                    $endpoint = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=video&sort[trending]=1";
                    $section = new SectionFirstPage($value['name'], $title, $endpoint, $rowCount);
                    $countVideos += $section->totalRows;
                    $response->sections[] = $section;
                } else
                if ($value['name'] == 'SortByName') {
                    $title = !empty($obj->SortByNameCustomTitle) ? $obj->SortByNameCustomTitle : __("Sort by name");
                    $rowCount = intval($obj->SortByNameRowCount);
                    $endpoint = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=video&sort[name]=asc";
                    $section = new SectionFirstPage($value['name'], $title, $endpoint, $rowCount);
                    $countVideos += $section->totalRows;
                    $response->sections[] = $section;
                } else
                if ($value['name'] == 'Shorts' && empty($_REQUEST['catName']) && AVideoPlugin::isEnabledByName('Shorts')) {
                    $title = !empty($obj->ShortsCustomTitle) ? $obj->ShortsCustomTitle : __("Shorts");
                    $rowCount = intval($obj->ShortsRowCount);
                    $endpoint = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=video&sort[shorts]=1";
                    $section = new SectionFirstPage($value['name'], $title, $endpoint, $rowCount);
                    $countVideos += $section->totalRows;
                    $response->sections[] = $section;
                } else
                if ($value['name'] == 'DateAdded' && empty($_REQUEST['catName'])) {
                    $title = !empty($obj->DateAddedCustomTitle) ? $obj->DateAddedCustomTitle : __("Date added");
                    $rowCount = intval($obj->DateAddedRowCount);
                    $endpoint = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=video&sort[created]=desc";
                    $section = new SectionFirstPage($value['name'], $title, $endpoint, $rowCount);
                    $countVideos += $section->totalRows;
                    $response->sections[] = $section;
                } else
                if ($value['name'] == 'PrivateContent') {
                    /*
                    $title = !empty($obj->PrivateContentCustomTitle) ? $obj->PrivateContentCustomTitle : __("Private Content");
                    $rowCount = intval($obj->PrivateContentRowCount);
                    $endpoint = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=video&sort[created]=asc";
                    $response->sections[] = new Section($title,$endpoint,$rowCount);
                    createGallery(!empty($obj->PrivateContentCustomTitle) ? $obj->PrivateContentCustomTitle : __("Private Content"), 'created', $obj->PrivateContentRowCount, 'privateContentOrder', __("Most"), __("Fewest"), $orderString, "DESC", true, "fas fa-lock");
                    */
                } else
                if ($value['name'] == 'MostWatched') {
                    $title = !empty($obj->MostWatchedCustomTitle) ? $obj->MostWatchedCustomTitle : __("Most watched");
                    $rowCount = intval($obj->MostWatchedRowCount);
                    $endpoint = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=video&sort[views_count]=desc";
                    $section = new SectionFirstPage($value['name'], $title, $endpoint, $rowCount);
                    $countVideos += $section->totalRows;
                    $response->sections[] = $section;
                } else
                if ($value['name'] == 'MostPopular') {
                    $title = !empty($obj->MostPopularCustomTitle) ? $obj->MostPopularCustomTitle : __("Most popular");
                    $rowCount = intval($obj->MostPopularRowCount);
                    $endpoint = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=video&sort[likes]=desc";
                    $section = new SectionFirstPage($value['name'], $title, $endpoint, $rowCount);
                    $countVideos += $section->totalRows;
                    $response->sections[] = $section;
                } else
                if ($value['name'] == 'SubscribedChannels' && User::isLogged() && AVideoPlugin::isEnabledByName('VideoTags')) {
                    require_once $global['systemRootPath'] . 'objects/subscribe.php';
                    $users_id = User::getId();
                    $channels = Subscribe::getSubscribedChannels($users_id, $obj->SubscribedChannelsRowCount, 1);
                    foreach ($channels as $value2) {
                        $title = $value2['channelName'];
                        $rowCount = $obj->SubscribedChannelsRowCount;
                        $endpoint = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=video&channelName={$title}&sort[created]=desc";
                        $section = new SectionFirstPage($value['name'], $title, $endpoint, $rowCount);
                        $countVideos += $section->totalRows;
                        $response->sections[] = $section;
                    }
                } else
                if ($value['name'] == 'SubscribedTags' && User::isLogged()) {
                    $users_id = User::getId();
                    $tags = Tags_subscriptions::getAllFromUsers_id($users_id);
                    if (!empty($tags)) {
                        foreach ($tags as $value2) {
                            $title = $value2['name'];
                            $rowCount = $obj->SubscribedTagsRowCount;
                            $endpoint = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=video&tags_id={$value2['id']}&sort[created]=desc";
                            $section = new SectionFirstPage($value['name'], $title, $endpoint, $rowCount);
                            $countVideos += $section->totalRows;
                            $response->sections[] = $section;
                        }
                    }
                } else
                if ($value['name'] == 'Categories') {
                    $onlySuggested = $obj->CategoriesShowOnlySuggested;
                    cleanSearchVar();
                    $categories = Category::getAllCategories(false, true, $onlySuggested);
                    reloadSearchVar();
                    foreach ($categories as $value2) {
                        $title = $value2['name'];
                        $rowCount = $obj->CategoriesRowCount;
                        $endpoint = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=video&catName={$value2['clean_name']}&sort[created]=desc";
                        $section = new SectionFirstPage($value['name'], $title, $endpoint, $rowCount);
                        $countVideos += $section->totalRows;
                        $response->sections[] = $section;
                    }
                }
            }
            $response->countVideos = $countVideos;
            $response->countSections = $countSections;


            $finish = microtime(true)-$start;
            $response->responseTime = $finish;

            $object = new ApiObject("", false, $response);

            ObjectYPT::setCache($cacheName, $object);
        }else{
            $finish = microtime(true)-$start;
            $object->response->responseCacheTime = $finish;
        }

        return $object;
    }
}

