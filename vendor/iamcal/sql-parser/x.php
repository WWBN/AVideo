<?php
	ini_set('display_errors', 1);
	error_reporting(E_ERROR | E_WARNING | E_PARSE);

	include('src/SQLParser.php');

	use iamcal\SQLParser;

$sql = <<<EOD
CREATE TABLE `mentions` (
  `team_id` bigint(20) unsigned NOT NULL,
  `type` enum('at','word','dm','everyone','channel','everything') NOT NULL,
  `is_bot` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`team_id`),
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

EOD;

	$obj = new SQLParser();

	#$tokens = $obj->lex($sql);
	#print_r($tokens);
	#exit;

	$obj->parse($sql);
	#echo json_encode($obj->tables['bounces']['fields'], JSON_PRETTY_PRINT)."\n";
	echo json_encode($obj->tables, JSON_PRETTY_PRINT)."\n";

