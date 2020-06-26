<?php
require_once '../../../videos/configuration.php';
require_once $global['systemRootPath'] . 'objects/user.php';
if (!User::isAdmin()) {
    header("Location: {$global['webSiteRootURL']}?error=" . __("You can not manager plugin add logo"));
    exit;
}
$obj = AVideoPlugin::getObjectDataIfEnabled("WWBN");
if(empty($obj)){
    die("Plugin disabled");
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['language']; ?>">
    <head>
        <title><?php echo $config->getWebSiteTitle(); ?>  :: WWBN</title>
        <?php
        include $global['systemRootPath'] . 'view/include/head.php';
        ?>

    </head>
    <body class="<?php echo $global['bodyClass']; ?>">
        <?php
        include $global['systemRootPath'] . 'view/include/navbar.php';
        ?>

        <div class="container">
        	<br><br>
        	<div class="row" style="background: #fff;">
        		<div class="col-md-12">

		        	<h1>INSTRUCTION : </h1>

			        <ol>
			        	<li>
			        		<b>Sign Up</b><br><br>
			        		<ul>
			        			<li>
			        				Enter your email, then click next. <br>
			        				<img src="../assets/images/signup.PNG"><br>
			        			</li>
			        			<li>
			        				Fill-up the following information <br>
			        				<img src="../assets/images/signup_info.PNG"><br><br>
			        			</li>
			        			<li>
			        				A success message will display after signup completion <br>
			        				<img src="../assets/images/success_message.PNG"><br><br>
			        			</li>
			        			<li>
			        				When registration approved by the admin, you will receive an email like this; <br>
			        				<img src="../assets/images/activated.PNG"><br><br>
			        			</li>
			        		</ul>
			        	</li>
			        	<li>
			        		<b>Encountering Error</b> <br><br>
			        		<ul>
			        			<li>
			        				Email is already registered. Possible reason is you have unsubscribe your account or your account isn't approve yet by the admin, just click <b>click here</b> (#2) to request/notify the admin about your registration again. <br>
			        				<img src="../assets/images/email_already_registered.PNG"><br><br>
			        			</li>
			        		</ul>
			        	</li>
			        	<li>
			        		<b> Submitting index of your platfor</b> <br><br>
			        		<ul>
			        			<li>
			        				
			        			</li>
			        		</ul>
			        	</li>
			        </ol>
		        </div>
	        </div>
        </div>
        

    </body>

</html>