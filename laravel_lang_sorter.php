#!/usr/bin/env php
<?php

	/* 
 	 * ======================================================================= *
	 *                                                                         *
	 * laravel_lang_sorter.php                                                 *
	 *                                                                         *
	 * Sort Laravel's lang file array (.php or .json) and beautify it's        * 
	 * contents.                                                               *
	 *                                                                         *
	 * author: Marko Pantelic                                                  *
	 *         marko_pantelic@mail.com                                         *
	 * ======================================================================= *
	 */

	// NOTE: This script does not support PHP comments yet!!!!
	// NOTE: This script outputs only PHP7 style arrays !

	// TODO: catch comments from input file (/**/ and //)
	//       Check in laravel docs if lang/json can be multilevel.      


	define("PROGNAME", "laravel_lang_sorter");
	define("INDENT_STR", "\t");
	//define("PHP5_SYNTAX_ARRAY", 0); // TODO: not used
	//define("PHP7_SYNTAX_ARRAY", 1); // TODO: not used


	/*
	 * Include array from file and return it
	 */ 
	function arrFromFile($file_name)
	{
		return require $file_name;
	}	


	/**
	 * Sort array recursively by it's keys
	 * Save max key width for every array level and store results in 
	 * $levelsMaxKeyWidth variable.
	 *
	 * @param array $arr                | array to be sorted
	 * @param integer $arrLevel         | level of current recursive step
	 * @param array $levelsMaxKeyWidth  | key = level , value = max key width 
	 */ 
	function recursiveKeySort(&$arr, &$levelsMaxKeyWidth, $arrLevel=-1)
	{
		ksort($arr, SORT_NATURAL | SORT_FLAG_CASE);
		$level = $arrLevel + 1;

		// set key to avoid 'undefined offset(key)' error
		if(! isset($levelsMaxKeyWidth[$level])) {
			$levelsMaxKeyWidth[$level] = 0;
		}

		foreach($arr as $key => $el) {

			// store max key width
			$kw = strlen($key) + 2;
			if ($kw > $levelsMaxKeyWidth[$level]) {
				$levelsMaxKeyWidth[$level] = $kw;
			}

			if(is_array($el)) {
				$arr[$key] = recursiveKeySort($el, $levelsMaxKeyWidth, $level);
			}
			else {
				$arr[$key] = trim($el);
			}
		}
		return $arr;
	}


	/**
	 * Return quote char. If contents of the $val string contain ' then
	 * return " and converse. 
	 * 
	 * @param string $val
	 * 
	 * @return string
	 */
	function getQuoteChar($val)
	{
		$q = "'"; // default quote char

		if(strpos($val, '"') === FALSE) {
			$q = '"';
		}
		if(strpos($val, "'") === FALSE) {
			$q = "'";
		}

		return $q;
	}


	/**
	 * Recursively construct cumulative string of multidimensional array
	 *
	 * @param array $arr                  | array to sort and output as a string
	 * @param array $levelsMaxKeyWidth    | array of keys max width for each level
	 * @param int   $arrLevel             | current recursive array level
	 *
	 * @return string
	 */	
	function arrStrConstruct($arr, $levelsMaxKeyWidth, $arrLevel=0)
	{
		$concat = '';

		foreach($arr as $key => $val) {

			if(is_array($val)) {
				 
				$concat .= ms(INDENT_STR, $arrLevel+1);
				$concat .= sprintf("%-". $levelsMaxKeyWidth[$arrLevel+1] 
				         . "s", "'" . $key . "' => [" . PHP_EOL);

				$concat .= arrStrConstruct($val, $levelsMaxKeyWidth, $arrLevel+1);
				$concat .= ms(INDENT_STR, $arrLevel+1) . "]," . PHP_EOL;
			}
			else {
				$concat .= ms(INDENT_STR, $arrLevel+1);
				$concat .= sprintf("%-". $levelsMaxKeyWidth[$arrLevel] . "s", "'$key'");
				$q = getQuoteChar($val);
				$concat .= " => " . $q . $val . $q . "," . PHP_EOL;
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


	/** 
	 * Show CLI question and receive response form the user.
	 *
	 * @param string $question
	 * @param array  $expected
	 *
	 * @return string
	 */
	function prompt($question, $expected)
	{
		/*
		// for readline(), installed libreadline is required
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


	/**
	 * Print help to the stdout.
	 * 
	 * @return void
	 */
	function printHelp($optionsExplanation)
	{
		echo "Usage: "  . PROGNAME . " [OPTION]... [FILE]... \n";
		echo "Sort and beautify contents of Laravel lang files\n\n";

		foreach($optionsExplanation	as $line) {
			echo $line . "\n";
		}
	}


	/**
	 * Check if all option arguments exists.
	 * getopt() function does not have this feature, therefore this metod was
	 * created.
	 * 
	 * @param array  $allInput
	 * @param string $shortOptions
	 * @param array  $longOptions
	 * 
	 * @return string|TRUE
	 */
	function checkOptionArgumentsExists($shortOptions, $longOptions)
	{
		global $argv;
		$shortOptionsArray = str_split($shortOptions);

		foreach($argv as $arg) {

			if(strlen($arg) == 2 && substr($arg, 0, 1) == '-') { // shortoption

				if(!in_array(ltrim($arg, '-'), $shortOptionsArray)) {
					return "Unknown short option '$arg'";
				}
			}
			elseif(strlen($arg) > 4 && substr($arg, 0, 2) == '--') { // long option

				if(!in_array(ltrim($arg, '--'), $longOptions)) {
					return "Unknown long option '$arg'";
				}
			}
		}

		return TRUE;
	}


	/**
	 * Return non option arguments from $argv array
	 * 
	 * @return array
	 */
	function getNonOptionArguments()
	{
		global $argv;
		global $argc;

		$argn = 0;
		while (++$argn < $argc && preg_match('/^-/', $argv[$argn])); # (no loop body)

		$arguments = array_slice($argv, $argn);

		return $arguments;
	}


	/**
	 * Exit execution PHP lang file is invalid.
	 */
	function checkPhpLangFile($filepath)
	{
		// check if PHP file has comments
		$contents = file_get_contents($filepath);
		$hasPhpComments = preg_match('/(\/\*)|(\/\/)/m', $contents); // just look for beginning /* or //

		if($hasPhpComments) {
			fwrite(STDERR, PROGNAME . ": PHP file has comments. This is currently not supported.\n");
			return FALSE;
		}

		return TRUE;
	}


	/**
	 * Sort PHP array and beautify it. 
	 * 
	 * @param string $filepath
	 * @param array  $execSetup
	 * 
	 * @return string|FALSE
	 */
	function performPhpLangFileSortAndBeautify($filepath, $execSetup)
	{
		if(!isset($execSetup['force'])) {
			if(!checkPhpLangFile($filepath)) { // this call is temporary until php comments parsing feature is created.
				return FALSE;
			}
		}

		$result = '';
		
		// store in this array max key width, where array key = level and value = max width
		$levelsMaxKeyWidth = [];

		$arrToSort = arrFromFile($filepath); 
		recursiveKeySort($arrToSort, $levelsMaxKeyWidth);

		$result .= "<?php" . PHP_EOL . PHP_EOL;
		$result .= "return [" . PHP_EOL;
		$result .= arrStrConstruct($arrToSort, $levelsMaxKeyWidth);
		$result .= "];" . PHP_EOL;

		return $result;
	}


	/**
	 * Sort JSON object by keys. 
	 * 
	 * @param string $filepath
	 * @param array  $execSetup
	 * 
	 * @return string
	 */
	function performJsonLangFileSortAndBeautify($filepath, $execSetup)
	{
		// TODO: check if input file is in utf8 (json_decode demands utf8 as input)
		//       check if JSON syntax is valid. See https://secure.php.net/manual/en/json.constants.php
		
		try {
			$json = file_get_contents($filepath);
		}
		catch(Exception $e) {
			fwrite(STDERR, PROGNAME . ": Reading file error -> " . $e->message);
			exit(1);
		}

		$arrToSort = json_decode($json, TRUE, JSON_UNESCAPED_UNICODE); // TRUE - convert to associative array
		ksort($arrToSort, SORT_NATURAL | SORT_FLAG_CASE);

		return json_encode($arrToSort, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
	}

	/**
	 * Perform sort and return result as string
	 * 
	 * @param string $filepath
	 * @param array  $execSetup
	 * 
	 * @return string
	 */
	function performSortAndBeautify($filepath, $execSetup)
	{
		$result = '';

		// get supplied filename extension
		// -------------------------------
		$filenameParts = explode('.', $filepath);
		$fileExt = end($filenameParts);

		// preform sort and string operations
		// ----------------------------------
		if ($fileExt == 'php') {
			$result = performPhpLangFileSortAndBeautify($filepath, $execSetup);
		}
		elseif ($fileExt == 'json') {
			$result = performJsonLangFileSortAndBeautify($filepath, $execSetup);
		}
		else {
			fwrite(STDERR, PROGNAME . ": Invalid file extension detected. Expecting 'php' or 'json'. Exiting..." . PHP_EOL);
			exit(1);
		}

		return $result;
	}


	/**
	 * Write result to a file.
	 * 
	 * @param string  $filepath
	 * @param string  $result
	 * @param boolean $ask
	 * 
	 * @return void
	 */
	function writeResultToFile($filepath, $result, $ask=FALSE)
	{
		if( ! is_writable($filepath) ) {	
			fwrite(STDERR, PROGNAME . ": Invalid file permissions. File is not writable." . PHP_EOL);
			exit(1);
		}

		if($ask) {
			$answer = prompt("NOTE: All old file contents will be overwritten. Continue (yes/no)", ["yes", "no"]);

			if($answer == "no") {
				echo PROGNAME. ": Exiting..." . PHP_EOL;
				exit(0);
			}
		}

		try {
			file_put_contents($filepath, $result);
		}
		catch(Exception $e) {	
			fwrite(STDERR, PROGNAME . ": Writing file error -> " . $e->message);
			exit(1);
		}

		echo PROGNAME . ": Sorted result written to a file '$filepath'" . PHP_EOL;	
	}


	/**
	 * Parse CLI arguments and return array with execution setup.
	 * 
	 * @return array
	 */
	function parseCliArguments()
	{
		// inplace and outputTarget are mutually exclusive
		$execSetup = [
			'inplace' => NULL,
			'recursive' => NULL,
			'force' => NULL,
			'output_target' => NULL // TODO: if there is more than one input file than throw error
		];

		// parse CLI arguments
		// ----------------------
		$shortOptions = "hrif";
		$longOptions = ['help', 'recursive', 'inplace', 'force'];
		$optionsExplanation = [
				"\t -h \t --help \t Print this help message", 
				"\t -r \t --recursive \t Go through the directory files recursively", 
				"\t -i \t --inplace \t Sort inplace (overwrite)", 
				"\t -f \t --force \t Do not ask me anything, ignore warnings."
		];

		$optArgs = getopt($shortOptions, $longOptions);
		$nonOptArgs = getNonOptionArguments();
		$execSetup['non_opt_args'] = $nonOptArgs;

		if( ($res = checkOptionArgumentsExists($shortOptions, $longOptions)) !== TRUE) {
			fwrite(STDERR, PROGNAME . ': ' . $res . PHP_EOL);
			exit(1);
		}

		// go through all known options
		if( isset($optArgs['h']) || isset($optArgs['help'])) {
			printHelp($optionsExplanation);
			exit(1);
		}
		if( isset($optArgs['i']) || isset($optArgs['inplace'])) {
			$execSetup['inplace'] = TRUE;
		}
		if( isset($optArgs['r']) || isset($optArgs['recursive'])) {
			$execSetup['recursive'] = TRUE;
		}
		if( isset($optArgs['f']) || isset($optArgs['force'])) {
			$execSetup['force'] = TRUE;
		}

		// check if required non option argument is received
		if(count($nonOptArgs) < 1) {
			fwrite(STDERR, PROGNAME . ': Filename or directory name is required. Use ' 
				. '--help option for usage explanation.' . PHP_EOL);
			exit(1);
		}

		return $execSetup;
	}


	/**
	 * Perform required action on one file.
	 * 
	 * @param string $filepath
	 * @param array  $execSetup
	 * 
	 * @return boolean
	 */
	function performActionOnFile($filepath, $execSetup)
	{
		if( ! file_exists($filepath) ) {
			fwrite(STDERR, PROGNAME . ': Invalid file path' . PHP_EOL);
			exit(1);
		}
		if( ! is_readable($filepath) ) {	
			fwrite(STDERR, PROGNAME . ': Invalid file permissions. File is not readable.' . PHP_EOL);
			exit(1);
		}

		$result = performSortAndBeautify($filepath, $execSetup);

		if($result === FALSE) {
			fwrite(STDERR, PROGNAME . ": Skipping file '$filepath'" . PHP_EOL);
			return FALSE;
		}

		if(isset($execSetup['inplace'])) {
			writeResultToFile($filepath, $result);
		}
		elseif(isset($execSetup['outputTarget'])) {
			writeResultToFile($execSetup['outputTarget'], $result); // TODO: set this when comment parsing feature is finished -> , $execSetup['force'] ? FALSE : TRUE);
		}
		else {
			echo $result;
		}

		return TRUE;
	}


	/**
	 * Perform required actions on directory contents.
	 * If $execSetup key 'recursive' is set, then perform recursion.
	 * 
	 * @param string $filepath
	 * @param array  $execSetup
	 * 
	 * @return void 
	 */
	function performActionOnDirectoryContents($dirpath, $execSetup)
	{
		if(isset($execSetup['recursive'])) {
			$dirIterator = new \RecursiveDirectoryIterator($dirpath, 
												\FilesystemIterator::SKIP_DOTS);

			foreach (new \RecursiveIteratorIterator($dirIterator) as $fileInfo) {

				$filepath = $fileInfo->getPathname();
				$res = performActionOnFile($filepath, $execSetup);
			}
		}
		else {
			$dirIterator = new \DirectoryIterator($dirpath); 

			foreach (new \IteratorIterator($dirIterator) as $fileInfo) {

				$filepath = $fileInfo->getPathname();

				if($fileInfo->isDir()) {
					//echo "Skipping dir " . $filepath . "\n";
					continue;
				}
				//echo $fileInfo->getPathname() . "\n";
				$res = performActionOnFile($filepath, $execSetup);
			}
		}

	}
	

	/**
	 * If filepath is dir then recursivly go through files.
	 * 
	 * @param string $filepath
	 * @param array  $execSetup
	 * 
	 * @return void
	 */
	function pathLooper($filepath, $execSetup)
	{
		if(is_dir($filepath) ) {
			performActionOnDirectoryContents($filepath, $execSetup);
		}
		else {
			performActionOnFile($filepath, $execSetup);
		}
	}


	/**
	 * Script's main() function
	 * 
	 * @return void
	 */
	function main() 
	{
		$execSetup = parseCliArguments();

		// loop through all non option arguments and if filepath is a file then 
		// instantly perform the sort operations. If filepath is a directory then
		// go through it recursively if --recursive option was set and perform 
		// sort operation on files.
		foreach($execSetup['non_opt_args'] as $filepath) {
			pathLooper($filepath, $execSetup);
		}
	}


	// ---------- //
	// run script //
	// ---------- //
	main();
	exit(0);

?>
