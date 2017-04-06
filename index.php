<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Error Page</title>
        <link href="view/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"/>
        <style>
            html{
            }
            body{
                margin: 0;
                padding: 0;
                background: #e7ecf0;
                font-family: Arial, Helvetica, sans-serif;
            }
            *{
                margin: 0;
                padding: 0;
            }
            p{
                font-size: 12px;
                color: #373737;
                font-family: Arial, Helvetica, sans-serif;
                line-height: 18px;
            }
            p a{
                color: #218bdc;
                font-size: 12px;
                text-decoration: none;
            }
            a{
                outline: none;
            }
            .f-left{
                float:left;
            }
            .f-right{
                float:right;
            }
            .clear{
                clear: both;
                overflow: hidden;
            }
            #block_error{
                width: 845px;
                height: 500px;
                border: 1px solid #cccccc;
                margin: 72px auto 0;
                -moz-border-radius: 4px;
                -webkit-border-radius: 4px;
                border-radius: 4px;
                background: #fff url(http://www.ebpaidrev.com/systemerror/block.gif) no-repeat 0 51px;
            }
            #block_error div{
                padding: 100px 40px 0 186px;
            }
            #block_error div h2{
                color: #218bdc;
                font-size: 24px;
                display: block;
                padding: 0 0 14px 0;
                border-bottom: 1px solid #cccccc;
                margin-bottom: 12px;
                font-weight: normal;
            }
        </style>
    </head>
    <body marginwidth="0" marginheight="0">
        <div id="block_error">
            <div>
                <h2>Error. &nbspOops you've have encountered an error</h2>
                <p>
                    It apperrs that Either something went wrong or the mod rewrite configration is not correct.<br />
                </p>
                <p>In order to use mod_rewrite you can type the following command in the terminal: </p>
                <p><code>a2enmod rewrite</code></p>

                <p>Restart apache2 after </p>

                <p><code>/etc/init.d/apache2 restart</code></p>

                <p>or </p>

                <p><code>service apache2 restart</code></p>

                <p> If it does not work check the file permition for htaccess</p>
                
                <p><code>sudo chown -R www-data:www-data <?php echo getcwd();?></code></p>
            </div>
        </div>
    </body>
</html>
