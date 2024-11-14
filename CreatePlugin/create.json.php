<?php
require_once __DIR__.'/../videos/configuration.php';

header('Content-Type: application/json');

if(!User::isAdmin()){
    forbiddenPage('You Must be admin');
}

if(!empty($global['disableAdvancedConfigurations'])){
    forbiddenPage('Configuration disabled');
}

use iamcal\SQLParser;

try {
    $parser = new SQLParser();

    // Plugin name and SQL file (replace these with dynamic input as needed)
    $pluginName = $_REQUEST['pluginName'];
    $sql = $_REQUEST['createTableSQL'];

    // Sanitize and format plugin name
    $pluginName = preg_replace('/[^a-zA-Z0-9_]/', '', ucfirst($pluginName));
    if (!preg_match('/^[A-Z][A-Za-z0-9_]*$/', $pluginName)) {
        throw new Exception('Invalid plugin name. It must start with an uppercase letter and contain only letters, numbers, and underscores.');
    }

    $parser->parse($sql);

    // Define plugin directories
    $pluginZip = "plugins/{$pluginName}.zip";
    $pluginDir = "plugins/{$pluginName}/";
    _mkdir($pluginDir);

    $pluginDirInstall = "{$pluginDir}install/";
    _mkdir($pluginDirInstall);
    $installFilePath = $pluginDirInstall . "install.sql";
    file_put_contents($installFilePath, $sql);
    $response['createdFiles'][] = $installFilePath;

    $pluginDirObjects = "{$pluginDir}Objects/";
    _mkdir($pluginDirObjects);

    $pluginDirView = "{$pluginDir}View/";
    _mkdir($pluginDirView);

    // Initialize arrays to build templates and file paths
    $includeTables = [];
    $editorNavTabs = [];
    $editorNavContent = [];
    $active = true;
    
    $response['tables'] = array();

    // Process each table found in SQL
    foreach ($parser->tables as $table) {
        $tableName = $table['name'];
        $response['tables'][] = $tableName;  // Add table name to response

        $classname = ucwords($tableName);
        $includeTables[] = "require_once \$global['systemRootPath'] . 'plugin/{$pluginName}/Objects/{$classname}.php';";
        
        // HTML for editor navigation
        $editorNavTabs[] = "<li class=\"" . ($active ? "active" : "") . "\"><a data-toggle=\"tab\" href=\"#{$classname}\"><?php echo __(\"{$tableName}\"); ?></a></li>";
        $editorNavContent[] = "<div id=\"{$classname}\" class=\"tab-pane fade " . ($active ? "in active" : "") . "\" style=\"padding: 10px;\">
                            <?php include \$global['systemRootPath'] . 'plugin/{$pluginName}/View/{$classname}/index_body.php'; ?>
                        </div>";
        $active = false;

        // Initialize column-specific arrays
        $columnsVars = [];
        $columnsString = [];
        $columnsGet = [];
        $columnsSet = [];
        $columnsForm = [];
        $columnsFooter = [];
        $columnsGrid = [];
        $columnsClearJQuery = [];
        $columnsDatatable = [];
        $columnsEdit = [];
        $columnsAdd = [];
        $columnsGetAll = [];

        // Process each field in the table
        foreach ($table['fields'] as $field) {
            if ($field['name'] === 'created' || $field['name'] === 'modified') {
                continue;
            }
            // Initialize fields
            $columnsVars[] = '$' . $field['name'];
            $type = strtolower($field['type']);
            $fieldName = ucwords(str_replace("_", " ", $field['name']));

            if ($type === 'text' || $type === 'varchar') {
                $columnsString[] = "'{$field['name']}'";
            }

            // Generate field-related template replacements based on type
            if ($field['name'] != 'id') {
                $columnsAdd[] = "\$o->set" . ucfirst($field['name']) . "(\$_POST['{$field['name']}']);";
            }
            
            if ($type == 'int' || $type == 'tinyint') {
                $columnsGet[] = "function get" . ucfirst($field['name']) . "() { return intval(\$this->{$field['name']}); }";
                $columnsSet[] = "function set" . ucfirst($field['name']) . "(\${$field['name']}) { \$this->{$field['name']} = intval(\${$field['name']}); }";
            } elseif ($type == 'float') {
                $columnsGet[] = "function get" . ucfirst($field['name']) . "() { return floatval(\$this->{$field['name']}); }";
                $columnsSet[] = "function set" . ucfirst($field['name']) . "(\${$field['name']}) { \$this->{$field['name']} = floatval(\${$field['name']}); }";
            } else {
                $columnsGet[] = "function get" . ucfirst($field['name']) . "() { return \$this->{$field['name']}; }";
                $columnsSet[] = "function set" . ucfirst($field['name']) . "(\${$field['name']}) { \$this->{$field['name']} = \${$field['name']}; }";
            }

            // Example code for creating getAll functions based on field name pattern
            if (preg_match("/^(.*)_id/i", $field['name'], $matches)) {
                $columnsGetAll[] = "static function getAll" . ucfirst($matches[1]) . "() {
                    global \$global;
                    \$table = \"{$matches[1]}\";
                    \$sql = \"SELECT * FROM {\$table} WHERE 1=1 \";
                    \$res = sqlDAL::readSql(\$sql);
                    \$rows = sqlDAL::fetchAllAssoc(\$res);
                    sqlDAL::close(\$res);
                    return \$rows;
                }";
            }
        }

        // Create class and view files for each table
        $classFile = "{$pluginDirObjects}{$classname}.php";
        $modelTemplate = file_get_contents("templates/model.php");
        $data = str_replace(
            ['{classname}', '{tablename}', '{pluginName}', '{columnsVars}', '{columnsString}', '{columnsGetAll}', '{columnsGet}', '{columnsSet}'],
            [$classname, $tableName, $pluginName, implode(",", $columnsVars), implode(",", $columnsString), implode(PHP_EOL, $columnsGetAll), implode(PHP_EOL, $columnsGet), implode(PHP_EOL, $columnsSet)],
            $modelTemplate
        );
        file_put_contents($classFile, $data);
        $response['createdFiles'][] = $classFile;

        $dir = "{$pluginDirView}{$classname}/";
        _mkdir($dir);

        // index.php
        $indexFile = "{$dir}index.php";
        $indexTemplate = file_get_contents("templates/index.php");
        $indexData = str_replace(['{classname}', '{pluginName}'], [$classname, $pluginName], $indexTemplate);
        file_put_contents($indexFile, $indexData);
        $response['createdFiles'][] = $indexFile;

        // index_body.php
        $indexBodyFile = "{$dir}index_body.php";
        $indexBodyTemplate = file_get_contents("templates/index_body.php");
        $indexBodyData = str_replace(
            ['{classname}', '{tablename}', '{pluginName}', '{columnsForm}', '{columnsFooter}', '{columnsGrid}', '{$columnsClearJQuery}', '{columnsDatatable}', '{$columnsEdit}'],
            [$classname, $tableName, $pluginName, implode(PHP_EOL, $columnsForm), implode(PHP_EOL, $columnsFooter), implode(PHP_EOL, $columnsGrid), implode(PHP_EOL, $columnsClearJQuery), implode("," . PHP_EOL, $columnsDatatable), implode(PHP_EOL, $columnsEdit)],
            $indexBodyTemplate
        );
        file_put_contents($indexBodyFile, $indexBodyData);
        $response['createdFiles'][] = $indexBodyFile;

        // list.json.php
        $listFile = "{$dir}list.json.php";
        $listTemplate = file_get_contents("templates/list.json.php");
        $listData = str_replace(['{classname}', '{pluginName}'], [$classname, $pluginName], $listTemplate);
        file_put_contents($listFile, $listData);
        $response['createdFiles'][] = $listFile;

        // delete.json.php
        $deleteFile = "{$dir}delete.json.php";
        $deleteTemplate = file_get_contents("templates/delete.json.php");
        $deleteData = str_replace(['{classname}', '{pluginName}'], [$classname, $pluginName], $deleteTemplate);
        file_put_contents($deleteFile, $deleteData);
        $response['createdFiles'][] = $deleteFile;

        // add.json.php
        $addFile = "{$dir}add.json.php";
        $addTemplate = file_get_contents("templates/add.json.php");
        $addData = str_replace(['{classname}', '{pluginName}', '{columnsAdd}'], [$classname, $pluginName, implode(PHP_EOL, $columnsAdd)], $addTemplate);
        file_put_contents($addFile, $addData);
        $response['createdFiles'][] = $addFile;
    }

    // Finalize main plugin and editor files
    $pluginFile = "{$pluginDir}{$pluginName}.php";
    $pluginTemplate = file_get_contents("templates/plugin.php");
    $pluginData = str_replace(
        ['{pluginName}', '{includeTables}', '{tablename}', '{uid}'],
        [$pluginName, implode(PHP_EOL, $includeTables), $pluginName, uniqid()],
        $pluginTemplate
    );
    file_put_contents($pluginFile, $pluginData);
    $response['createdFiles'][] = $pluginFile;

    $editorFile = "{$pluginDir}View/editor.php";
    $editorTemplate = file_get_contents("templates/editor.php");
    $editorData = str_replace(
        ['{pluginName}', '{editorNavTabs}', '{editorNavContent}'],
        [$pluginName, implode(PHP_EOL, $editorNavTabs), implode(PHP_EOL, $editorNavContent)],
        $editorTemplate
    );
    file_put_contents($editorFile, $editorData);
    $response['createdFiles'][] = $editorFile;

    // Populate response with plugin details
    $response['pluginName'] = $pluginName;
    $response['pluginDir'] = $pluginDir;
    $response['msg'] = "Plugin '{$pluginName}' created successfully.";

    $response['zipDirectory'] = zipDirectory( __DIR__."/{$pluginDir}", __DIR__."/{$pluginZip}");
    
    $response['zipDownload'] = "{$global['webSiteRootURL']}CreatePlugin/{$pluginZip}";

    rrmdir(__DIR__."/{$pluginDir}");

} catch (Exception $e) {
    $response['error'] = true;
    $response['msg'] = $e->getMessage();
}

echo json_encode($response);

// Helper function to create directories if they don't exist
function _mkdir($dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}
