<?php

require_once '../../videos/configuration.php';

$obj = AVideoPlugin::getObjectDataIfEnabled("ADs");
if (empty($obj)) {
    forbiddenPage(__("The plugin is disabled"));
    exit;
}

$is_admin = User::isAdmin();

if (empty($is_admin) && !ADs::canHaveCustomAds()) {
    gotToLoginAndComeBackHere(__("You can not do this"));
    exit;
}

$is_regular_user = empty($is_admin) || !empty($_REQUEST['customAds']);

if ($is_regular_user) {
    $is_regular_user = User::getId();
}

// the split is a site setting: only the site-wide ads editor changes it
$canChangeSplit = !empty($is_admin) && empty($is_regular_user);
$splitStates = [];
foreach (ADs::AdsSplitPositions as $splitPosition) {
    $splitStates[$splitPosition[2]] = ADs::isSplitEnabled($splitPosition[2]);
}
$positionsInfo = ADs::getAdsPositionsInfo();

/**
 * The positions this editor can change, desktop first, each split slot right after the banner it divides.
 */
$positions = [];
$orderedTypes = [];
foreach ([false, true] as $mobileGroup) {
    foreach (ADs::AdsPositions as $value) {
        $isMobilePosition = !empty(preg_match('/mobile/i', $value[0]));
        if ($isMobilePosition !== $mobileGroup) {
            continue;
        }
        $orderedTypes[] = $value[0];
        foreach (ADs::getSplitPositions($value[0]) as $slotType) {
            $orderedTypes[] = $slotType;
        }
    }
}
foreach ($orderedTypes as $type) {
    $settingsType = ADs::getSettingsType($type);
    $allowOption = "{$settingsType}AllowUserToModify";
    if ($is_regular_user && empty($obj->$allowOption)) {
        continue;
    }
    if ($is_regular_user && $settingsType !== $type && empty($splitStates[$settingsType])) {
        // creators only see the halves while the site uses them
        continue;
    }
    $info = isset($positionsInfo[$type]) ? $positionsInfo[$type] : [];
    $info = array_merge([
        'name' => $type,
        'device' => preg_match('/mobile/i', $type) ? 'mobile' : 'desktop',
        'pages' => [],
        'map' => [],
        'page' => 'home',
    ], $info);
    $size = ADs::getSize($type);
    $hasCode = false;
    if (empty($is_regular_user) && isset($obj->$type) && is_object($obj->$type) && isset($obj->$type->value)) {
        $hasCode = !_empty(trim((string) $obj->$type->value));
    }
    $label = ADs::getLabel($type);
    $images = ADs::getAds($type, $is_regular_user);
    $positions[$type] = [
        'type' => $type,
        'parent' => $settingsType !== $type ? $settingsType : '',
        'side' => '',
        'name' => $info['name'],
        'label' => $label !== $type ? $label : '',
        'device' => $info['device'],
        'pages' => $info['pages'],
        'map' => $info['map'],
        'page' => $info['page'],
        'notDisplayed' => !empty($info['notDisplayed']),
        'width' => intval($size['width']),
        'height' => intval($size['height']),
        'images' => count($images),
        'firstImage' => empty($images[0]['imageURL']) ? '' : $images[0]['imageURL'],
        'code' => $hasCode,
    ];
}
foreach (ADs::AdsSplitPositions as $value) {
    if (isset($positions[$value[0]])) {
        $positions[$value[0]]['side'] = $value[3];
    }
}

// "View on site": a page where the position is rendered, opened with "Show Ads Positions"
$previewURLs = [];
if (!empty($is_admin)) {
    $previewURLs['home'] = $global['webSiteRootURL'];
    $previewURLs['video'] = $global['webSiteRootURL'];
    $previewURLs['channel'] = User::getChannelLink();
    try {
        $sampleVideo = Video::getVideo('', Video::SORT_TYPE_VIEWABLENOTUNLISTED);
        if (!empty($sampleVideo['id'])) {
            $previewURLs['video'] = Video::getLink($sampleVideo['id'], $sampleVideo['clean_title']);
        }
    } catch (\Throwable $th) {
        _error_log('ADs editor: no video for View on site ' . $th->getMessage());
    }
}

function getADsEditorPreviewURL($previewURLs, $position)
{
    if (empty($previewURLs) || $position['notDisplayed'] || empty($previewURLs[$position['page']])) {
        return '';
    }
    $url = addQueryStringParameter($previewURLs[$position['page']], 'AdsDebug', 1);
    $url = addQueryStringParameter($url, 'AdsDevice', $position['device']);
    return addQueryStringParameter($url, 'AdsHighlight', $position['type']);
}

function getADsEditorSizeText($position)
{
    return "{$position['width']} &times; {$position['height']} px";
}

function getADsEditorDeviceLabel($position)
{
    if ($position['device'] === 'mobile') {
        return '<span class="label label-info"><i class="fas fa-mobile-alt" aria-hidden="true"></i> ' . __('Mobile') . '</span>';
    }
    return '<span class="label label-default"><i class="fas fa-desktop" aria-hidden="true"></i> ' . __('Desktop') . '</span>';
}

/**
 * Configured state shown in the list and on the map; editor.js keeps it current after changes.
 */
function getADsEditorStatus($position)
{
    $html = '';
    if (!empty($position['images'])) {
        $html .= '<span class="badge" title="' . __('Images') . '"><i class="fas fa-image" aria-hidden="true"></i> ' . intval($position['images']) . '</span> ';
    }
    if (!empty($position['code'])) {
        $html .= '<span class="badge" title="' . __('Ad code') . '"><i class="fas fa-code" aria-hidden="true"></i></span> ';
    }
    if (empty($html)) {
        $html = '<span class="text-muted ADsStatusEmpty">' . __('Empty') . '</span>';
    }
    return $html;
}

function getADsEditorThumb($position)
{
    $src = empty($position['firstImage']) ? '' : htmlspecialchars($position['firstImage'], ENT_QUOTES, 'UTF-8');
    return '<img class="ADsThumb' . (empty($src) ? ' hidden' : '') . '" data-type="' . $position['type'] . '" src="' . $src . '" alt="">';
}

/**
 * The header of a banner that can be split, shown on the banner and on both of its halves: the desktop
 * layout (one banner or two side by side) and a small diagram to move between the whole banner and its
 * halves. editor.js keeps the layout, the selected part and the thumbnails current.
 */
function getADsEditorSplitHeader($positions, $parent, $currentType, $canChangeSplit, $splitEnabled)
{
    $split = ADs::getSplitPositions($parent);
    if (empty($split) || !isset($positions[$parent])) {
        return '';
    }
    $isParent = $currentType === $parent;
    $html = '<div class="panel panel-info ADsSplitPanel" id="ADsSplitPanel' . $currentType . '"><div class="panel-body">';
    if ($canChangeSplit) {
        $name = 'ADsSplitMode' . $currentType;
        // the halves follow the banner's configured size (ADs::getSize())
        $wholeSize = intval($positions[$parent]['width']) . ' &times; ' . intval($positions[$parent]['height']);
        $halfType = reset($split);
        $halfSize = isset($positions[$halfType]) ? $positions[$halfType] : ADs::getSize($halfType);
        $halfSize = '2 &times; ' . intval($halfSize['width']) . ' &times; ' . intval($halfSize['height']);
        $html .= '<fieldset class="ADsSplitMode"><legend>' . __('Desktop layout') . '</legend>'
            . '<label class="radio-inline"><input type="radio" class="ADsSplitModeInput" name="' . $name . '" value="single" data-type="' . $parent . '"'
            . ($splitEnabled ? '' : ' checked') . '> ' . __('One banner') . ' <small>' . $wholeSize . ' px</small></label>'
            . '<label class="radio-inline"><input type="radio" class="ADsSplitModeInput" name="' . $name . '" value="split" data-type="' . $parent . '"'
            . ($isParent ? ' id="ADsSplitToggle' . $parent . '"' : '')
            . ($splitEnabled ? ' checked' : '') . '> ' . __('Two banners side by side') . ' <small>' . $halfSize . ' px</small></label>'
            . '<p class="help-block">' . __('Phones and tablets keep their mobile banner. Changing the layout never deletes images or code.') . '</p>'
            . '</fieldset>';
    } else {
        $html .= '<p><strong>' . __('Desktop layout') . ':</strong> '
            . '<span class="ADsWhenSplitOff">' . __('One banner') . '</span><span class="ADsWhenSplitOn">' . __('Two banners side by side') . '</span></p>';
    }
    $buttons = ['whole' => $parent] + $split;
    if (count(array_intersect($buttons, array_keys($positions))) < 2) {
        // a creator while the split is off: the halves are not editable, there is nothing to switch to
        $buttons = [];
    }
    $html .= '<div class="ADsSplitSwitcher' . (empty($buttons) ? ' hidden' : '') . '" role="group" aria-label="' . __('Choose what to edit') . '">'
        . '<div class="ADsSplitSwitcherTitle">' . __('Choose what to edit') . '</div>';
    $halvesOpen = false;
    foreach ($buttons as $part => $type) {
        if (!isset($positions[$type])) {
            continue;
        }
        if ($part === 'whole') {
            $text = '<span class="ADsWhenSplitOff">' . __('Whole banner') . '</span><span class="ADsWhenSplitOn">' . __('Original banner (hidden while split)') . '</span>';
        } elseif ($part === 'left') {
            $text = '<i class="fas fa-caret-left" aria-hidden="true"></i> ' . __('Left half');
        } else {
            $text = __('Right half') . ' <i class="fas fa-caret-right" aria-hidden="true"></i>';
        }
        if ($part !== 'whole' && !$halvesOpen) {
            $html .= '<div class="ADsSplitSwitcherHalves">';
            $halvesOpen = true;
        }
        $current = $type === $currentType;
        $focus = '#adsTabs' . $type . ' .ADsSplitSwitcher .ADsSelectPosition[data-type=&quot;' . $type . '&quot;]';
        $html .= '<button type="button" class="btn ' . ($current ? 'btn-primary' : 'btn-default') . ' ADsSelectPosition ADsSplitSwitcherButton ADsSplitSwitcher-' . $part . '"'
            . ' data-type="' . $type . '" data-focus="' . $focus . '" aria-controls="adsTabs' . $type . '"' . ($current ? ' aria-current="true"' : '') . '>'
            . getADsEditorThumb($positions[$type])
            . '<span class="ADsSplitSwitcherText"><strong>' . $text . '</strong> <small>' . getADsEditorSizeText($positions[$type]) . '</small></span>'
            . '<span class="ADsNavStatus" data-type="' . $type . '">' . getADsEditorStatus($positions[$type]) . '</span>'
            . '</button>';
    }
    if ($halvesOpen) {
        $html .= '</div>';
    }
    $html .= '</div>';
    if ($isParent) {
        $html .= '<div class="alert alert-info ADsWhenSplitOn">' . __('Desktop visitors now see the two halves. This banner keeps its images and code for when you go back to one banner.') . '</div>';
    } else {
        $sideText = $positions[$currentType]['side'] === 'left' ? __('This ad goes on the LEFT half of the banner.') : __('This ad goes on the RIGHT half of the banner.');
        $html .= '<p class="ADsSplitSideText"><strong>' . $sideText . '</strong></p>'
            . '<div class="alert alert-warning ADsWhenSplitOff">' . __('Not shown now: desktop visitors see one banner. Choose "Two banners side by side" to show the halves.') . '</div>';
    }
    return $html . '</div></div>';
}

/**
 * One ad space on the map: a button that opens its settings, or a plain box for a position this user
 * cannot edit.
 */
function getADsEditorMapSlot($positions, $type, $text, $extraClass = '', $parent = '')
{
    $shape = 'ADsMapSlotBanner';
    $configured = false;
    $name = $text;
    $size = '';
    if (isset($positions[$type])) {
        $position = $positions[$type];
        if ($position['width'] && $position['height'] && $position['width'] / $position['height'] < 3) {
            $shape = 'ADsMapSlotRectangle';
        }
        $configured = !empty($position['images']) || !empty($position['code']);
        $name = $position['name'];
        $size = "{$position['width']}&times;{$position['height']}";
    } else {
        return "<div class=\"ADsMapSlot ADsMapSlotLocked {$shape} {$extraClass}\" data-type=\"{$type}\">"
            . "<span class=\"ADsMapSlotText\">{$text}</span></div>";
    }
    $status = $configured ? __('Configured') : __('Empty');
    $stateClass = $configured ? 'ADsMapSlotConfigured' : 'ADsMapSlotEmpty';
    $parentAttr = empty($parent) ? '' : " data-parent=\"{$parent}\"";
    return "<button type=\"button\" class=\"btn btn-default ADsMapSlot {$shape} {$stateClass} {$extraClass}\""
        . " data-type=\"{$type}\"{$parentAttr} aria-controls=\"adsTabs{$type}\""
        . " aria-label=\"" . htmlspecialchars("{$name}, " . str_replace('&times;', 'x', $size) . ", {$status}", ENT_QUOTES, 'UTF-8') . "\">"
        . "<i class=\"fas fa-check-circle ADsMapSlotIcon\" aria-hidden=\"true\"></i> "
        . "<span class=\"ADsMapSlotText\">{$text}</span> <small class=\"ADsMapSlotSize\">{$size}</small></button>";
}

/**
 * Schematic page layouts, from the views listed in ADs::getAdsPositionsInfo().
 * A row is a content block, an ad slot, or columns of rows.
 */
$mapBlocks = [
    'nav' => __('Top menu'),
    'featured' => __('Featured video'),
    'videos' => __('Videos'),
    'player' => __('Player'),
    'description' => __('Title and description'),
    'related' => __('Related videos'),
    'comments' => __('Comments'),
    'banner' => __('Channel cover'),
];
$mapLayouts = [
    'video' => [
        'desktop' => [['block' => 'nav'], ['slot' => 'leaderBoardTop'], ['block' => 'player'], ['slot' => 'leaderBoardTop2'], ['cols' => [[['block' => 'description'], ['block' => 'comments']], [['slot' => 'sideRectangle'], ['block' => 'related']]]]],
        'mobile' => [['block' => 'nav'], ['slot' => 'leaderBoardTopMobile'], ['block' => 'player'], ['slot' => 'leaderBoardTopMobile2'], ['block' => 'description'], ['slot' => 'sideRectangle'], ['block' => 'related']],
    ],
    'home' => [
        'desktop' => [['block' => 'nav'], ['slot' => 'leaderBoardTop'], ['cols' => [[['block' => 'featured']], [['slot' => 'leaderBoardBigVideo']]]], ['slot' => 'leaderBoardTop2'], ['block' => 'videos'], ['slot' => 'leaderBoardMiddle'], ['block' => 'videos']],
        'mobile' => [['block' => 'nav'], ['slot' => 'leaderBoardTopMobile'], ['block' => 'featured'], ['slot' => 'leaderBoardBigVideoMobile'], ['slot' => 'leaderBoardTopMobile2'], ['block' => 'videos'], ['slot' => 'leaderBoardMiddleMobile'], ['block' => 'videos']],
    ],
    'channel' => [
        'desktop' => [['block' => 'nav'], ['slot' => 'channelLeaderBoardTop'], ['block' => 'banner'], ['cols' => [[['slot' => 'leaderBoardTop'], ['block' => 'featured']], [['slot' => 'leaderBoardBigVideo']]]], ['block' => 'videos'], ['slot' => 'leaderBoardMiddle']],
        'mobile' => [['block' => 'nav'], ['slot' => 'channelLeaderBoardTopMobile'], ['block' => 'banner'], ['slot' => 'leaderBoardTopMobile'], ['block' => 'featured'], ['slot' => 'leaderBoardBigVideoMobile'], ['block' => 'videos'], ['slot' => 'leaderBoardMiddleMobile']],
    ],
];
$mapPageNames = [
    'video' => __('Video page'),
    'home' => __('Home page'),
    'channel' => __('Channel page'),
];

function getADsEditorMapRows($rows, $positions, $mapBlocks)
{
    global $positionsInfo;
    $html = '';
    foreach ($rows as $row) {
        if (!empty($row['block'])) {
            $html .= '<div class="ADsMapBlock">' . $mapBlocks[$row['block']] . '</div>';
        } elseif (!empty($row['cols'])) {
            $html .= '<div class="ADsMapCols">';
            foreach ($row['cols'] as $index => $col) {
                $html .= '<div class="ADsMapCol ' . (empty($index) ? 'ADsMapColMain' : 'ADsMapColSide') . '">'
                    . getADsEditorMapRows($col, $positions, $mapBlocks) . '</div>';
            }
            $html .= '</div>';
        } else {
            $type = $row['slot'];
            $name = isset($positionsInfo[$type]['name']) ? $positionsInfo[$type]['name'] : $type;
            $split = ADs::getSplitPositions($type);
            if (empty($split)) {
                $html .= getADsEditorMapSlot($positions, $type, $name);
                continue;
            }
            // the whole banner, or its halves while the split is on (editor.css)
            $html .= '<div class="ADsMapSplitGroup ADsSplitState ' . (ADs::isSplitEnabled($type) ? 'ADsSplitOn' : '') . '" data-split-parent="' . $type . '">'
                . getADsEditorMapSlot($positions, $type, $name, 'ADsMapSplitWhole')
                . '<div class="ADsMapSplitPair">';
            foreach ($split as $side => $slotType) {
                $sideText = $side === 'left' ? __('Left') : __('Right');
                $html .= getADsEditorMapSlot($positions, $slotType, $sideText, 'ADsMapSplitHalf', $type);
            }
            $html .= '</div></div>';
        }
    }
    return $html;
}

// Start with the common above-player banner, or its visible left half.
$preferredType = !empty($splitStates['leaderBoardTop']) && isset($positions['leaderBoardTopLeft']) ? 'leaderBoardTopLeft' : 'leaderBoardTop';
$firstType = isset($positions[$preferredType]) ? $preferredType : key($positions);
$initialPage = empty($positions[$firstType]['map']) ? 'video' : $positions[$firstType]['map'][0];

$editorConfig = [
    'positions' => [],
    'splits' => $splitStates,
    'canChangeSplit' => $canChangeSplit,
    'saveToken' => $canChangeSplit ? getToken() : '',
    'texts' => [
        'configured' => __('Configured'),
        'empty' => __('Empty'),
        'images' => __('Images'),
        'adCode' => __('Ad code'),
        'splitOn' => __('Split on: desktop visitors now see the left and right halves.'),
        'splitOff' => __('Split off: desktop visitors now see the original 728 x 90 banner again.'),
        'splitOffByType' => [],
        'error' => __('An error occurred'),
        'saved' => __('Saved'),
        'desktop' => __('Desktop'),
        'mobile' => __('Mobile'),
        'pages' => $mapPageNames,
    ],
];
foreach ($positions as $type => $position) {
    if (isset($splitStates[$type])) {
        // every translation keeps "728 x 90" as written, so the banner's configured size replaces it
        $editorConfig['texts']['splitOffByType'][$type] = str_replace('728 x 90', intval($position['width']) . ' x ' . intval($position['height']), $editorConfig['texts']['splitOff']);
    }
    $editorConfig['positions'][$type] = [
        'name' => $position['name'],
        'parent' => $position['parent'],
        'map' => $position['map'],
        'images' => $position['images'],
        'firstImage' => $position['firstImage'],
        'page' => $position['page'],
        'code' => $position['code'],
        'width' => $position['width'],
        'height' => $position['height'],
        'previewURL' => getADsEditorPreviewURL($previewURLs, $position),
        'device' => $position['device'],
    ];
}

$_page = new Page(array('ADs Editor'));
$_page->setExtraStyles(array('plugin/ADs/editor.css'));
$_page->setExtraScripts(array('plugin/ADs/editor.js'));
?>
<div class="container-fluid ADsEditor" id="ADsEditor">
    <script>
        var is_regular_user = <?php echo json_encode($is_regular_user); ?>;
        var ADsEditorConfig = <?php echo json_encode($editorConfig); ?>;
    </script>
    <br>
    <div class="panel panel-default">
        <div class="panel-heading clearfix">
            <div class="pull-right">
                <?php
                echo getTourHelpButton('plugin/ADs/editor.help.json', 'btn btn-default btn-xs');
                ?>
            </div>
            <?php
            if ($is_regular_user) {
                echo ' - ';
            }
            echo __('Edit Ads');
            ?>
        </div>
        <div class="panel-body">
            <div class="ADsPositionChooser form-group">
                <label for="ADsPositionSelect"><span class="ADsStepNumber">1</span> <?php echo __('Position'); ?></label>
                <select class="form-control" id="ADsPositionSelect">
                    <?php foreach (['desktop' => __('Desktop'), 'mobile' => __('Mobile (phones and tablets)')] as $device => $name) { ?>
                        <optgroup label="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php foreach ($positions as $type => $position) {
                                if ($position['device'] !== $device) { continue; }
                            ?>
                                <option value="<?php echo $type; ?>"<?php echo $type === $firstType ? ' selected' : ''; ?>><?php echo htmlspecialchars($position['name'], ENT_QUOTES, 'UTF-8') . ' (' . $position['width'] . ' × ' . $position['height'] . ')'; ?></option>
                            <?php } ?>
                        </optgroup>
                    <?php } ?>
                </select>
            </div>
            <div class="ADsWorkspace">
                <div class="ADsWorkspaceContent">

                    <div class="tab-content">

                        <?php
                        $active = ' in active';
                        foreach ($positions as $type => $position) {
                            $active = $type === $firstType ? ' in active' : '';
                            $width = $position['width'];
                            $height = $position['height'];
                            $previewURL = getADsEditorPreviewURL($previewURLs, $position);
                            $stateParent = $position['parent'] ?: $type;
                            $splitEnabled = !empty($splitStates[$stateParent]);
                        ?>
                            <div id="adsTabs<?php echo $type; ?>" class="tab-pane fade ADsSplitState <?php echo $active . ($splitEnabled ? ' ADsSplitOn' : ''); ?>" data-split-parent="<?php echo $stateParent; ?>" role="tabpanel" aria-labelledby="adsNav<?php echo $type; ?>">
                                <div class="panel panel-default">
                                    <div class="panel-heading clearfix">
                                        <?php
                                        if (!empty($previewURL)) {
                                        ?>
                                            <a href="<?php echo htmlspecialchars($previewURL, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" class="btn btn-default btn-xs pull-right ADsViewOnSite">
                                                <i class="fas fa-external-link-alt" aria-hidden="true"></i> <?php echo __('View on site'); ?>
                                            </a>
                                        <?php
                                        }
                                        ?>
                                        <h3 class="panel-title ADsPositionTitle" tabindex="-1">
                                            <?php echo $position['name']; ?>
                                            <small><?php echo getADsEditorSizeText($position); ?></small>
                                            <?php echo getADsEditorDeviceLabel($position); ?>
                                        </h3>
                                    </div>
                                    <div class="panel-body">
                                        <?php
                                        // the banner and its halves share one header: layout and what to edit
                                        $splitHeader = getADsEditorSplitHeader($positions, $stateParent, $type, $canChangeSplit, $splitEnabled);
                                        if (!empty($splitHeader) && ($canChangeSplit || $splitEnabled)) {
                                            echo '<details class="ADsDetails ADsLayoutDetails"' . ($splitEnabled || !empty($position['parent']) ? ' open' : '') . '><summary>' . __('Desktop layout') . '</summary>' . $splitHeader . '</details>';
                                        }
                                        ?>
                                        <p class="help-block ADsPositionSummary"><?php echo empty($position['pages'][0]) ? '' : $position['pages'][0]; ?></p>
                                        <?php if ($canChangeSplit) { ?>
                                            <div class="alert alert-warning ADsImageCodeNotice<?php echo $position['code'] ? '' : ' hidden'; ?>" data-type="<?php echo $type; ?>"><?php echo __('Creator images take priority on their content. With a creator but no custom image, site code comes before site images. Without a creator, site images come before code. Saving or deleting a site image clears this position’s code.'); ?></div>
                                        <?php } ?>
                                        <h4 class="ADsWorkflowTitle"><span class="ADsStepNumber">2</span> <?php echo __("Image"); ?> <small><?php echo getADsEditorSizeText($position); ?></small></h4>
                                        <form>
                                            <div class="form-group">
                                                <?php
                                                $croppie1 = getCroppie(__("Upload Image") . ' ' . $position['name'], "saveAdsImage" . $type, $width, $height);
                                                echo $croppie1['html'];
                                                ?>
                                            </div>
                                            <input type="hidden" id="inputAdsFileName<?php echo $type; ?>" class="form-control" placeholder="<?php echo __("Filename"); ?>">
                                            <button class="btn btn-primary btn-block"><i class="fas fa-save"></i> <?php echo __('Save Image'); ?></button>
                                        </form>
                                    </div>
                                    <div class="panel-footer ADsImageMetadata<?php echo empty($position['images']) ? ' hidden' : ''; ?>">
                                        <h4 class="ADsWorkflowTitle"><span class="ADsStepNumber">3</span> <?php echo __('Link'); ?> <small><?php echo __('URL') . ' · ' . __('Title'); ?></small></h4>
                                        <ul class="list-group" id="list-group-<?php echo $type; ?>">
                                        </ul>

                                        <button class="btn btn-success btn-block" id="SaveOrderMetadata<?php echo $type; ?>"><i class="fas fa-save"></i> <?php echo __('Save Order and Metadata'); ?></button>
                                    </div>
                                    <div class="panel-body ADsAdvancedSection">
                                        <details class="ADsDetails ADsAdvanced">
                                            <summary><?php echo __('Advanced'); ?> · <?php echo $canChangeSplit ? __('Ad code') . ' / ' : ''; ?><?php echo __('Details'); ?></summary>
                                        <div class="ADsPositionInfo">
                                            <strong><?php echo __('Where it appears'); ?></strong>
                                            <ul>
                                                <?php
                                                foreach ($position['pages'] as $pageText) {
                                                    echo '<li>' . $pageText . '</li>';
                                                }
                                                ?>
                                            </ul>
                                            <p class="small text-muted">
                                                <?php
                                                echo __('Setting name') . ': <code>' . $type . '</code>';
                                                if (!empty($position['label'])) {
                                                    echo ' &middot; ' . __('Label') . ': ' . htmlspecialchars($position['label'], ENT_QUOTES, 'UTF-8');
                                                }
                                                ?>
                                            </p>
                                            <?php
                                            if ($position['notDisplayed']) {
                                                echo '<div class="alert alert-warning">' . __('The website pages do not display this position at the moment. Images saved here are only returned by the API.') . '</div>';
                                            }
                                            ?>
                                        </div>
                                        <?php if ($canChangeSplit) { ?>
                                            <div class="form-group ADsCodeEditor">
                                                <label for="ADsCode<?php echo $type; ?>"><?php echo __('Ad code'); ?></label>
                                                <p class="help-block"><?php echo __('Creator images take priority on their content. With a creator but no custom image, site code comes before site images. Without a creator, site images come before code. Saving or deleting a site image clears this position’s code.'); ?></p>
                                                <textarea class="form-control ADsCode" id="ADsCode<?php echo $type; ?>" data-type="<?php echo $type; ?>" rows="6" spellcheck="false"><?php echo htmlspecialchars((string) ($obj->$type->value ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                <button type="button" class="btn btn-primary ADsSaveCode" data-type="<?php echo $type; ?>"><?php echo __('Save ad code'); ?></button>
                                            </div>
                                        <?php } ?>
                                        </details>
                                    </div>
                                </div>
                            </div>
                            <script>
                                // Keep one pending create and reuse initialized croppers. Destroying one while
                                // its image bind is pending also breaks Croppie's asynchronous callbacks.
                                var restartCroppie<?php echo $type; ?> = function(imageURL) {
                                    clearTimeout(<?php echo $croppie1['createCroppie']; ?>Timeout);
                                    var cropper = $('#<?php echo $croppie1['id']; ?>');
                                    if (cropper.data('croppie')) {
                                        cropper.croppie('bind', {url: imageURL});
                                    } else {
                                        <?php echo $croppie1['createCroppie']; ?>(imageURL);
                                    }
                                };
                                <?php echo $croppie1['restartCroppie']; ?> = restartCroppie<?php echo $type; ?>;

                                function saveAdsImage<?php echo $type; ?>(image) {
                                    saveAdsImage(image, '<?php echo $type; ?>');
                                }

                                function getAdsCroppieFunction<?php echo $type; ?>() {
                                    <?php
                                    echo $croppie1['getCroppieFunction'];
                                    ?>
                                }

                                $(document).ready(function() {
                                    startAdsEditors('<?php echo $type; ?>');
                                });
                            </script>
                        <?php
                            $active = '';
                        }
                        ?>
                    </div>
                </div>
            </div>
            <details class="ADsDetails ADsMapDetails">
                <summary><?php echo __('Where the ads appear'); ?></summary>
            <section class="ADsMap" id="ADsMap" aria-labelledby="ADsMapTitle" data-page="<?php echo $initialPage; ?>">
                <div class="ADsMapHeader">
                    <h4 id="ADsMapTitle"><?php echo __('Where the ads appear'); ?></h4>
                    <div class="btn-group btn-group-sm" role="group" aria-label="<?php echo __('Page'); ?>" id="ADsMapPages">
                        <?php
                        foreach ($mapPageNames as $page => $pageName) {
                            $pressed = $page === $initialPage;
                        ?>
                            <button type="button" class="btn <?php echo $pressed ? 'btn-primary' : 'btn-default'; ?> ADsMapPageButton" data-page="<?php echo $page; ?>" aria-pressed="<?php echo $pressed ? 'true' : 'false'; ?>">
                                <?php echo $pageName; ?>
                            </button>
                        <?php
                        }
                        ?>
                    </div>
                </div>
                <p class="help-block"><?php echo __('A simplified view of the page. Select an ad space to open its settings.'); ?></p>
                <div class="row">
                    <?php
                    $devices = [
                        'desktop' => ['col-sm-8', '<i class="fas fa-desktop" aria-hidden="true"></i> ' . __('Desktop')],
                        'mobile' => ['col-sm-4', '<i class="fas fa-mobile-alt" aria-hidden="true"></i> ' . __('Mobile (phones and tablets)')],
                    ];
                    foreach ($devices as $device => $deviceInfo) {
                    ?>
                        <div class="<?php echo $deviceInfo[0]; ?>">
                            <div class="panel panel-default ADsMapFrame ADsMapFrame-<?php echo $device; ?>">
                                <div class="panel-heading"><?php echo $deviceInfo[1]; ?></div>
                                <div class="panel-body">
                                    <?php
                                    foreach ($mapLayouts as $page => $layout) {
                                    ?>
                                        <div class="ADsMapPage <?php echo $page === $initialPage ? '' : 'hidden'; ?>" data-page="<?php echo $page; ?>">
                                            <?php echo getADsEditorMapRows($layout[$device], $positions, $mapBlocks); ?>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
                </div>
                <div class="ADsMapActions">
                    <?php
                if (!empty($is_admin)) {
                ?>
                    <a href="<?php echo addQueryStringParameter($global['webSiteRootURL'], 'AdsDebug', 1); ?>" target="_blank" rel="noopener" class="btn btn-default btn-xs">
                        <i class="fas fa-bug" aria-hidden="true"></i> <?php echo __('Show Ads Positions'); ?>
                    </a>
                <?php
                }
                    ?>
                </div>
                <p class="ADsMapLegend small">
                    <span class="btn btn-default btn-xs ADsMapSlot ADsMapSlotConfigured" aria-hidden="true"><i class="fas fa-check-circle ADsMapSlotIcon"></i></span> <?php echo __('Configured'); ?>
                    <span class="btn btn-default btn-xs ADsMapSlot ADsMapSlotEmpty" aria-hidden="true">&nbsp;</span> <?php echo __('Empty'); ?>
                    <span class="btn btn-primary btn-xs ADsMapSlot" aria-hidden="true">&nbsp;</span> <?php echo __('Selected'); ?>
                </p>
            </section>
            </details>

            <?php if (!empty($is_admin)) { ?>
                <section class="panel panel-default" id="ADsPreviewPanel" aria-labelledby="ADsPreviewTitle">
                    <div class="panel-heading ADsPreviewHeading">
                        <h4 class="panel-title" id="ADsPreviewTitle">
                            <?php echo __('Real page preview'); ?>
                            <small class="ADsPreviewContext hidden" id="ADsPreviewContext"></small>
                        </h4>
                        <div class="ADsPreviewActions">
                            <button type="button" class="btn btn-default btn-xs hidden" id="ADsPreviewReload">
                                <i class="fas fa-sync-alt" aria-hidden="true"></i> <?php echo __('Reload'); ?>
                            </button>
                            <a href="#" target="_blank" rel="noopener" class="btn btn-default btn-xs hidden" id="ADsPreviewOpen">
                                <i class="fas fa-external-link-alt" aria-hidden="true"></i> <?php echo __('Open in a new tab'); ?>
                            </a>
                            <button type="button" class="btn btn-primary btn-xs" id="ADsPreviewLoad" aria-expanded="false" aria-controls="ADsPreviewBody"
                                data-show="<?php echo htmlspecialchars(__('Show preview'), ENT_QUOTES, 'UTF-8'); ?>"
                                data-hide="<?php echo htmlspecialchars(__('Close preview'), ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="fas fa-eye" aria-hidden="true"></i> <span class="ADsPreviewLoadText"><?php echo __('Show preview'); ?></span>
                            </button>
                        </div>
                    </div>
                    <div class="panel-body hidden" id="ADsPreviewBody">
                        <p class="help-block"><?php echo __('The selected position is outlined on a real page of your site and follows the position you choose below. Mobile preview changes only the ADs positions; the rest of the page uses your browser device.'); ?></p>
                        <?php if (!AVideoPlugin::showAds(0)) { ?>
                            <div class="alert alert-info" id="ADsPreviewNoAdsForYou"><?php echo __('Your account has a plan that removes ads, so the preview outlines the positions without showing the ads. Open the page in a private window to see what visitors see.'); ?></div>
                        <?php } ?>
                        <div class="alert alert-warning hidden" id="ADsPreviewUnavailable"><?php echo __('The website pages do not display this position, so there is nothing to preview.'); ?></div>
                        <iframe id="ADsPreviewFrame" class="ADsPreviewFrame" title="<?php echo __('Real page preview'); ?>" src="about:blank"></iframe>
                    </div>
                </section>
            <?php } ?>

                <div class="ADsPositionList">
                    <details class="ADsDetails">
                    <summary><?php echo __('Ad positions'); ?></summary>
                    <nav aria-label="<?php echo __('Ad positions'); ?>" id="ADsPositionsNav">
                        <?php
                        $active = 'active';
                        $lastDevice = '';
                        foreach ($positions as $type => $position) {
                            if ($position['device'] !== $lastDevice) {
                                if (!empty($lastDevice)) {
                                    echo '</ul>';
                                }
                                $lastDevice = $position['device'];
                                $groupTitle = $lastDevice === 'mobile' ? __('Mobile (phones and tablets)') : __('Desktop');
                                echo '<h5 class="ADsNavGroupTitle">' . $groupTitle . '</h5>';
                                echo '<ul class="nav nav-pills nav-stacked ADsNav" role="tablist">';
                            }
                            $itemClass = $type === $firstType ? 'active' : '';
                            if (!empty($position['parent'])) {
                                $itemClass .= ' ADsNavSplitItem';
                            } elseif (!empty(ADs::getSplitPositions($type))) {
                                $itemClass .= ' ADsNavSplitParent';
                            }
                            $sideIcon = '';
                            if ($position['side'] === 'left') {
                                $sideIcon = '<i class="fas fa-caret-left" aria-hidden="true"></i> ';
                            } elseif ($position['side'] === 'right') {
                                $sideIcon = '<i class="fas fa-caret-right" aria-hidden="true"></i> ';
                            }
                        ?>
                            <?php $stateParent = $position['parent'] ?: $type; ?>
                            <li class="ADsSplitState <?php echo $itemClass . (!empty($splitStates[$stateParent]) ? ' ADsSplitOn' : ''); ?>" data-split-parent="<?php echo $stateParent; ?>" role="presentation">
                                <a data-toggle="tab" href="#adsTabs<?php echo $type; ?>" id="adsNav<?php echo $type; ?>" role="tab" aria-controls="adsTabs<?php echo $type; ?>" data-type="<?php echo $type; ?>">
                                    <span class="ADsNavStatus pull-right" data-type="<?php echo $type; ?>"><?php echo getADsEditorStatus($position); ?></span>
                                    <?php echo $sideIcon . $position['name']; ?>
                                    <small class="ADsNavSize"><?php echo getADsEditorSizeText($position); ?></small>
                                    <?php
                                    if (!empty($position['parent'])) {
                                        echo '<small class="ADsNavNote ADsWhenSplitOff">' . __('Used only while the split is on') . '</small>';
                                    } elseif (!empty(ADs::getSplitPositions($type))) {
                                        echo '<small class="ADsNavNote ADsWhenSplitOn">' . __('Desktop shows the halves instead') . '</small>';
                                    }
                                    if ($position['notDisplayed']) {
                                        echo '<small class="ADsNavNote">' . __('Not displayed on the website') . '</small>';
                                    }
                                    ?>
                                </a>
                            </li>
                        <?php
                            $active = '';
                        }
                        if (!empty($lastDevice)) {
                            echo '</ul>';
                        }
                        ?>
                    </nav>
                    </details>
                </div>
        </div>
    </div>
    <li class="list-group-item clearfix hidden listItemTemplate">
        <div class="row">
            <div class="col-sm-3">
                <i class="fas fa-grip-vertical pull-left" style="margin-right: 10px;"></i>
                <img src="" class="img img-responsive pull-left" style="max-height: 60px; max-width: 150px; margin-right: 10px;">
            </div>
            <div class="col-sm-7">
                <input type="hidden" value="" name="fileName"  />
                <input type="hidden" value="" name="imageURL"  />
                <input type="hidden" value="" name="type"  />
                <input type="hidden" value="" name="order"  />
                <label class="ADsMetadataField"><?php echo __('URL'); ?><input type="url" value="" class="form-control" name="url" placeholder="https://" /></label>
                <label class="ADsMetadataField"><?php echo __('Title'); ?><input type="text" value="" class="form-control" name="title" /></label>
            </div>
            <div class="col-sm-2">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-primary" aria-label="<?php echo __('Edit image'); ?>">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" aria-label="<?php echo __('Delete image'); ?>">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </li>
    <script>
        var ADsListsLoaded = {};

        function saveAdsImage(image, type) {
            modal.showPleaseWait();
            var itemCount = $("#list-group-" + type + " > li").length;
            $.ajax({
                url: webSiteRootURL + 'plugin/ADs/saveImage.json.php',
                data: {
                    type: type,
                    filename: $('#inputAdsFileName' + type).val(),
                    image: image,
                    is_regular_user: is_regular_user,
                    order: itemCount + 1
                },
                type: 'post',
                success: function(response) {
                    if (!response.error) {
                        avideoToastSuccess(__("Ads Saved!"));
                        restartForm(type);
                        loadAdsList(type);
                        if (!is_regular_user) {
                            // saveImage.json.php clears the site-wide ad code (ADs::saveAdsHTML())
                            ADsEditor.clearCode(type);
                        }
                    } else {
                        avideoAlertError(response.msg);
                    }
                },
                error: function() {
                    avideoToastError(ADsEditorConfig.texts.error);
                },
                complete: function() {
                    modal.hidePleaseWait();
                }
            });
        }

        function startAdsEditors(type) {
            $('#adsTabs' + type + ' form').submit(function(evt) {
                evt.preventDefault();
                setTimeout(function() {
                    eval('getAdsCroppieFunction' + type + '();');
                }, 500);
                return false;
            });
            $('#SaveOrderMetadata' + type).click(function() {
                saveAdsMetadata(type);
            });
            if ($('#adsTabs' + type).hasClass('active')) {
                restartForm(type);
                loadAdsList(type);
            }
        }

        function createAdImageItem(fileName, imageURL, type, url, title, order) {
            // Clone the template
            const clonedItem = $('.listItemTemplate').clone().removeClass('hidden listItemTemplate');

            // Replace placeholders with actual values
            clonedItem.removeClass('hidden');
            clonedItem.removeClass('listItemTemplate');
            clonedItem.attr('id', fileName);
            clonedItem.find('img').attr('src', imageURL);
            clonedItem.find('input[name="fileName"]').val(fileName);
            clonedItem.find('input[name="imageURL"]').val(imageURL);
            clonedItem.find('input[name="type"]').val(type);
            clonedItem.find('input[name="url"]').val(url);
            clonedItem.find('input[name="title"]').val(title);
            clonedItem.find('input[name="order"]').val(order);
            clonedItem.find('.btn-primary').attr('onclick', `editAdsImage('${fileName}', '${type}')`);
            clonedItem.find('.btn-danger').attr('onclick', `deleteAdsImage('${fileName}', '${type}')`);

            return clonedItem;
        }

        function restartForm(type) {
            eval('restartCroppie' + type + '("<?php echo getURL("view/img/transparent1px.png"); ?>");')
            $('#inputAdsFileName' + type).val('');
        }

        function saveAdsMetadata(type) {
            modal.showPleaseWait();
            var metadata = getResultListOrder('list-group-' + type);
            $.ajax({
                url: webSiteRootURL + 'plugin/ADs/saveImagesMetadata.php',
                data: {
                    type: type,
                    metadata: metadata,
                    is_regular_user: is_regular_user,
                },
                type: 'post',
                success: function(response) {
                    if (!response.error) {
                        avideoToastSuccess(__("Ads Saved!"));
                        loadAdsList(type);
                        ADsEditor.refreshPreview();
                    } else {
                        avideoAlertError(response.msg);
                    }
                },
                error: function() {
                    avideoToastError(ADsEditorConfig.texts.error);
                },
                complete: function() {
                    modal.hidePleaseWait();
                }
            });
        }

        function getResultListOrder(id) {
            var result = [];
            $("#" + id + " > li").each(function(index, elem) {
                var $elem = $(elem);
                var imgSrc = $elem.find("img").attr("src");
                var url = $elem.find("input[name='url']").val();
                var title = $elem.find("input[name='title']").val();

                result.push({
                    order: index + 1,
                    imgSrc: imgSrc,
                    url: url,
                    title: title
                });
            });
            return result;
        }

        function loadAdsList(type) {
            ADsListsLoaded[type] = 'loading';
            $('#list-group-' + type).empty();
            $.ajax({
                url: webSiteRootURL + 'plugin/ADs/getAdsList.json.php',
                data: {
                    type: type,
                    is_regular_user: is_regular_user,
                },
                type: 'post',
                success: function(response) {
                    if (!response.error) {
                        ADsListsLoaded[type] = true;
                        ADsEditor.setImages(type, response.ads ? response.ads.length : 0, response.ads && response.ads.length ? response.ads[0].imageURL : '');
                        if (response.ads && response.ads.length) {
                            response.ads.forEach(ad => {
                                var item = createAdImageItem(ad.fileName, ad.imageURL, ad.type, ad.txt.url, ad.txt.title, ad.txt.order);
                                $('#list-group-' + type).append(item);
                            });
                            $("#list-group-" + type).sortable({
                                update: function(event, ui) {
                                    //saveAdsMetadata(type);
                                }
                            });
                        }
                    } else {
                        ADsListsLoaded[type] = false;
                        avideoAlertError(response.msg);
                    }
                },
                error: function() {
                    ADsListsLoaded[type] = false;
                    avideoToastError(ADsEditorConfig.texts.error);
                }
            });
        }

        function editAdsImage(fileName, type) {
            var imageURL = $('#' + fileName + ' input[name="imageURL"]').val();
            var url = $('#' + fileName + ' input[name="url"]').val();
            var title = $('#' + fileName + ' input[name="title"]').val();
            var fileName = $('#' + fileName + ' input[name="fileName"]').val();
            var order = $('#' + fileName + ' input[name="order"]').val();
            eval('restartCroppie' + type + '(imageURL);')
            $('#inputAdsFileName' + type).val(fileName);
        }

        function deleteAdsImage(fileName, type) {
            modal.showPleaseWait();
            var type = $('#' + fileName + ' input[name="type"]').val();
            $.ajax({
                url: webSiteRootURL + 'plugin/ADs/deleteImage.json.php',
                data: {
                    type: type,
                    fileName: fileName,
                    is_regular_user: is_regular_user
                },
                type: 'post',
                success: function(response) {
                    if (!response.error) {
                        avideoToastSuccess(__("Ads deleted!"));
                        loadAdsList(type);
                        if (!is_regular_user) {
                            ADsEditor.clearCode(type);
                        }
                    } else {
                        avideoAlertError(response.msg);
                    }
                },
                error: function() {
                    avideoToastError(ADsEditorConfig.texts.error);
                },
                complete: function() {
                    modal.hidePleaseWait();
                }
            });
        }
    </script>
</div>
<?php
$_page->print();
?>
