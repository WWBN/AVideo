<?php
require_once '../../videos/configuration.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title><?php echo $config->getWebSiteTitle(); ?></title>
        <style type="text/css">

        </style>
    </head>
    <body leftmargin="10" rightmargin="10" marginwidth="10" topmargin="10" bottommargin="10" marginheight="10" offset="0" bgcolor="#f0f1f4">
        <table width="80%"  cellpadding="10" cellspacing="0" border="0" align="center" bgcolor="#FFF" style="margin:0 auto;">
            <tr>
                <td align="center"><img src="<?php echo $global['webSiteRootURL'], $config->getLogo(); ?>" alt="<?php echo $config->getWebSiteTitle(); ?>"/></td>
            </tr>
            <tr>
                <td>{message}</td>
            </tr>
            <tr>
                <td></td>
            </tr>
        </table>
    </body>
</html>