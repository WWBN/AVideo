<?php
require_once '../../videos/configuration.php';

?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title>Live - <?php echo $config->getWebSiteTitle(); ?></title>
        <meta name="generator" content="YouPHPTube - A Free Youtube Clone Script" />
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>
    </head>
    <body>
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>
        <div class="container">
            <div class="info info-warning">
                <div class="post-text" itemprop="text">
                    <p>LiveChat uses a SSL connection</p>
                    <p>If you are using Apache web server (2.4 or above), enable these modules in httpd.conf file :</p>

                    <ol>
                        <li><a href="http://httpd.apache.org/docs/2.2/mod/mod_proxy.html">mod_proxy.so</a></li>
                        <li><a href="http://httpd.apache.org/docs/2.4/mod/mod_proxy_wstunnel.html">mod_proxy_wstunnel.so</a></li>
                    </ol>

                    <p>Add this setting to your <b>httpd.conf</b> file</p>

                    <pre><code>ProxyPass /wss/ ws://127.0.0.1:8888/</code></pre>

                    <p>Restart Apache web server and make sure that your Ratchet worker (web socket connection) is open before applying the settings (telnet hostname port).</p>
                </div>
            </div>
        </div>
        <?php
        include $global['systemRootPath'] . 'view/include/footer.php';
        ?>
    </body>
</html>
