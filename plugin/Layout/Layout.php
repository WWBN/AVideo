<?php

global $global;
require_once $global['systemRootPath'] . 'plugin/Plugin.abstract.php';

class Layout extends PluginAbstract {

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
        return $obj;
    }

    public function getPluginMenu() {
        global $global;
        return "";
        $filename = $global['systemRootPath'] . 'plugin/Customize/pluginMenu.html';
        return file_get_contents($filename);
    }

    public function getStart() {
        global $global;
        return "";
        $filename = "{$global['systemRootPath']}videos/cache/custom.css";
        if (!file_exists($filename)) {
            include $global['systemRootPath'] . 'plugin/Customize/sass/compile.php';
        }
        return false;
    }

    static function getIconsList() {
        global $global;
        include $global['systemRootPath'] . 'plugin/Layout/fontAwesomeFAB.php';
        // Fetch variables scss file in variable
        $font_vars = file_get_contents($global['systemRootPath'] . 'view/css/fontawesome-free-5.5.0-web/scss/_variables.scss');

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
                if(!in_array($className, $font_awesome_brands)){
                    $classNameFull = "fa fa-{$className}";
                }else{
                    $classNameFull = "fab fa-{$className}";
                }
                $fonts_list[$className] = array($className, $classNameFull, $t[1]);
            }
        }
        return $fonts_list;
    }

    static function getSelectSearchable($optionsArray, $name, $selected, $id="", $class = "", $placeholder=false) {
        global $global;
        $html = "";
        if (empty($global['getSelectSearchable'])) {
            $html .= '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />';
            $html .= '<style>.select2-selection__rendered {line-height: 36px !important;}.select2-selection {height: 38px !important;}</style>';
        }
        if(empty($class)){
            $class = "js-select-search";
        }
        $html .= '<select class="form-control ' . $class . '" name="' . $name . '" id="'.$id.'">';
        if($placeholder){
            $html .= '<option value=""> -- </option>';
        }
        foreach ($optionsArray as $key => $value) {
            $selectedString = "";
            if(is_array($value)){ // need this because of the category icons
                $_value = $value[1];
                $_text = $value[0];
            }else{
                $_value = $key;
                $_text = $value;
            }
            if($_value==$selected){
                $selectedString = "selected";
            }
            $html .= '<option value="' . $_value . '" '.$selectedString.'>' . $_text . '</option>';
        }
        $html .= '</select>';
        $global['getSelectSearchable'] = 1;
        return $html;
    }

    static function getIconsSelect($name, $selected="", $id="", $class = "") {
        global $getIconsSelect;
        $getIconsSelect = 1;
        $icons = self::getIconsList();
        if(empty($id)){
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
        $code = '<script>$(document).ready(function() {$(\'#'.$id.'\').select2({templateSelection: getIconsSelectformatStateResult, templateResult: getIconsSelectformatStateResult,width: \'100%\'});});</script>';
        self::addFooterCode($code);
        return self::getSelectSearchable($icons, $name, $selected, $id, $class." iconSelect", true);
    }

    static function getCategorySelect($name, $selected="", $id="", $class = "") {
        $rows = Category::getAllCategories(true, false);
        array_multisort(array_column($rows, 'hierarchyAndName'), SORT_ASC, $rows);
        $cats = array();
        foreach ($rows as $value) {
            $cats[$value['id']] = htmlentities( "<i class='{$value['iconClass']}'></i> ".$value['hierarchyAndName']);
        }
        if(empty($id)){
            $id = uniqid();
        }
        $code = "<script>function getCategorySelectformatStateResult (state) {
                                    if (!state.id) {
                                      return state.text;
                                    }
                                    var \$state = $(
                                      '<span>' + state.text + '</span>'
                                    );
                                    return \$state;
                                  };";
        self::addFooterCode($code);
        $code = '$(document).ready(function() {$(\'#'.$id.'\').select2({templateSelection: getCategorySelectformatStateResult, templateResult: getCategorySelectformatStateResult,width: \'100%\'});});</script>';
        self::addFooterCode($code);
        return self::getSelectSearchable($cats, $name, $selected, $id, $class, true);
    }

    public function getFooterCode() {
        global $global;

        $obj = $this->getDataObject();
        $content = '';
        if (!empty($global['getSelectSearchable'])) {
            $content .= '<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>';
            $content .= '<script>$(document).ready(function() {$(\'.js-select-search\').select2();});</script>';
        }
        
        $content .= self::_getFooterCode();
        return $content;
    }
    
    private static function addFooterCode($code){
        global $LayoutaddFooterCode;
        if(!isset($LayoutaddFooterCode)){
            $LayoutaddFooterCode = array();
        }
        $LayoutaddFooterCode[] = $code;
    }
    
    
    private static function _getFooterCode(){
        global $LayoutaddFooterCode;
        if(!isset($LayoutaddFooterCode)){
            return "";
        }
        $LayoutaddFooterCode = array_unique($LayoutaddFooterCode);
        return implode(PHP_EOL, $LayoutaddFooterCode);
    }

}
