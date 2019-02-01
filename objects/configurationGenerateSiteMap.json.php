<?php

function siteMap() {
    global $global;
    $date = date('Y-m-d\TH:i:s') . "+00:00";
    
    $xml = '<?xml version="1.0" encoding="UTF-8"?>
    <urlset
        xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
        <!-- Main Page -->
        <url>
            <loc>'.$global['webSiteRootURL'].'</loc>
            <lastmod>'.$date.'</lastmod>
            <changefreq>always</changefreq>
            <priority>1.00</priority>
        </url>

        <url>
            <loc>'.$global['webSiteRootURL'].'help</loc>
            <lastmod>'.$date.'</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.50</priority>
        </url>
        <url>
            <loc>'.$global['webSiteRootURL'].'about</loc>
            <lastmod>'.$date.'</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.50</priority>
        </url>
        <url>
            <loc>'.$global['webSiteRootURL'].'contact</loc>
            <lastmod>'.$date.'</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.50</priority>
        </url>

        <!-- Channels -->
        <url>
            <loc>'.$global['webSiteRootURL'].'channels</loc>
            <lastmod>'.$date.'</lastmod>
            <changefreq>daily</changefreq>
            <priority>0.80</priority>
        </url>
        ';
        $users = User::getAllUsers(true);
        foreach ($users as $value) {
            $xml .= '        
            <url>
                <loc>'.User::getChannelLink($value['id']).'</loc>
                <lastmod>'.$date.'</lastmod>
                <changefreq>daily</changefreq>
                <priority>0.70</priority>
            </url>
            ';
        }
        $xml .= ' 
        <!-- Categories -->
        ';
        $rows = Category::getAllCategories();
        foreach ($rows as $value) {
            $xml .= '  
            <url>
                <loc>'.$global['webSiteRootURL'].'cat/'.$value['clean_name'].'</loc>
                <lastmod>'.$date.'</lastmod>
                <changefreq>weekly</changefreq>
                <priority>0.80</priority>
            </url>
            ';
        }
        $xml .= '<!-- Videos -->';
        $rows = Video::getAllVideos("viewable");
        foreach ($rows as $value) {
            $xml .= '   
            <url>
                <loc>'.Video::getLink($value['id'], $value['clean_title']).'</loc>
                <lastmod>'.$date.'</lastmod>
                <changefreq>monthly</changefreq>
                <priority>0.80</priority>
            </url>
            ';
        }
        $xml .= '</urlset> ';
        return $xml;
}

//header("Content-type: text/xml");
header('Content-Type: application/json');
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
require_once $global['systemRootPath'] . 'objects/user.php';
require_once $global['systemRootPath'] . 'objects/category.php';

$obj = new stdClass();
$obj->error = true;
$obj->msg = "";

if (!User::isAdmin()) {
    $obj->msg = __("Permission denied");
    die(json_encode($obj));
}
$sitemap = siteMap();

if(!file_put_contents($sitemapFile, $sitemap)){
    $obj->msg = "We could not save the sitemap";
    die(json_encode($obj));
}

$obj->error = false;
die(json_encode($obj));
