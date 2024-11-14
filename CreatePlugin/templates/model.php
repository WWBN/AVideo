<?php

require_once dirname(__FILE__) . '/../../../videos/configuration.php';

class {classname} extends ObjectYPT {

    protected {columnsVars};
    
    static function getSearchFieldsNames() {
        return array({columnsString});
    }

    static function getTableName() {
        return '{tablename}';
    }
    
    {columnsGetAll}
    
    {columnsSet}
    
    {columnsGet}

        
}
