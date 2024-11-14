<?php
	include('src/SQLParser.php');

	use iamcal\SQLParser;


	$sql = file_get_contents('data/glitch_main.sql');

	$obj = new SQLParser();

if (0){
	$s = microtime(true);
	$tokens = $obj->lex($sql);
	$e = microtime(true);

	print_r($tokens);

	$ms = round(1000 * ($e - $s));
	echo "Lexing took $ms ms\n";
}

if (0){
	$s = microtime(true);
	$tokens = $obj->lex($sql);
	$e = microtime(true);
	$tokens = $obj->collapse_tokens($tokens);
	$e2 = microtime(true);

	print_r($tokens);

	$ms1 = round(1000 * ($e - $s));
	$ms2 = round(1000 * ($e2 - $e));
	echo "Lexing took $ms1 ms\n";
	echo "Collapsing took $ms2 ms\n";
}

if (1){

	$obj->find_single_table = true;

	$s = microtime(true);
	$obj->parse($sql);
	$e = microtime(true);

	print_r($obj->tables);

	$ms = round(1000 * ($e - $s));
	echo "Parse took $ms ms\n";
}
