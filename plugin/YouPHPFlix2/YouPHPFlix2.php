<?php
use OpenApi\Attributes as OA;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';
require_once $global['systemRootPath'] . 'plugin/AVideoPlugin.php';
class YouPHPFlix2 extends PluginAbstract
{

    public function getTags()
    {
        return array(
            PluginTags::$NETFLIX,
            PluginTags::$FREE,
        );
    }
    public function getDescription()
    {
        $txt = "Make the first page looks like a Netflix site";
        $help = "<br><small><a href='https://github.com/WWBN/AVideo/wiki/Configure-a-Netflix-Clone-Page' target='_blank'><i class='fas fa-question-circle'></i> Help</a></small>";
        return $txt . $help;
    }

    public function getName()
    {
        return "YouPHPFlix2";
    }

    public function getUUID()
    {
        return "e3a568e6-ef61-4dcc-aad0-0109e9be8e36";
    }

    public function getPluginVersion()
    {
        return "1.0";
    }

    public function getEmptyDataObject()
    {
        global $global;
        $obj = new stdClass();
        $obj->hidePrivateVideos = false;
        $obj->pageDots = true;
        $obj->Suggested = true;
        $obj->SuggestedCustomTitle = 'Suggested';
        $obj->SuggestedAutoPlay = true;
        $obj->PlayList = true;
        $obj->PlayListAutoPlay = true;
        $obj->Favorites = true;
        $obj->WatchLater = true;
        $obj->Channels = true;
        $obj->ChannelsAutoPlay = true;
        $obj->Trending = true;
        $obj->TrendingCustomTitle = 'Trending';
        $obj->TrendingAutoPlay = true;
        $obj->DateAdded = true;
        $obj->DateAddedCustomTitle = 'Date added (newest)';
        $obj->DateAddedAutoPlay = true;
        $obj->MostPopular = true;
        $obj->MostPopularCustomTitle = "Most popular";
        $obj->MostPopularAutoPlay = true;
        $obj->MostWatched = true;
        $obj->MostWatchedCustomTitle = 'Most watched';
        $obj->MostWatchedAutoPlay = true;
        $obj->SortByName = false;
        $obj->SortByNameCustomTitle = 'Alphabetical';
        $obj->SortByNameAutoPlay = true;
        $obj->Categories = true;
        $obj->CategoriesShowOnlySuggested = false;
        $obj->CategoriesAutoPlay = true;
        $obj->maxVideos = 20;
        $obj->BigVideo = true;
        $obj->RemoveBigVideoDescription = false;
        $obj->BigVideoPlayIcon = true;
        $obj->BigVideoMarginBottom = "-350px";
        $obj->backgroundRGB = "20,20,20";
        $obj->landscapePosters = true;
        $obj->playVideoOnFullscreen = true;
        $obj->playVideoOnFullscreenOnIframe = true;
        $obj->youtubeModeOnFullscreen = false;
        $obj->paidOnlyLabelOverPoster = false;
        $obj->titleLabel = true;
        $obj->titleLabelOverPoster = false;
        $obj->titleLabelCSS = "";
        $obj->hidePlayButtonIfCannotWatch = false;
        $obj->doNotShowSeriesInfoOnMainPage = false;
        $obj->useGalleryModeOnCategory = true;
        return $obj;
    }

    public function getHelp()
    {
        if (User::isAdmin()) {
            return "<h2 id='YouPHPFlix help'>YouPHPFlix options (admin)</h2><table class='table'><thead><th>Option-name</th><th>Default</th><th>Description</th></thead><tbody><tr><td>DefaultDesign</td><td>checked</td><td>The original style, for each category, one row with the newest videos</td></tr><tr><td>DateAdded,MostPopular,MostWatched,SortByName</td><td>checked,checked,checked,unchecked</td><td>Metacategories</td></tr><tr><td>LiteDesign</td><td>unchecked</td> <td>All categories in one row</td></tr><tr><td>separateAudio</td><td>unchecked</td><td>Create a own row for audio</td></tr></tbody></table>";
        }
        return "";
    }

    public function getFirstPage()
    {
        global $global;

        if (!empty($_REQUEST['catName'])) {
            $obj = $this->getDataObject();
            if ($obj->useGalleryModeOnCategory) {
                return $global['systemRootPath'] . 'plugin/Gallery/view/modeGallery.php';
            }
        }

        return $global['systemRootPath'] . 'plugin/YouPHPFlix2/view/modeFlix.php';
    }

    public function getHeadCode()
    {
        global $global, $isEmbed;
        $obj = $this->getDataObject();
        $baseName = basename($_SERVER["SCRIPT_FILENAME"]);
        if ($baseName == 'channel.php') {
            return "";
        }
        $css = "";
        //$css .= "<link href=\"".getCDN()."view/css/custom/".$obj->theme.".css\" rel=\"stylesheet\" type=\"text/css\"/>";
        $css .= "<link href=\"" . getURL("plugin/YouPHPFlix2/view/css/style.css") . "\" rel=\"stylesheet\" type=\"text/css\"/>";
        if (!empty($obj->youtubeModeOnFullscreen) && canFullScreen()) {
            $isEmbed = 1;
            $css .= '<link href="' . getCDN() . 'plugin/YouPHPFlix2/view/css/fullscreen.css" rel="stylesheet" type="text/css"/>';
            $css .= '<style>.container-fluid {overflow: visible;padding: 0;}#mvideo{padding: 0 !important; position: absolute; top: 0;}</style>';
            $css .= '<style>body.fullScreen{overflow: hidden;}</style>';
        }
        return $css;
    }

    static function getLinkToVideo($videos_id, $ignoreEmbed = false)
    {
        $obj = AVideoPlugin::getObjectData("YouPHPFlix2");
        $link = Video::getLinkToVideo($videos_id);
        if (!empty($obj->playVideoOnFullscreen)) {
            if (!Video::isSerie($videos_id) && empty($ignoreEmbed)) {
                $link = parseVideos($link, 1, 0, 0, 0, 1);
            }
        }
        return $link;
    }

    public function getFooterCode()
    {
        $obj = $this->getDataObject();
        global $global;

        $js = '';

        if (!empty($obj->playVideoOnFullscreenOnIframe) && !isSerie()) {
            $js .= '<script>$(function () { if(typeof linksToFullscreen === \'function\'){ linksToFullscreen(\'a.galleryLink\'); } });</script>';
            $js .= '<script>var playVideoOnFullscreen = 1;</script>';
        } else
        if (!empty($obj->playVideoOnFullscreen) && !isSerie()) {
            $js .= '<script>$(function () { if(typeof linksToEmbed === \'function\'){ linksToEmbed(\'a.galleryLink\'); } });</script>';
            $js .= '<script>var playVideoOnFullscreen = 2;</script>';
        } else {
            $js .= '<script>var playVideoOnFullscreen = false;</script>';
        }
        if(!empty($global['channelToYouPHPFlix2'])){
            $js .= '<script src="' . getURL('plugin/YouPHPFlix2/view/js/addChannel.js') . '"></script>';
        }
        $js .= '<script src="' . getURL('plugin/YouPHPFlix2/view/js/fullscreen.js') . '"></script>';
        return $js;
    }

    /**
     * @param string $parameters
     * This will return the configuration of the first page, also the URL to retreive the videos list from each section
     * @example {webSiteRootURL}plugin/API/{getOrSet}.json.php?APIPlugin={APIPlugin}&APIName={APIName}
     * @return \ApiObject
     */
    #[OA\Get(
        path: "/api/YouPHPFlix2/firstPage",
        summary: 'Get homepage sections and their corresponding video list endpoints',
        description: 'Returns configuration for the first page from the YouPHPFlix2 plugin, including sections like Suggested, Trending, Channels, Playlists, etc. Each section includes an endpoint to fetch related videos.',
        tags: ['YouPHPFlix2'],
        security: [['APISecret' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returns a list of sections for the homepage, each with a video list endpoint',
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
                                            new OA\Property(property: 'name', type: 'string', example: 'Suggested'),
                                            new OA\Property(property: 'title', type: 'string', example: 'Suggested Videos'),
                                            new OA\Property(property: 'endpoint', type: 'string', example: 'https://yourdomain.com/plugin/API/get.json.php?APIName=video&sort[suggested]=1'),
                                            new OA\Property(property: 'totalRows', type: 'integer', example: 20)
                                        ]
                                    )
                                ),
                                new OA\Property(property: 'countSections', type: 'integer', example: 6),
                                new OA\Property(property: 'countVideos', type: 'integer', example: 120),
                                new OA\Property(property: 'responseTime', type: 'number', format: 'float', example: 0.201),
                                new OA\Property(property: 'responseCacheTime', type: 'number', format: 'float', nullable: true)
                            ]
                        )
                    ]
                )
            )
        ]
    )]

    static function API_get_firstPage($parameters)
    {
        global $global;
        $start = microtime(true);
        $cacheName = 'YouPHPFlix2_API_get_firstPage_' . md5(json_encode($parameters)).'_'.User::getId();
        $object = ObjectYPT::getCacheGlobal($cacheName, 3600); // 1 hour
        if (empty($object)) {
            $obj = AVideoPlugin::getObjectData("YouPHPFlix2");

            $response = new stdClass();
            $response->type = 'Flix';
            $response->sections = array();
            $countSections = 0;
            $countVideos = 0;

            $rowCount = intval($obj->maxVideos);
            if ($obj->Suggested) {
                $countSections++;
                $title = __("Suggested");
                $endpoint = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=video&sort[suggested]=1";
                $section = new SectionFirstPage('Suggested', $title, $endpoint, $rowCount);
                $countVideos += $section->totalRows;
                $response->sections[] = $section;
            }
            if ($obj->Channels) {
                $users_id_array = VideoStatistic::getUsersIDFromChannelsWithMoreViews();
                $channels = Channel::getChannels(true, "u.id, '" . implode(",", $users_id_array) . "'");
                if (!empty($channels)) {
                    $countSections++;
                    foreach ($channels as $channel) {
                        $title = $channel["channelName"];
                        $endpoint = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=video&channelName={$title}";
                        $section = new SectionFirstPage('Channel', $title, $endpoint, $rowCount);
                        $countVideos += $section->totalRows;
                        $response->sections[] = $section;
                    }
                }
            }
            if ($obj->PlayList) {
                $plObj = AVideoPlugin::getDataObjectIfEnabled('PlayLists');
                if (!empty($plObj)) {
                    $programs = Video::getAllVideos(Video::SORT_TYPE_VIEWABLENOTUNLISTED, false, !$obj->hidePrivateVideos, array(), false, false, true, false, true);
                    cleanSearchVar();
                    if (!empty($programs)) {
                        $countSections++;
                        foreach ($programs as $serie) {
                            $title = $channel["channelName"];
                            $endpoint = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=video_from_program&playlists_id={$serie['serie_playlists_id']}";
                            $section = new SectionFirstPage('PlayList', $title, $endpoint, $rowCount);
                            $countVideos += $section->totalRows;
                            $response->sections[] = $section;
                        }
                    }
                    reloadSearchVar();
                }
            }
            if ($obj->Trending) {
                $countSections++;
                $title = __("Trending");
                $endpoint = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=video&sort[trending]=1";
                $section = new SectionFirstPage('Trending', $title, $endpoint, $rowCount);
                $countVideos += $section->totalRows;
                $response->sections[] = $section;
            }
            if ($obj->DateAdded) {
                $countSections++;
                $title =  __("Date added");
                $endpoint = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=video&sort[created]=asc";
                $section = new SectionFirstPage('DateAdded', $title, $endpoint, $rowCount);
                $countVideos += $section->totalRows;
                $response->sections[] = $section;
            }
            if ($obj->MostPopular) {
                $countSections++;
                $title = __("Most popular");
                $endpoint = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=video&sort[likes]=desc";
                $section = new SectionFirstPage('MostPopular', $title, $endpoint, $rowCount);
                $countVideos += $section->totalRows;
                $response->sections[] = $section;
            }
            if ($obj->MostWatched) {
                $countSections++;
                $title = __("Most watched");
                $endpoint = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=video&sort[views_count]=desc";
                $section = new SectionFirstPage('MostWatched', $title, $endpoint, $rowCount);
                $countVideos += $section->totalRows;
                $response->sections[] = $section;
            }
            if ($obj->SortByName) {
                $countSections++;
                $title = __("Sort by name");
                $rowCount = intval($obj->SortByNameRowCount);
                $endpoint = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=video&sort[name]=asc";
                $section = new SectionFirstPage('SortByName', $title, $endpoint, $rowCount);
                $countVideos += $section->totalRows;
                $response->sections[] = $section;
            }
            if ($obj->Categories) {
                $countSections++;
                cleanSearchVar();
                $categories = Category::getAllCategories(false, true, $obj->CategoriesShowOnlySuggested);
                reloadSearchVar();
                foreach ($categories as $value2) {
                    $type = 'Category';
                    if(!empty($value2['parentId'])){
                        $type = 'SubCategory';
                    }
                    $title = $value2['name'];
                    $endpoint = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=video&catName={$value2['clean_name']}";
                    //$endpoint = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=category&catName={$value2['clean_name']}";
                    $section = new SectionFirstPage($type, $title, $endpoint, $rowCount);
                    $countVideos += $section->totalRows;
                    $response->sections[] = $section;
                }
            }

            $response->countVideos = $countVideos;
            $response->countSections = $countSections;


            $finish = microtime(true) - $start;
            $response->responseTime = $finish;
            $object = new ApiObject("", false, $response);

            ObjectYPT::setCacheGlobal($cacheName, $object);
        } else {
            $finish = microtime(true) - $start;
            $object->response->responseCacheTime = $finish;
        }
        return $object;
    }


    public static function getAddChannelToYouPHPFlix2Button($users_id)
    {
        global $global, $config;
        $filePath = $global['systemRootPath'] . 'plugin/YouPHPFlix2/buttonChannelToYouPHPFlix2.php';
        $varsArray = array('users_id' => $users_id);
        $button = getIncludeFileContent($filePath, $varsArray);
        return $button;
    }

    public static function setAddChannelToYouPHPFlix2($users_id, $add)
    {
        global $global, $config;
        $users_id = intval($users_id);
        $add = intval($add);

        $obj = AVideoPlugin::getObjectData('YouPHPFlix2');

        $parameterName = "Channel_{$users_id}_";

        if (!empty($add)) {
            $obj->{$parameterName} = true;
            $obj->{"{$parameterName}CustomTitle"} = User::getNameIdentificationById($users_id);
            $obj->{"{$parameterName}Autoplay"} = true;
        } else {
            unset($obj->{$parameterName});
            unset($obj->{"{$parameterName}CustomTitle"});
            unset($obj->{"{$parameterName}Autoplay"});
        }
        return AVideoPlugin::setObjectData('YouPHPFlix2', $obj);
    }

    public static function isChannelToYouPHPFlix2($users_id)
    {
        $obj = AVideoPlugin::getObjectData('YouPHPFlix2');

        $parameterName = "Channel_{$users_id}_";

        return !empty($obj->{$parameterName});
    }
}
