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
			        			<li>
			        				Signin to view the indexing module/page <br>
			        				<img src="../assets/images/signin.PNG"><br><br>
			        			</li>
			        		</ul>
			        	</li>
			        	<li>
			        		<b>Encountering Error</b> <br><br>
			        		<ul>
			        			<li>
			        				Email is already registered. Possible reason is your account isn't approve yet by the admin, just click the <b>click here</b> link (#2) to request/notify the admin about your registration again if your registration is taking more days. <br>
			        				<img src="../assets/images/email_already_registered.PNG"><br><br>
			        			</li>
			        		</ul>
			        	</li>
			        	<li>
			        		<b> Submitting index of your platform</b> <br><br>
			        		<ul>
			        			<li>
			        				Click the menu <b>Network Index</b> in the sidebar, then click the <b>plus sign [+] button</b> on the right side of <b>MY INDEX LIST</b>.<br>
			        				<img src="../assets/images/network_index_module.PNG" width="100%"><br><br>
			        			</li>
			        			<li>
			        				Change the two fields depends on what you like, only the Content Type and Country Restriction will be editable and the other four fields are auto generated.
			        				<img src="../assets/images/submit_index.PNG"><br><br>
			        			</li>
			        		</ul>
			        	</li>
			        	<li>
			        		<b> Index data</b> <br><br>
			        		<ul>
			        			<li>
			        				This will be look like when successfully submitted an index; <br>
			        				<b>1</b> - Edit button, to edit the content type and restriction fields <br>
			        				<b>2</b> - Link button, this will be the redirect link to your platform (URL) <br>
			        				<b>3</b> - Deactivate/Re-activate button, deactivating will remove your index to all affilates connected with and the re-activate will send an notification for activation similar when submitting new index<br>
			        				<b>4</b> - Active/Deactivated, Active means your index can be assigned to any affiliates and Deactivated means your index is not available to be index. <br>
			        				<img src="../assets/images/index_info.PNG" width="100%"><br><br>
			        			</li>
			        			<!-- <li>
			        				Updating data <br>
			        				<img src="../assets/images/index_info.PNG" width="100%"><br><br>
			        			</li> -->
			        		</ul>
			        	</li>
			        </ol>
		        </div>
	        </div>
        </div>
        

    </body>

</html>