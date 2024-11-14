<?php
require_once __DIR__.'/../videos/configuration.php';

if(!User::isAdmin()){
    forbiddenPage('You Must be admin');
}


if(!empty($global['disableAdvancedConfigurations'])){
    forbiddenPage('Configuration disabled');
}

use iamcal\SQLParser;

$parser = new SQLParser();

$pluginName = $_REQUEST['pluginName'];
$sql = $_REQUEST['createTableSQL'];

$parser->parse($sql);
//var_dump($parser->tables);exit;
$pluginDir = __DIR__."/plugins/{$pluginName}/";
echo $pluginDir, PHP_EOL;
_mkdir($pluginDir);

$pluginDirInstall = "{$pluginDir}/install/";
_mkdir($pluginDirInstall);
file_put_contents($pluginDirInstall . "install.sql", $sql);

$pluginDirObjects = "{$pluginDir}/Objects/";
_mkdir($pluginDirObjects);

$pluginDirView = "{$pluginDir}/View/";
_mkdir($pluginDirView);

$includeTables = array();
$editorNavTabs = array();
$editorNavContent = array();
$active = true;
foreach ($parser->tables as $value) {
    $classname = ucwords($value['name']);
    $tableName = ucwords(str_replace("_", " ", $value['name']));
    $editorNavTabs[] = "<li class=\"" . ($active ? "active" : "") . "\"><a data-toggle=\"tab\" href=\"#{$classname}\"><?php echo __(\"{$tableName}\"); ?></a></li>";
    $editorNavContent[] = "<div id=\"{$classname}\" class=\"tab-pane fade " . ($active ? "in active" : "") . "\" style=\"padding: 10px;\">
                            <?php
                            include \$global['systemRootPath'] . 'plugin/{$pluginName}/View/{$classname}/index_body.php';
                            ?>
                        </div>";
    $active = false;
    $includeTables[] = "require_once \$global['systemRootPath'] . 'plugin/{$pluginName}/Objects/{$classname}.php';";
    $columnsVars = array();
    $columnsString = array();
    $columnsGet = array();
    $columnsSet = array();
    $columnsForm = array();
    $columnsFooter = array();
    $columnsGrid = array();
    $columnsClearJQuery = array();
    $columnsDatatable = array();
    $columnsEdit = array();
    $columnsAdd = array();
    $columnsGetAll = array();

    foreach ($value['fields'] as $value2) {
        if ($value2['name'] == 'created' || $value2['name'] == 'modified') {
            continue;
        }
        $columnsVars[] = '$' . $value2['name'];
        $type = strtolower($value2['type']);
        if ($type == 'text' || $type == 'varchar') {
            $columnsString[] = "'{$value2['name']}'";
        }
        $fieldName = ucwords(str_replace("_", " ", $value2['name']));
        $columnsClearJQuery[] = "$('#{$classname}{$value2['name']}').val('');";
        $columnsEdit[] = "$('#{$classname}{$value2['name']}').val(data.{$value2['name']});";
        if ($value2['name'] != 'id') {
            $columnsAdd[] = "\$o->set" . ucfirst($value2['name']) . "(\$_POST['{$value2['name']}']);";
        }
        
        if ($type == 'int' || $type == 'tinyint') {
            $columnsGet[] = " 
    function get" . ucfirst($value2['name']) . "() {
        return intval(\$this->{$value2['name']});
    }  ";

            $columnsSet[] = " 
    function set" . ucfirst($value2['name']) . "(\${$value2['name']}) {
        \$this->{$value2['name']} = intval(\${$value2['name']});
    } ";
        } else if ($type == 'float') {
            $columnsGet[] = " 
    function get" . ucfirst($value2['name']) . "() {
        return floatval(\$this->{$value2['name']});
    }  ";

            $columnsSet[] = " 
    function set" . ucfirst($value2['name']) . "(\${$value2['name']}) {
        \$this->{$value2['name']} = floatval(\${$value2['name']});
    } ";
        } else {
            $columnsGet[] = " 
    function get" . ucfirst($value2['name']) . "() {
        return \$this->{$value2['name']};
    }  ";

            $columnsSet[] = " 
    function set" . ucfirst($value2['name']) . "(\${$value2['name']}) {
        \$this->{$value2['name']} = \${$value2['name']};
    } ";
        }

        if(preg_match("/^(.*)_id/i", $value2['name'], $matches)){
            
            $columnsGetAll[] = "static function getAll" . ucfirst($matches[1]) . "() {
        global \$global;
        \$table = \"{$matches[1]}\";
        \$sql = \"SELECT * FROM {\$table} WHERE 1=1 \";

        \$sql .= self::getSqlFromPost();
        \$res = sqlDAL::readSql(\$sql);
        \$fullData = sqlDAL::fetchAllAssoc(\$res);
        sqlDAL::close(\$res);
        \$rows = array();
        if (\$res != false) {
            foreach (\$fullData as \$row) {
                \$rows[] = \$row;
            }
        } else {
            /**
             * 
             * @var array \$global
             * @var object \$global['mysqli'] 
             */
            _error_log(\$sql . ' Error : (' . \$global['mysqli']->errno . ') ' . \$global['mysqli']->error);
        }
        return \$rows;
    }";
            $columnsForm[] = '<div class="form-group col-sm-12">
                                    <label for="' . $classname . $value2['name'] . '"><?php echo __("' . $fieldName . '"); ?>:</label>
                                    <select class="form-control input-sm" name="' . $value2['name'] . '" id="' . $classname . $value2['name'] . '">
                                        <?php
                                        $options = '.$classname.'::getAll' . ucfirst($matches[1]) . '();
                                        foreach ($options as $value) {
                                            echo \'<option value="\'.$value[\'id\'].\'">\'.$value[\'id\'].\'</option>\';
                                        }
                                        ?>
                                    </select>
                                </div>';
        }else if ($value2['name'] == 'id') {
            $columnsGrid[] = "<th>#</th>";
            $columnsDatatable[] = '{"data": "id"}';
            $columnsForm[] = '<input type="hidden" name="id" id="' . $classname . 'id" value="" >';
        } else if ($value2['name'] == 'status') {
            $columnsGrid[] = '<th><?php echo __("' . $fieldName . '"); ?></th>';
            $columnsDatatable[] = '{"data": "' . $value2['name'] . '"}';
            $columnsForm[] = '<div class="form-group col-sm-12">
                                        <label for="status"><?php echo __("Status"); ?>:</label>
                                        <select class="form-control input-sm" name="status" id="' . $classname . 'status">
                                            <option value="a"><?php echo __("Active"); ?></option>
                                            <option value="i"><?php echo __("Inactive"); ?></option>
                                        </select>
                                    </div>';
        } else {
            $columnsGrid[] = '<th><?php echo __("' . $fieldName . '"); ?></th>';
            $columnsDatatable[] = '{"data": "' . $value2['name'] . '"}';
            if($type == 'text'){
                $columnsForm[] = '<div class="form-group col-sm-12">
                                        <label for="' . $classname . $value2['name'] . '"><?php echo __("' . $fieldName . '"); ?>:</label>
                                        <textarea id="' . $classname . $value2['name'] . '" name="' . $value2['name'] . '" class="form-control input-sm" placeholder="<?php echo __("' . $fieldName . '"); ?>" required="true"></textarea>
                                    </div>';
            }else if($type == 'int'){
                $columnsForm[] = '<div class="form-group col-sm-12">
                                        <label for="' . $classname . $value2['name'] . '"><?php echo __("' . $fieldName . '"); ?>:</label>
                                        <input type="number" step="1" id="' . $classname . $value2['name'] . '" name="' . $value2['name'] . '" class="form-control input-sm" placeholder="<?php echo __("' . $fieldName . '"); ?>" required="true">
                                    </div>';
            }else if($type == 'float'){
                $columnsForm[] = '<div class="form-group col-sm-12">
                                        <label for="' . $classname . $value2['name'] . '"><?php echo __("' . $fieldName . '"); ?>:</label>
                                        <input type="number" step="0.01" id="' . $classname . $value2['name'] . '" name="' . $value2['name'] . '" class="form-control input-sm" placeholder="<?php echo __("' . $fieldName . '"); ?>" required="true">
                                    </div>';
                
            }else if($type == 'datetime'){
                $columnsForm[] = '<div class="form-group col-sm-12">
                                        <label for="' . $classname . $value2['name'] . '"><?php echo __("' . $fieldName . '"); ?>:</label>
                                        <input type="text" id="' . $classname . $value2['name'] . '" name="' . $value2['name'] . '" class="form-control input-sm" placeholder="<?php echo __("' . $fieldName . '"); ?>" required="true" autocomplete="off">
                                    </div>';
                $columnsFooter[] = '<script> $(document).ready(function () {$(\'#' . $classname . $value2['name'] . '\').datetimepicker({format: \'yyyy-mm-dd hh:ii\',autoclose: true });});</script>';
                
            }else{
                $columnsForm[] = '<div class="form-group col-sm-12">
                                        <label for="' . $classname . $value2['name'] . '"><?php echo __("' . $fieldName . '"); ?>:</label>
                                        <input type="text" id="' . $classname . $value2['name'] . '" name="' . $value2['name'] . '" class="form-control input-sm" placeholder="<?php echo __("' . $fieldName . '"); ?>" required="true">
                                    </div>';
            }
        }
    }

    $templatesDir = __DIR__.'/templates/';

    $classFile = "{$pluginDirObjects}{$classname}.php";
    $modelTemplate = file_get_contents("{$templatesDir}model.php");
    $search = array('{classname}', '{tablename}', '{pluginName}', '{columnsVars}', '{columnsString}', '{columnsGetAll}', '{columnsGet}', '{columnsSet}');
    $replace = array($classname, $value['name'], $pluginName, implode(",", $columnsVars), implode(",", $columnsString), implode(PHP_EOL, $columnsGetAll), implode(PHP_EOL, $columnsGet), implode(PHP_EOL, $columnsSet));
    $data = str_replace($search, $replace, $modelTemplate);
    file_put_contents($classFile, $data);


    $dir = "{$pluginDirView}{$classname}/";
    _mkdir($dir);

    $file = "{$dir}index.php";
    $modelTemplate = file_get_contents("{$templatesDir}index.php");
    $search = array('{classname}', '{pluginName}');
    $replace = array($classname, $pluginName);
    $data = str_replace($search, $replace, $modelTemplate);
    file_put_contents($file, $data);

    $file = "{$dir}index_body.php";
    $modelTemplate = file_get_contents("{$templatesDir}index_body.php");
    $search = array('{classname}','{tablename}', '{pluginName}', '{columnsForm}', '{columnsFooter}', '{columnsGrid}', '{$columnsClearJQuery}', '{columnsDatatable}', '{$columnsEdit}');
    $replace = array($classname,$value['name'], $pluginName, implode(PHP_EOL, $columnsForm), implode(PHP_EOL, $columnsFooter), implode(PHP_EOL, $columnsGrid), implode(PHP_EOL, $columnsClearJQuery), implode("," . PHP_EOL, $columnsDatatable), implode(PHP_EOL, $columnsEdit));
    $data = str_replace($search, $replace, $modelTemplate);
    file_put_contents($file, $data);

    $file = "{$dir}list.json.php";
    $modelTemplate = file_get_contents("{$templatesDir}list.json.php");
    $search = array('{classname}', '{pluginName}');
    $replace = array($classname, $pluginName);
    $data = str_replace($search, $replace, $modelTemplate);
    file_put_contents($file, $data);

    $file = "{$dir}delete.json.php";
    $modelTemplate = file_get_contents("{$templatesDir}delete.json.php");
    $search = array('{classname}', '{pluginName}');
    $replace = array($classname, $pluginName);
    $data = str_replace($search, $replace, $modelTemplate);
    file_put_contents($file, $data);

    $file = "{$dir}add.json.php";
    $modelTemplate = file_get_contents("{$templatesDir}add.json.php");
    $search = array('{classname}', '{pluginName}', '{columnsAdd}');
    $replace = array($classname, $pluginName, implode(PHP_EOL, $columnsAdd));
    $data = str_replace($search, $replace, $modelTemplate);
    file_put_contents($file, $data);
}

$pluginFile = "{$pluginDir}$pluginName.php";
$pluginTemplate = file_get_contents("{$templatesDir}plugin.php");
$search = array('{pluginName}', '{includeTables}', '{tablename}', '{uid}');
$replace = array($pluginName, implode(PHP_EOL, $includeTables), $pluginName, "5ee8405eaaa16");
$data = str_replace($search, $replace, $pluginTemplate);
file_put_contents($pluginFile, $data);

$pluginFile = "{$pluginDir}View/editor.php";
$pluginTemplate = file_get_contents("{$templatesDir}editor.php");
$search = array('{pluginName}', '{editorNavTabs}', '{editorNavContent}');
$replace = array($pluginName, implode(PHP_EOL, $editorNavTabs), implode(PHP_EOL, $editorNavContent));
$data = str_replace($search, $replace, $pluginTemplate);
file_put_contents($pluginFile, $data);

function _mkdir($dir) {
    if (!is_dir($dir)) {
        mkdir($dir);
    }
}
