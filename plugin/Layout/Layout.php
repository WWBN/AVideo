<?php
global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class Layout extends PluginAbstract {
    static $criticalCSS = array(
        'view/bootstrap/css/bootstrap.min.css',
        'view/css/custom',
        'videos/cache/custom.css',
        'view/css/navbar.css',
        'plugin/Gallery/style.css',
        'view/css/main.css',
    );
    static private $tags = array();
    static $searchOptions = array(
        array(
            'text' => 'Video Title',
            'value' => 'v.title'
        ),
        array(
            'text' => 'Video Description',
            'value' => 'v.description'
        ),
        array(
            'text' => 'Channel Name',
            'value' => 'c.name'
        ),
        array(
            'text' => 'Channel Description',
            'value' => 'c.description'
        ),
        array(
            'text' => 'Video ID',
            'value' => 'v.id'
        ),
        array(
            'text' => 'Video Filename',
            'value' => 'v.filename'
        ),
    );

    public function getTags() {
        return array(
            PluginTags::$RECOMMENDED,
            PluginTags::$FREE
        );
    }

    public function getDescription() {
        return "Finetune the layout and helpers";
    }

    public function getName() {
        return "Layout";
    }

    public function getPluginVersion() {
        return "1.1";
    }

    public function getUUID() {
        return "layout84-8f5a-4d1b-b912-172c608bf9e3";
    }

    public function getEmptyDataObject() {
        global $global;
        $obj = new stdClass();
        /*
          $variablesFile = $global['systemRootPath'] . 'plugin/Customize/sass/variables.scss';
          $subject = file_get_contents($variablesFile);
          $o = new stdClass();
          $o->type = "textarea";
          $o->value = $subject;
          $obj->colorsVariables = $o;
          $obj->showCustomCSS = true;
         * 
         */
        //$obj->showButtonNotification = false;
        $obj->showSearchOptionsBelowNavbar = false;
        $obj->categoriesTopButtons = false;
        $obj->categoriesTopButtonsShowOnlyOnFirstPage = true;
        $obj->categoriesTopButtonsShowVideosCount = false;
        $obj->categoriesTopButtonsFluid = true;
        $obj->categoriesTopLeftMenu = true;
        $obj->enableAccessibility = false;

        $o = new stdClass();
        $o->type = array(0 => '-- ' . ("Random")) + self::getLoadersArray();
        $o->value = 'avideo';
        $obj->pageLoader = $o;

        return $obj;
    }

    static function getLoadersArray() {
        $files = Layout::getLoadersFiles();
        $response = array();
        foreach ($files as $key => $value) {
            $response[$value['name']] = ucfirst($value['name']);
        }
        return $response;
    }

    static function getLoadersFiles() {
        global $global;
        $files = _glob($global['systemRootPath'] . 'plugin/Layout/loaders/', '/.*html/');
        $response = array();
        foreach ($files as $key => $value) {
            $name = str_replace('.html', '', basename($value));
            $response[$name] = array('path' => $value, 'name' => $name);
        }
        return $response;
    }

    static public function getLoader($file) {
        global $global;
        $files = self::getLoadersFiles();
        $name = '';
        if (!empty($file)) {
            foreach ($files as $key => $value) {
                if ($file == $value['name']) {
                    $name = $value['name'];
                    break;
                }
            }
        }
        if (empty($name)) {
            $rand_key = array_rand($files);
            $name = $files[$rand_key]['name'];
        }

        $content = file_get_contents($global['systemRootPath'] . 'plugin/Layout/loaders/' . $name . '.html');
        return trim(preg_replace('/\s+/', ' ', str_replace('lds-', 'lds-' . uniqid(), $content)));
    }

    static function getLoaderDefault() {
        global $_getLoaderDefault;

        if (!isset($_getLoaderDefault)) {
            $obj = AVideoPlugin::getObjectData('Layout');
            $loader = Layout::getLoader($obj->pageLoader->value);
            //$loader = Layout::getLoader('spinner');
            $parts = explode('</style>', $loader);
            if (preg_match('/style/', $parts[0])) {
                $parts[0] .= '</style>';
            }
            $_getLoaderDefault = array('css' => $parts[0], 'html' => $parts[1]);
        }
        return $_getLoaderDefault;
    }

    static function getBGAnimationFiles() {
        global $global;
        $files = _glob($global['systemRootPath'] . 'plugin/Layout/animatedBackGrounds/', '/.*php/');
        $response = array();
        foreach ($files as $key => $value) {
            $name = basename($value);
            if ($name === 'index.php') {
                continue;
            }
            $name = str_replace('.php', '', $name);
            $response[$name] = array('path' => $value, 'name' => $name);
        }
        return $response;
    }

    static function includeBGAnimationFile($file) {
        if (empty($file)) {
            return false;
        }
        $files = self::getBGAnimationFiles();
        if ($file == 1) {
            $f = $files[array_rand($files)];
            echo '<!-- ' . $f['name'] . ' -->';
            include $f['path'];
        }
        foreach ($files as $key => $value) {
            if ($file == $value['name']) {
                include $value['path'];
                break;
            }
        }
        return true;
    }

    public function getPluginMenu() {
        global $global;
        return "";
        $filename = $global['systemRootPath'] . 'plugin/Customize/pluginMenu.html';
        return file_get_contents($filename);
    }

    public function getHeadCode() {
        global $global;
        $loaderParts = self::getLoaderDefault();
        echo $loaderParts['css'];
        echo "<script>var avideoLoader = '{$loaderParts['html']}';</script>";
        return false;
    }

    static function getIconsList() {
        global $global;
        include $global['systemRootPath'] . 'plugin/Layout/fontAwesomeFAB.php';
        // Fetch variables scss file in variable
        $font_vars = file_get_contents($global['systemRootPath'] . 'node_modules/fontawesome-free/scss/_variables.scss');

        // Find the position of first $fa-var , as all icon class names start after that
        $pos = strpos($font_vars, '$fa-var');

        // Filter the string and return only the icon class names
        $font_vars = substr($font_vars, $pos);

        // Create an array of lines
        $lines = explode("\n", $font_vars);

        $fonts_list = array();
        foreach ($lines as $line) {
            // Discard any black line or anything without :
            if (strpos($line, ':') !== false) {
                // Icon names and their Unicode Private Use Area values are separated with : and hence, explode them.
                $t = explode(":", $line);

                // Remove the $fa-var with fa, to use as css class names.
                $className = str_replace('$fa-var-', '', $t[0]);
                if (!in_array($className, $font_awesome_brands)) {
                    $classNameFull = "fa fa-{$className}";
                } else {
                    $classNameFull = "fab fa-{$className}";
                }
                $fonts_list[$className] = array($className, $classNameFull, $t[1]);
            }
        }
        return $fonts_list;
    }

    static function getSelectSearchable($optionsArray, $name, $selected, $id = "", $class = "", $placeholder = false, $templatePlaceholder = '') {
        global $global, $nonCriticalCSS;
        if (empty($id)) {
            $id = $name;
        }
        $html = "";
        if (empty($global['getSelectSearchable'])) {
            $html .= '<link href="' . getURL('view/js/select2/select2.min.css') . '" rel="stylesheet"  ' . $nonCriticalCSS . '/>';
            $html .= '<style>
                .select2-selection__rendered {line-height: 32px !important;}
                .select2-selection {min-height: 34px !important;}</style>';
        }
        if (empty($class)) {
            $class = "js-select-search";
        }
        $html .= '<select class="form-control ' . $class . '" name="' . $name . '" id="' . $id . '" style="display:none;">';
        if ($placeholder) {
            $html .= '<option value="" > -- </option>';
        }
        foreach ($optionsArray as $key => $value) {
            $selectedString = "";
            if (is_array($value)) { // need this because of the category icons
                $_value = $value[1];
                $_parameters = @$value[2];
                $_text = $value[0];
            } else {
                $_parameters = '';
                $_value = $key;
                $_text = $value;
            }
            if ($_value == $selected) {
                $selectedString = "selected";
            }
            $html .= '<option value="' . $_value . '" ' .
                    $selectedString . ' ' . $_parameters . '>' .
                    $_text . '</option>';
        }
        $html .= '</select>';
        // this is just to display something before load the select2
        if (empty($templatePlaceholder)) {
            $html .= '<select class="form-control" id="deleteSelect_' . $id . '" ><option></option></select>';
        } else {
            $html .= $templatePlaceholder;
        }
        $html .= '<script>$(document).ready(function() {$(\'#deleteSelect_' . $id . '\').remove();});</script>';

        $global['getSelectSearchable'] = 1;
        return $html;
    }

    static function getSelectSearchableHTML($optionsArray, $name, $selected, $id = "", $class = "", $placeholder = false, $templatePlaceholder = '') {
        global $global;
        if (empty($id)) {
            $id = $name;
        }
        $html = self::getSelectSearchable($optionsArray, $name, $selected, $id, $class, $placeholder, $templatePlaceholder);

        $html .= "<script>function getSelectformatStateResult{$name} (state) {
                                    if (!state.id) {
                                      return state.text;
                                    }
                                    var \$state = $(
                                      '<span><i class=\"' + state.id + '\"></i>'+
                                      state.text + '</span>'
                                    );
                                    return \$state;
                                  };";
        $html .= '$(document).ready(function() {$(\'#' . $id . '\').select2({templateSelection: getSelectformatStateResult' . $name . ', templateResult: getSelectformatStateResult' . $name . ',width: \'100%\'});});</script>';
        return $html;
    }

    static function getIconsSelect($name, $selected = "", $id = "", $class = "") {
        global $getIconsSelect;
        $getIconsSelect = 1;
        $icons = self::getIconsList();
        if (empty($id)) {
            $id = uniqid();
        }
        $code = "<script>function getIconsSelectformatStateResult (state) {
                                    if (!state.id) {
                                      return state.text;
                                    }
                                    var \$state = $(
                                      '<span><i class=\"' + state.id + '\"></i>'+
                                      ' - ' + state.text + '</span>'
                                    );
                                    return \$state;
                                  };</script>";
        self::addFooterCode($code);
        $code = '<script>$(document).ready(function() {$(\'#' . $id . '\').select2({templateSelection: getIconsSelectformatStateResult, templateResult: getIconsSelectformatStateResult,width: \'100%\'});});</script>';
        self::addFooterCode($code);
        return self::getSelectSearchable($icons, $name, $selected, $id, $class . " iconSelect", true);
    }

    static function getAvilableFlags() {
        global $global;
        $flags = array();
        include_once $global['systemRootPath'] . 'objects/bcp47.php';
        $files = _glob("{$global['systemRootPath']}locale", '/^[a-z]{2}(_.*)?.php$/');
        foreach ($files as $filename) {
            $filename = basename($filename);
            $fileEx = basename($filename, ".php");

            $name = $global['bcp47'][$fileEx]['label'];
            $flag = $global['bcp47'][$fileEx]['flag'];

            $flags[$fileEx] = array(json_encode(array('text' => $name, 'icon' => "flagstrap-icon flagstrap-{$flag}")), $fileEx, 'val3-' . $name);
        }
        return $flags;
    }

    static function getAllFlags() {
        global $global;
        if (empty($global)) {
            $global = [];
        }
        $flags = array();
        include_once $global['systemRootPath'] . 'objects/bcp47.php';
        foreach ($global['bcp47'] as $key => $filename) {

            $name = $filename['label'];
            $flag = $filename['flag'];

            $flags[$key] = array(json_encode(array('text' => $name, 'icon' => "flagstrap-icon flagstrap-{$flag}")), $key, 'val3-' . $name);
        }
        return $flags;
    }

    static function getLangsSelect($name, $selected = "", $id = "", $class = "navbar-btn", $flagsOnly = false, $getAll = false) {
        global $getLangsSelect;
        $getLangsSelect = 1;
        if ($getAll) {
            $flags = self::getAllFlags();
        } else {
            $flags = self::getAvilableFlags();
        }
        if (empty($id)) {
            $id = uniqid();
        }
        if ($selected == 'us') {
            $selected = 'en_US';
        }
        $selected = revertLangString($selected);
        //var_dump($selected, $flags[$selected], $flags);
        if (!empty($flags[$selected])) {
            $selectedJson = _json_decode($flags[$selected][0]);
            $selectedJsonIcon = $selectedJson->icon;
        } else {
            $selectedJsonIcon = '';
        }

        $html = '<div class="btn-group" id="div_' . $id . '">
            <input type="hidden" name="' . $name . '" value="' . $selected . '" id="' . $id . '"/>
            <button type="button" class="btn btn-default dropdown-toggle ' . $class . '" data-toggle="dropdown" aria-expanded="true">
                <span class="flag"><i class="selectedflagicon ' . $selectedJsonIcon . '"></i></span> <span class="caret"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right dropdown-menu-arrow" role="menu">';

        $selfURI = getSelfURI();
        foreach ($flags as $key => $value) {
            $info = json_decode($value[0]);
            $url = addQueryStringParameter($selfURI, 'lang', $key);

            $active = '';
            if ($selectedJsonIcon === $info->icon) {
                $active = 'active';
            }

            $html .= '<li class="dropdown-submenu ' . $active . '">
                    <a tabindex="-1" rel="nofollow" hreflang="' . $key . '" href="' . $url . '" value="' . $key . '" onclick="$(\'#div_' . $id . ' > button > span.flag\').html($(this).find(\'span.flag\').html());$(\'input[name=' . $name . ']\').val(\'' . $key . '\');">
                        <span class="flag"><i class="' . $info->icon . '" aria-hidden="true"></i></span> ' . $info->text . '</a>
                    </li>';
        }

        $html .= '</ul></div>';
        return $html;
    }

    static function getUserSelect($name, $users_id_list, $selected = "", $id = "", $class = "") {
        $elements = array();
        foreach ($users_id_list as $users_id) {
            $name = User::getNameIdentificationById($users_id);
            $photo = User::getPhoto($users_id);
            $elements[$users_id] = htmlentities("<img src='{$photo}' class='img img-responsive pull-left' style='max-height:20px;margin-top: 2px;'> {$name}");
            if ($users_id == User::getId()) {
                $elements[$users_id] .= " (Me)";
            }
        }
        if (empty($id)) {
            $id = uniqid();
        }
        $methodName = __FUNCTION__;
        $code = "<script>function {$methodName}formatStateResult (state) {
                                    if (!state.id) {
                                      return state.text;
                                    }
                                    var \$state = $(
                                      '<span>' + state.text + '</span>'
                                    );
                                    return \$state;
                                  };</script>";
        self::addFooterCode($code);
        $code = '<script>$(document).ready(function() {$(\'#' . $id . '\').select2({templateSelection: ' . $methodName . 'formatStateResult, templateResult: ' . $methodName . 'formatStateResult,width: \'100%\'});});</script>';
        self::addFooterCode($code);
        return self::getSelectSearchable($elements, $name, $selected, $id, $class, true);
    }

    static function getCategorySelect($name, $selected = "", $id = "", $class = "") {
        $parentsOnly = @$_GET['parentsOnly'];
        unset($_GET['parentsOnly']);
        $rows = Category::getAllCategories(true, false);
        $_GET['parentsOnly'] = $parentsOnly;
        //var_dump($rows);exit;
        //array_multisort(array_column($rows, 'hierarchyAndName'), SORT_ASC, $rows);
        $cats = array();
        foreach ($rows as $value) {
            $cats[$value['id']] = htmlentities("<i class='{$value['iconClass']}'></i> " . $value['hierarchyAndName']);
        }
        if (empty($id)) {
            $id = uniqid();
        }
        $methodName = __FUNCTION__;
        $code = "<script>function {$methodName}formatStateResult (state) {
                                    if (!state.id) {
                                      return state.text;
                                    }
                                    var \$state = $(
                                      '<span>' + state.text + '</span>'
                                    );
                                    return \$state;
                                  };";
        self::addFooterCode($code);
        $code = '$(document).ready(function() {$(\'#' . $id . '\').select2({templateSelection: ' . $methodName . 'formatStateResult, templateResult: ' . $methodName . 'formatStateResult,width: \'100%\'});});</script>';
        self::addFooterCode($code);
        return self::getSelectSearchable($cats, $name, $selected, $id, $class, true);
    }

    static function getUserGroupsSelect($name, $selected = "", $id = "", $class = "") {
        $rows = UserGroups::getAllUsersGroupsArray();
        if (empty($id)) {
            $id = uniqid();
        }
        $methodName = __FUNCTION__;
        $code = "<script>function {$methodName}formatStateResult (state) {
                                    if (!state.id) {
                                      return state.text;
                                    }
                                    var \$state = $(
                                      '<span>' + state.text + '</span>'
                                    );
                                    return \$state;
                                  };";
        self::addFooterCode($code);
        $code = '$(document).ready(function() {$(\'#' . $id . '\').select2({templateSelection: ' . $methodName . 'formatStateResult, templateResult: ' . $methodName . 'formatStateResult,width: \'100%\'});});</script>';
        self::addFooterCode($code);
        return self::getSelectSearchable($rows, $name, $selected, $id, $class, true);
    }

    public function getFooterCode() {
        global $global;

        $obj = $this->getDataObject();
        $content = '';
        if (!empty($global['getSelectSearchable'])) {
            $content .= '<script src="' . getURL('view/js/select2/select2.min.js') . '"></script>';
            // $content .= '<script>$(document).ready(function() {$(\'.js-select-search\').select2();});</script>';
        }

        if (!empty($obj->enableAccessibility)) {

            $file = $global['systemRootPath'] . 'plugin/Layout/accessibility/accessibility.php';
            $content .= getIncludeFileContent($file);
        }


        $content .= self::_getFooterCode();
        return $content;
    }

    private static function addFooterCode($code) {
        global $LayoutaddFooterCode;
        if (!isset($LayoutaddFooterCode)) {
            $LayoutaddFooterCode = array();
        }
        $LayoutaddFooterCode[] = $code;
    }

    private static function _getFooterCode() {
        global $LayoutaddFooterCode;
        if (!isset($LayoutaddFooterCode)) {
            return "";
        }
        $LayoutaddFooterCode = array_unique($LayoutaddFooterCode);
        return implode(PHP_EOL, $LayoutaddFooterCode);
    }

    public function getHTMLMenuRight() {
        global $global;
        $obj = $this->getDataObject();
        if (empty($obj->showButtonNotification)) {
            return false;
        }
        include $global['systemRootPath'] . 'plugin/Layout/menuRight.php';
    }

    public function navBarAfter() {
        global $global;
        $obj = $this->getDataObject();
        $content = '';
        if (!AVideoPlugin::isEnabledByName('YouPHPFlix2') && !empty($obj->categoriesTopButtons)) {
            if (empty($obj->categoriesTopButtonsShowOnlyOnFirstPage) || isFirstPage()) {
                $content .= getIncludeFileContent($global['systemRootPath'] . 'plugin/Layout/categoriesTopButtons.php');
            }
        }
        if (!empty($obj->showSearchOptionsBelowNavbar) && isFirstPage()) {
            $content .= getIncludeFileContent($global['systemRootPath'] . 'plugin/Layout/searchOptions.php');
        }
        return $content;
    }

    static function getUserAutocomplete($default_users_id = 0, $id = '', $parameters = array(), $jsFunctionForSelectCallback = '') {
        global $global;
        $default_users_id = intval($default_users_id);
        if (empty($id)) {
            $id = 'getUserAutocomplete_' . uniqid();
        }
        include $global['systemRootPath'] . 'plugin/Layout/userAutocomplete.php';
        return "updateUserAutocomplete{$id}();";
    }

    static function getVideoAutocomplete($default_videos_id = 0, $id = '', $parameters = array(), $jsFunctionForSelectCallback = '') {
        global $global;
        $default_videos_id = intval($default_videos_id);
        if (empty($id)) {
            $id = 'getVideoAutocomplete_' . uniqid();
        }
        include $global['systemRootPath'] . 'plugin/Layout/videoAutocomplete.php';
        return "updateVideoAutocomplete{$id}();";
    }

    static function organizeHTML($html) {
        global $global; // add socket twice on live page
        //return $html;
        if (isBot()) {
            //var_dump('doNOTOrganizeHTML');exit;
            return $html . PHP_EOL . '<!-- Layout::organizeHTML isBot -->';
        }
        if (!empty($global['doNOTOrganizeHTML'])) {
            //var_dump('doNOTOrganizeHTML');exit;
            return $html . PHP_EOL . '<!-- Layout::organizeHTML doNOTOrganizeHTML -->';
        }
        if (!empty($_REQUEST['debug'])) {
            //var_dump('doNOTOrganizeHTML');exit;
            return $html . PHP_EOL . '<!-- Layout::organizeHTML debug -->';
        }
        self::$tags = array();
        //return $html;
        //var_dump($html);exit;
        $html = self::getTagsLinkCSS($html);
        $html = self::getTagsScript($html);
        $html = self::separeteTag($html, 'style');
        $html = self::separeteTag($html, 'script');
        //$html = preg_replace('/<script.*><\/script>/i', '', $html);
        //return $html;
        //var_dump(self::$tags['script']);exit;
        //var_dump(self::$tags['tagscript']);exit;
        if (!empty(self::$tags['tagcss'])) {
            self::$tags['tagcss'] = self::removeDuplicated(self::$tags['tagcss']);
            
            usort(self::$tags['tagcss'], "_sortCSS");
            
            $html = str_replace('</head>', PHP_EOL . implode(PHP_EOL, array_unique(self::$tags['tagcss'])) . '</head>', $html);
        }
        //return $html;
        if (!empty(self::$tags['style'])) {
            $html = str_replace('</head>', '<style>' . PHP_EOL . implode(PHP_EOL, array_unique(self::$tags['style'])) . '</style></head>', $html);
        }
        if (!empty(self::$tags['tagscript'])) {
            self::$tags['tagscript'] = self::removeDuplicated(self::$tags['tagscript']);
            usort(self::$tags['tagscript'], "_sortJS");
            $html = str_replace('</body>', PHP_EOL . implode(PHP_EOL, array_unique(self::$tags['tagscript'])) . '</body>', $html);
        }
        if (!empty(self::$tags['script'])) {
            $html = str_replace('</body>', '<script>' . PHP_EOL . implode(PHP_EOL, array_unique(self::$tags['script'])) . '</script></body>', $html);
        }
        $html = self::removeExtraSpacesFromHead($html);
        $html = self::removeExtraSpacesFromScript($html);
        //echo $html;exit;
        return $html;
    }

    private static function tryToReplace($search, $replace, $subject) {
        if (true || self::codeIsValid($subject)) {
            $newSubject = str_replace($search, $replace, $subject, $count);
            return ['newSubject' => $newSubject, 'success' => $count];
        } else {
            _error_log('organizeHTML: Invalid code: ' . $subject);
            return ['newSubject' => $subject, 'success' => false];
        }
    }

    private static function codeIsValid($string) {
        $len = strlen($string);
        $stack = array();
        for ($i = 0; $i < $len; $i++) {
            switch ($string[$i]) {
                case '{':
                    array_push($stack, 0);
                    break;
                case '}':
                    if (array_pop($stack) !== 0)
                        return false;
                    break;
                case '(':
                    array_push($stack, 0);
                    break;
                case ')':
                    if (array_pop($stack) !== 0)
                        return false;
                    break;
                case '[':
                    array_push($stack, 1);
                    break;
                case ']':
                    if (array_pop($stack) !== 1)
                        return false;
                    break;
                default:
                    break;
            }
        }
        return (empty($stack));
    }

    static function removeExtraSpacesFromHead($html) {
        preg_match('/(<head.+<\/head>)/Usi', $html, $matches);
        $str = preg_replace('/[ \t]+/', ' ', $matches[0]);
        $str = preg_replace('/\n\s*\n+/', PHP_EOL, $matches[0]);
        //var_dump($str);exit;
        $html = str_replace($matches[0], $str, $html);
        return $html;
    }

    static function removeExtraSpacesFromScript($html) {
        preg_match_all('/(<script[^>]*>.+<\/script>)/Usi', $html, $matches);
        foreach ($matches as $value) {
            $str = preg_replace('/ +/', ' ', $value);
            $html = str_replace($value, $str, $html);
        }
        return $html;
    }

    static function getTagsLinkCSS($html) {
        $nonCriticalCSS = ' rel="preload" media="print" as="style" onload="this.media=\'all\'" ';
        preg_match_all('/<link[^>]+href=[^>]+>/Usi', $html, $matches);
        if (!empty($matches)) {
            foreach ($matches[0] as $value) {
                $response = self::tryToReplace($value, '', $html);
                if ($response['success']) {
                    if (strpos($value, 'rel="preload"') === false) {
                        $containsCritical = false;
                        foreach (self::$criticalCSS as $crit) {
                            if (strpos($value, $crit) !== false) {
                                $containsCritical = true;
                                break;
                            }
                        }
                        if (!$containsCritical) {
                            $value = str_replace('type="text/css"', 'type="text/css" ' . $nonCriticalCSS, $value);
                        }
                    }
                    self::addTag('tagcss', $value);
                    $html = $response['newSubject'];
                }
            }
        }
        return $html;
    }

    static function getTagsScript($html) {
        preg_match_all('/<script[^<]* src=[^<]+<\/script>/Usi', $html, $matches);
        if (!empty($matches)) {
            foreach ($matches[0] as $key => $value) {
                // ignore google analitics
                if (!self::shouldIgnoreJS($value)) {
                    $response = self::tryToReplace($value, '', $html);
                    if ($response['success']) {
                        self::addTag('tagscript', $value);
                        $html = $response['newSubject'];
                    }
                }
            }
        }
        return $html;
    }

    static function separeteTag($html, $tag) {
        $reg = '/<' . $tag . '[^>]*>(.*)<\/' . $tag . '>/Usi';
        //var_dump($reg, $html);
        preg_match_all($reg, $html, $matches);
        //var_dump($matches);exit;
        if (!empty($matches)) {
            foreach ($matches[0] as $key => $value) {
                if (!self::shouldIgnoreJS($value)) {
                    $response = self::tryToReplace($value, '', $html);
                    if ($response['success']) {
                        self::addTag($tag, $matches[1][$key]);
                        $html = $response['newSubject'];
                    }
                }
            }
        }
        return $html;
    }

    static function shouldIgnoreJS($tag) {
        if (
                preg_match('/application.+json/i', $tag) ||
                preg_match('/function gtag\(/i', $tag) ||
                preg_match('/<script async/i', $tag) ||
                preg_match('/doNotSepareteTag/', $tag) ||
                preg_match('/window.googletag/', $tag) ||
                preg_match('/document\.write/', $tag) ||
                isBot()
        ) {
            return true;
        }
        return false;
    }

    static public function addTag($tag, $value) {
        if (empty($value)) {
            return false;
        }
        if (!isset(self::$tags[$tag])) {
            self::$tags[$tag] = array();
        }
        self::$tags[$tag][] = $value;
        return true;
    }

    public function getEnd() {
        global $global;
        $html = _ob_get_clean();
        $html = self::organizeHTML($html);
        //_ob_clean();
        _ob_start();
        echo '<!-- Layout organizeHTML start -->' . PHP_EOL . $html . PHP_EOL . '<!-- Layout organizeHTML END -->';
    }

    static private function removeDuplicated($list) {
        $cleanList = array();
        $srcList = array();
        foreach ($list as $key => $value) {
            preg_match('/<script.+src=["\']([^"\']+)["\']/i', $value, $matches);
            if (!empty($matches[1])) {
                if (!in_array($matches[1], $srcList)) {
                    $cleanList[] = $value;
                    $srcList[] = $matches[1];
                }
            } else {
                preg_match('/<link.+href=["\']([^"\']+)["\']/i', $value, $matches);
                if (!empty($matches[1])) {
                    if (!in_array($matches[1], $srcList)) {
                        $cleanList[] = $value;
                        $srcList[] = $matches[1];
                    }
                }
            }
        }
        //var_dump($srcList);exit;
        return $cleanList;
    }

    static function getSuggestedButton($videos_id, $class = 'btn btn-xs') {
        global $global;
        if (empty($videos_id)) {
            return '';
        }
        if (!Permissions::canAdminVideos()) {
            return '';
        }
        $varsArray = array('videos_id' => $videos_id, 'class' => $class);
        $filePath = $global['systemRootPath'] . 'plugin/Layout/suggestedButton.php';
        return getIncludeFileContent($filePath, $varsArray);
    }

    static function getCategoriesToSearch() {
        global $global;
        $global['doNotSearch'] = 1;
        $categories = Category::getAllCategories(false, true);
        $global['doNotSearch'] = 0;
        return $categories;
    }

    static function getSearchOptions($name) {
        $divs = array();
        $id = str_replace('[]', '', $name) . uniqid();
        foreach (Layout::$searchOptions as $key => $value) {
            $divs[] = '<div class="form-check">
                            <input class="form-check-input" type="checkbox" value="' . $value['value'] . '" id="' . $id . '_' . $key . '" name="' . $name . '">
                            <label class="form-check-label" for="' . $id . '_' . $key . '">
                            ' . __($value['text']) . ' 
                            </label>
                       </div>';
        }
        return $divs;
    }

    static function getSearchCategories($name) {
        global $global;
        $divs = array();
        $id = str_replace('[]', '', $name) . uniqid();
        $divs[] = '<div class="form-check">
                        <input class="form-check-input" type="radio" id="' . $id . '" name="' . $name . '" checked value="">
                        <label class="form-check-label" for="' . $id . '">
                            <i class="fas fa-list"></i> ' . __('All') . '
                        </label>
                    </div>';
        $global['doNotSearch'] = 1;
        $categories_edit = Category::getAllCategories(false, true);
        $global['doNotSearch'] = 0;
        foreach ($categories_edit as $key => $value) {
            $divs[] = '<div class="form-check">
                            <input class="form-check-input" type="radio" value="' . $value['clean_name'] . '" id="' . $id . '_' . $key . '" name="' . $name . '">
                            <label class="form-check-label" for="' . $id . '_' . $key . '">
                                <i class="' . $value['iconClass'] . '"></i> ' . __($value['hierarchyAndName']) . '
                            </label>
                        </div>';
        }
        return $divs;
    }

    static function getSearchDateTime($name) {
        global $global;
        $divs = array();
        $id = str_replace('[]', '', $name) . uniqid();
        $divs[] = '<div class="form-check">
                        <input class="form-check-input" type="radio" id="' . $id . '" name="' . $name . '" checked value="">
                        <label class="form-check-label" for="' . $id . '">
                            ' . __('All') . '
                        </label>
                    </div>';

        $divs[] = '<div class="form-check">
                    <input class="form-check-input" type="radio" value="1" id="' . $id . '_1" name="' . $name . '">
                    <label class="form-check-label" for="' . $id . '_1">
                        1 ' . __('Day') . '
                    </label>
                </div>';
        for ($i = 5; $i <= 30; $i += 5) {
            $divs[] = '<div class="form-check">
                            <input class="form-check-input" type="radio" value="' . $i . '" id="' . $id . '_' . $i . '" name="' . $name . '">
                            <label class="form-check-label" for="' . $id . '_' . $i . '">
                                ' . $i . ' ' . __('Days') . '
                            </label>
                        </div>';
        }


        $divs[] = '<div class="form-check">
                    <input class="form-check-input" type="radio" value="30" id="' . $id . '_30" name="' . $name . '">
                    <label class="form-check-label" for="' . $id . '_30">
                        1 ' . __('Month') . '
                    </label>
                </div>';
        for ($i = 60; $i <= 360; $i += 30) {
            $divs[] = '<div class="form-check">
                            <input class="form-check-input" type="radio" value="' . $i . '" id="' . $id . '_' . $i . '" name="' . $name . '">
                            <label class="form-check-label" for="' . $id . '_' . $i . '">
                                ' . ($i / 30) . ' ' . __('Months') . '
                            </label>
                        </div>';
        }
        return $divs;
    }

    static function getSearchViews($name) {
        global $global;
        $video = Video::getVideoWithMoreViews();

        if ($video['views_count'] > 10) {
            $step = $video['views_count'] / 10;
        } else {
            $step = 1;
        }

        if ($step >= 10000) {
            $step = round($step / 10000) * 10000;
        } else if ($step >= 1000) {
            $step = round($step / 1000) * 1000;
        } else if ($step >= 100) {
            $step = round($step / 100) * 100;
        }

        $divs = array();
        $id = str_replace('[]', '', $name) . uniqid();
        $divs[] = '<div class="form-check">
                        <input class="form-check-input" type="radio" id="' . $id . '" name="' . $name . '" checked value="">
                        <label class="form-check-label" for="' . $id . '">
                            ' . __('All') . '
                        </label>
                    </div>';
        for ($i = $step; $i <= $video['views_count']; $i += $step) {
            $count = intval($i);
            $divs[] = '<div class="form-check">
                            <input class="form-check-input" type="radio" value="' . $count . '" id="' . $id . '_' . $count . '" name="' . $name . '">
                            <label class="form-check-label" for="' . $id . '_' . $count . '">
                                ' . $count . ' ' . __('Views or more') . '
                            </label>
                        </div>';
        }

        return $divs;
    }

    static function getSearchTags($name) {
        global $global;
        if (!class_exists('TagsHasVideos')) {
            return array();
        }
        $global['doNotSearch'] = 1;
        $tags = TagsHasVideos::getAllWithVideo();
        $global['doNotSearch'] = 0;
        if (empty($tags)) {
            return array();
        }
        $divs = array();
        $id = str_replace('[]', '', $name) . uniqid();
        $divs[] = '<div class="form-check">
                        <input class="form-check-input" type="radio" id="' . $id . '" name="' . $name . '" checked value="">
                        <label class="form-check-label" for="' . $id . '">
                            <i class="fas fa-tags"></i> ' . __('All') . '
                        </label>
                    </div>';
        foreach ($tags as $key => $value) {
            $divs[] = '<div class="form-check">
                            <input class="form-check-input" type="radio" value="' . $value['id'] . '" id="' . $id . '_' . $key . '" name="' . $name . '">
                            <label class="form-check-label" for="' . $id . '_' . $key . '">
                                <i class="fas fa-tag"></i> ' . __($value['name']) . '
                            </label>
                        </div>';
        }
        return $divs;
    }

    static function getSearchHTML($elements, $name) {
        $id = 'search_' . uniqid();
        $class = 'searchHTML' . str_replace('[]', '', $name);
        ?>
        <div class="panel panel-default searchHTML <?php echo $class; ?>" id="<?php echo $id; ?>-panel" style="margin: 0;">
            <div class="panel-heading">
                <input class="form-control" type="text" id="<?php echo $id; ?>-search" placeholder="<?php echo __('Search'); ?>..." style="float: unset;">
            </div>
            <div class="panel-body <?php echo $id; ?>">
        <?php echo implode('', $elements); ?>
            </div>
        </div>
        <script>
            $(document).ready(function () {
                searchInList('#<?php echo $id; ?>-search', '.<?php echo $id; ?> .form-check');
                $('#<?php echo $id; ?>-panel .form-check-input').on('change', function () {
                    var checked = $(this).prop('checked');
                    var value = $(this).val();
                    $('.<?php echo $class; ?> input[type="checkbox"], .<?php echo $class; ?> input[type="radio"]').each(function () {
                        if ($(this).val() === value) {
                            $(this).prop('checked', checked);
                        }
                    });

                    const checkedValues = $('#<?php echo $id; ?>-panel .form-check-input').filter(':checked').map(function () {
                        return this.value;
                    }).get();

                    console.log('#<?php echo $id; ?>-panel .form-check-input', checkedValues, JSON.stringify(checkedValues));
                    Cookies.set('<?php echo $name; ?>', JSON.stringify(checkedValues), {
                        expires: 365,
                        path: '/'
                    });
                    setSearchFilterIcon();
                });

                var savedCookies = Cookies.get('<?php echo $name; ?>');
                if (savedCookies) {
                    var checkedValues = JSON.parse(savedCookies);
                    $('#<?php echo $id; ?>-panel .form-check-input').each(function () {
                        this.checked = checkedValues.includes(this.value);
                    });
                }
                setSearchFilterIcon();
            });
        </script>
        <?php
    }

    static function getSearchOptionHTML() {
        $name = 'searchFieldsNames[]';
        $elements = self::getSearchOptions($name);
        self::getSearchHTML($elements, $name);
    }

    static function getSearchCategoriesHTML() {
        $name = 'catName';
        $elements = self::getSearchCategories($name);
        self::getSearchHTML($elements, $name);
    }

    static function getSearchTagsHTML() {
        $name = 'tags_id';
        $elements = self::getSearchTags($name);
        self::getSearchHTML($elements, $name);
    }

    static function getSearchDateHTML() {
        $name = 'created';
        $elements = self::getSearchDateTime($name);
        self::getSearchHTML($elements, $name);
    }

    static function getSearchViewsHTML() {
        $name = 'minViews';
        $elements = self::getSearchViews($name);
        self::getSearchHTML($elements, $name);
    }

}

function compareOrder($a, $b, $firstOrder, $lastOrder) {
    $aIndexFirst = $aIndexLast = $bIndexFirst = $bIndexLast = null;

    // Check if $a and $b are in $firstOrder
    foreach ($firstOrder as $index => $value) {
        if (strpos($a, $value) !== false)
            $aIndexFirst = $index;
        if (strpos($b, $value) !== false)
            $bIndexFirst = $index;
    }

    // Check if $a and $b are in $lastOrder
    foreach ($lastOrder as $index => $value) {
        if (strpos($a, $value) !== false)
            $aIndexLast = $index;
        if (strpos($b, $value) !== false)
            $bIndexLast = $index;
    }

    // Handle case when $a and $b are in $firstOrder
    if ($aIndexFirst !== null && $bIndexFirst !== null) {
        return $aIndexFirst - $bIndexFirst; // order by position in $firstOrder
    }
    if ($aIndexFirst !== null) {
        return -1; // $a in $firstOrder, $b not
    }
    if ($bIndexFirst !== null) {
        return 1; // $b in $firstOrder, $a not
    }

    // Handle case when $a and $b are in $lastOrder
    if ($aIndexLast !== null && $bIndexLast !== null) {
        return $aIndexLast - $bIndexLast; // order by position in $lastOrder
    }
    if ($aIndexLast !== null) {
        return 1; // $a in $lastOrder, $b not
    }
    if ($bIndexLast !== null) {
        return -1; // $b in $lastOrder, $a not
    }

    // If none of the above conditions met, order doesn't matter
    return 0;
}

function _sortJS($a, $b) {
    $firstOrder = [
        "jquery.min.js",
        "jquery-ui",
        "js/bootstrap.min.js",
        "js.cookie.js",
        "node_modules/video.js/dist/video",
        "videojs-contrib-ads",
        "videojs-ima",
        "moment.min.js",
        "moment-timezone.min.js",
        "moment-timezone-with-data.min.js",
        "moment/locale/",
        "jquery.lazy.min.js",
        "jquery.lazy.plugins.min.js",
        "flickity.pkgd.min.js",
        "flickity-bg-lazyload/bg-lazyload.js",
    ];
    $lastOrder = [
        "js/script.js",
        "/plugin/",
    ];
    return compareOrder($a, $b, $firstOrder, $lastOrder);
}

function _sortCSS($a, $b) {
    $firstOrder = array(
        'view/bootstrap/css/bootstrap.min.css',
        'view/css/custom',
        'videos/cache/custom.css',
        'view/css/navbar.css',
        'plugin/Gallery/style.css',
        'node_modules/animate.css/animate',
        'view/css/main.css',
    );
    $lastOrder = array(
        'node_modules/video.js/dist/video-js.min.css', 
        'videojs',
        'plugin/PlayerSkins/player.css',
        'plugin/PlayerSkins/skins/', 
    );;
    return compareOrder($a, $b, $firstOrder, $lastOrder);
}