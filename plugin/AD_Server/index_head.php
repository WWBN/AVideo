<?php
$plugin = YouPHPTubePlugin::loadPluginIfEnabled('AD_Server');
$ad_server_location = YouPHPTubePlugin::loadPluginIfEnabled('AD_Server_Location');
?>
<link rel="stylesheet" type="text/css" href="<?php echo $global['webSiteRootURL']; ?>view/css/DataTables/datatables.min.css"/>
<link href="<?php echo $global['webSiteRootURL']; ?>view/js/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $global['webSiteRootURL']; ?>view/js/jquery-ui/jquery-ui.min.css" rel="stylesheet" type="text/css"/>

<style>
    #campaignVideosTable td img {
        height: 50px;
        margin: 5px;
    }
    .ui-autocomplete{
        z-index: 9999999;
    }
</style>