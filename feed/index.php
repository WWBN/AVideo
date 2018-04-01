<?php
header("Content-Type: application/rss+xml; charset=UTF8");


require_once '../videos/configuration.php';

 $db = mysqli_connect($mysqlHost,$mysqlUser,$mysqlPass,$mysqlDatabase);

 

       $query = $db->query("SELECT * FROM `videos` WHERE 1 ORDER BY `videos`.`created` DESC LIMIT 10");  
    
      if ($db->affected_rows >= 1)  {    
echo'<?xml version="1.0" encoding="UTF-8"?>'?>

<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/">
	
<channel>
    <title>RSS YouPHPTube</title>
    <description>Rss Feed</description>
    <link><?php echo $global['webSiteRootURL'] ;?></link>
    <sy:updatePeriod>hourly</sy:updatePeriod>
    <sy:updateFrequency>1</sy:updateFrequency>
 
 <image>
		<title>RSS Feed</title>
		<url><?php echo $global['webSiteRootURL'] ;?>/videos/userPhoto/logo.png</url>
		<link><?php echo $global['webSiteRootURL'] ;?></link>
		 <width>144</width>
		<height>40</height>
		<description>YouPHPTube versione rss</description>
	</image>
 
 <?php
      while ($row = $query->fetch_assoc()) { 
   ?>
    

       <item>
       <title><?php echo $row['title']; ?></title>
       <description><?php echo $row['description']; ?></description>
       <link> <?php
        echo $global['webSiteRootURL'] ;?>/video/<?php echo $row['clean_title']; ?></link>
       <pubDate><?php echo date('r', strtotime($row['created'])); ?></pubDate>
       </item>
     
     <?php
	   }   
    ?>
</channel>
</rss>

<?php
} 
?>
