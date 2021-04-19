<?php
if(empty($mysqlHost)){
    die();
}
error_log("ERROR: your site is offline we could not connect into MySQL");
error_log("ERROR: used credentials: mysqlHost = $mysqlHost; mysqlUser = $mysqlUser; mysqlPass = $mysqlPass; mysqlDatabase = $mysqlDatabase;");
?>
<!doctype html>
<title><? echo __("Site Maintenance"); ?></title>
<style>
  body { text-align: center; padding: 150px; }
  h1 { font-size: 50px; }
  body { font: 20px Helvetica, sans-serif; color: #333; }
  article { display: block; text-align: left; width: 650px; margin: 0 auto; }
  a { color: #dc8100; text-decoration: none; }
  a:hover { color: #333; text-decoration: none; }
</style>

<article>
    <center>
        <img src="videos/userPhoto/logo.png"/>
    </center>
    <h1><? echo __("We&rsquo;ll be back soon!"); ?></h1>
    <div>
        <p><? echo __("Sorry for the inconvenience but we&rsquo;re performing some maintenance at the moment."); ?></p>
        <p><? echo __("&mdash; The Team"); ?></p>
    </div>
</article>
