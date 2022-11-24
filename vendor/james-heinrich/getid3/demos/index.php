<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head><title>getID3 demos</title><style type="text/css">BODY, TD, TH { font-family: sans-serif; font-size: 10pt; }</style></head><body>

<?php
die('For security reasons, this demo has been disabled. It can be enabled by removing line '.__LINE__.' in demos/'.basename(__FILE__));
?>

In this directory are a number of examples of how to use <a href="https://www.getid3.org/">getID3()</a>.<br>
If you don't know what to run, take a look at <a href="demo.browse.php"><b>demo.browse.php</b></a>
<hr>
Other demos:<ul>
<?php
if ($dh = @opendir('.')) {
	while ($file = @readdir($dh)) {
		if (preg_match('#^demo\\..+\\.php$#', $file)) {
			echo '<li><a href="'.htmlentities($file).'">'.htmlentities($file).'</a></li>';
		}
	}
}
?>
</ul>
</body>
</html>
