#!/usr/bin/env php

<?php

	/* 
 	 * ===============================
	 *
	 * sorter.php
	 *
	 * Sort Laravel's lang file array
	 * (.php or .json)
	 *
	 * author: Marko Pantelic 
	 *         marko_pantelic@mail.com
	 * ===============================
	 */

	// TODO: catch comments from input file (/**/ and //)
	//       Check in laravel docs if lang/json can be multilevel.      
	// 	 receive 'argopt' style input arguments (-i   - inplace file sort
	// 		                  		 --p5 - php 5 syntax array)

	/*
		USAGE:
			sorter.php FILE_TO_SORT [OUTPUT_MODE]
	 */


	define("PROGNAME", "Laravel lang sort");
	define("INDENT_STR", "\t");
	define("PHP5_SYNTAX_ARRAY", 0); // not used (will be when argopt is completed)!
	define("PHP7_SYNTAX_ARRAY", 1); // not used (will be when argopt is completed)!	
	define("OUTPUT_STDOUT", 2);
	define("OUTPUT_FILE_ORIGIN", 3);


	// choose output syntax array
	$OUTPUT_SYNTAX_ARRAY = PHP7_SYNTAX_ARRAY;

	// choose output format
	$OUTPUT = OUTPUT_STDOUT;


	/*
	 * Include array from file and return it
	 */ 
	function arr_from_file($file_name)
	{
		return require $file_name;
	}	


	/**
	 * Sort array recursively by it's keys
	 * Save max key width for every array level and store results in $levels_max_key_width
	 *
	 * @param $arr                  array   | array to be sorted
	 * @param $arr_level            integer | level of current recursive step
	 * @param $levels_max_key_width array   | key = level , value = max key width 
	 */ 
	function recursive_key_sort(&$arr, &$levels_max_key_width, $arr_level=-1)
	{
		ksort($arr, SORT_NATURAL | SORT_FLAG_CASE);
		$level = $arr_level + 1;

		// set key to avoid 'undefined offset(key)' error
		if(! isset($levels_max_key_width[$level])) {
			$levels_max_key_width[$level] = 0;
		}

		foreach($arr as $key => $el) {

			// store max key width
			$kw = strlen($key) + 2;
			if ($kw > $levels_max_key_width[$level]) {
				$levels_max_key_width[$level] = $kw;
			}

			if(is_array($el)) {
				$arr[$key] = recursive_key_sort($el, $levels_max_key_width, $level);
			}
			else {
				$arr[$key] = trim($el);
			}
		}
		return $arr;
	}


	/* 
	 * Recursively construct cumulative string of multidimensional array
	 *
	 * @param array $arr                  | array to sort and output as a string
	 * @param array $levels_max_key_width | array of keys max width for each level
	 * @param int $arr_level              | current recursive array level
	 *
	 * @return string
	 */	
	function arr_str_construct($arr, $levels_max_key_width, $arr_level=0)
	{
		$concat = '';

		foreach($arr as $key => $val) {

			if(is_array($val)) {
				 
				$concat .= ms(INDENT_STR, $arr_level+1);
				$concat .= sprintf("%-". $levels_max_key_width[$arr_level+1] . "s", "'" . $key . "' => [" . PHP_EOL);

				$concat .= arr_str_construct($val, $levels_max_key_width, $arr_level+1);
				$concat .= ms(INDENT_STR, $arr_level+1) . "]," . PHP_EOL;
			}
			else {
				$concat .= ms(INDENT_STR, $arr_level+1);
				$concat .= sprintf("%-". $levels_max_key_width[$arr_level] . "s", "'$key'");
				$concat .= " => '" . $val . "'," . PHP_EOL;
			}

		}

		return $concat;
	}


	/*
	 * Multiply string n number of times
	 */ 
	function ms($str, $n)
	{
		$concat = '';
		for($i=0; $i<$n; ++$i) {
			$concat .= $str;
		}
		return $concat;
	}


	/* 
	 * Show question and recevie response form user in CLI 
	 *
	 * @param string $question
	 * @param array $expected
	 *
	 * @return string
	 */
	function prompt($question, $expected)
	{
		// for readline(), libreadline is required
		/*
		while (! in_array($line = readline($question))) {
			//readline_add_history($line); // readline library not available in Windows
		}
		 */
		$line = '#@$!78-?~'; // most unprobable string

		while( ! in_array($line, $expected) ) {
			echo $question;
			$line = rtrim( fgets( STDIN ), "\n" );
		}

		return $line;
	}




	// ------ //
	// main() //
	// ------ //
	
	$result = '';


	// check input arguments
	// ---------------------
	if ($argc < 2) {
		echo PROGNAME . ": Invalid number of arguments" . PHP_EOL;
		echo "USAGE: " . PROGNAME . " FILENAME [OUTPUT_MODE]" . PHP_EOL;
		echo "output modes: - stdout (default)" . PHP_EOL;
		echo "              - overwrite (overwrite supplied file with result)" . PHP_EOL;

		exit(1);
	}

	if ($argc >= 3) {

		switch($argv[2]) {
			case "stdout":
				$OUTPUT = OUTPUT_STDOUT; 
				break;
			case "overwrite":
				$OUTPUT = OUTPUT_FILE_ORIGIN;
				break;
			default:
				echo PROGNAME . ": invalid output mode specified. exiting..." . PHP_EOL;
				exit(1);
		}
	}

	$file_name = $argv[1];

	// TODO: check file write and read permissions
	if( ! file_exists($file_name) ) {
		echo PROGNAME . ": Invalid file path" . PHP_EOL;
		exit(1);
	}
	if( ! is_readable($file_name) ) {	
		echo PROGNAME . ": Invalid file permissions. File is not readable." . PHP_EOL;
		exit(1);
	}

	// get supplied filename extension
	// -------------------------------
	$filename_parts = explode('.', $file_name);
	$file_extension = end($filename_parts);


	// preform sort and string operations
	// ----------------------------------
	if ($file_extension == 'php') {
		// store in this array max key width, where array key = level and value = max width
		$levels_max_key_width = [];

		$arr_to_sort = arr_from_file($file_name); 
		recursive_key_sort($arr_to_sort, $levels_max_key_width);

		// print to output
		$result .= "<?php" . PHP_EOL . PHP_EOL;
		$result .= "return [" . PHP_EOL;

		$result .= arr_str_construct($arr_to_sort, $levels_max_key_width);

		$result .= "];" . PHP_EOL;
	}
	elseif ($file_extension == 'json') {
		// TODO: check if input file is in utf8 (json_decode demands utf8 as input)
		//       check if JSON syntax is valid. See https://secure.php.net/manual/en/json.constants.php
		
		try {
			$json = file_get_contents($file_name);
		}
		catch(Exception $e) {
			echo PROGNAME . ": Reading file error -> " . $e->message;
			exit(1);
		}

		$arr_to_sort = json_decode($json, TRUE); // TRUE - convert to associative array
		ksort($arr_to_sort, SORT_NATURAL | SORT_FLAG_CASE);

		$result = json_encode($arr_to_sort, JSON_PRETTY_PRINT);
	}
	else {
		echo PROGNAME . ": Invalid file extension detected. Expecting 'php' or 'json'. Exiting..." . PHP_EOL;
		exit(1);
	}

	// output result
	// -------------
	if ($OUTPUT == OUTPUT_STDOUT) {
		echo $result;
	}
	elseif ($OUTPUT == OUTPUT_FILE_ORIGIN) {

		if( ! is_writable($file_name) ) {	
			echo PROGNAME . ": Invalid file permissions. File is not writable." . PHP_EOL;
			exit(1);
		}

		$answer = prompt("NOTE: All lang file comments will be lost. Continue (yes/no)", ["yes", "no"]);

		if($answer == "no") {
			echo PROGNAME. ": Exiting..." . PHP_EOL;
			exit(0);
		}

		try {
			file_put_contents($file_name, $result);
		}
		catch(Exception $e) {	
			echo PROGNAME . ": Writing file error -> " . $e->message;
			exit(1);
		}

		echo PROGNAME . ": sorting done. Original file updated (overwritten)" . PHP_EOL;
	}
	else {
		echo PROGNAME . ": PROGRAMMER ERROR: invalid 'OUTPUT' option !" . PHP_EOL;
		exit(1);
	}


	/*
	// FIRST SOLUTION
	$sorted_str = var_export($arr_to_sort, TRUE);
	$tl = 0; // tab level

	foreach(explode(PHP_EOL, $sorted_str) as $orig_line) {

		$line = trim($orig_line);
		$tmp = 0;
		$bonus_tl = '';

		if($line == "array (") {
			$line = "[";
			++$tmp;
			$bonus_tl = INDENT_STR;
		}
		elseif($line == "),") {
			$line = "],";
			--$tmp;
		}
		elseif($line == ");" || $line == ")") {
			--$tl;
			$line = "];";
		}
		echo ms(INDENT_STR, $tl) . $bonus_tl . $line . PHP_EOL;

		$tl += $tmp;
	}
	 */
	 
	exit(0);


?>
