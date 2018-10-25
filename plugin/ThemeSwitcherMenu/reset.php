<?php
require_once '../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/ThemeSwitcherMenu/ThemeSwitcherMenu.php';
ThemeSwitcherMenu::reset();
header("Location: ".(!empty($_SERVER["HTTP_REFERER"])? strtok($_SERVER["HTTP_REFERER"],'?'):$global['webSiteRootURL']));