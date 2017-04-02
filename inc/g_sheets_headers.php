<?php
// Options Vars
$spreadsheet_url = get_option('sheets_url');

 /**
 * Preview Headers Information
 **/
echo '<em>To help, here are your column headers: </em>';
	
	$csv = array_map('str_getcsv', file($spreadsheet_url));
	array_walk($csv, function(&$a) use ($csv) {
	      $a = array_combine($csv[0], $a);
	    });
	    //array_shift($csv); # remove column header


	$string = implode(",", $csv[0]);
	echo '<code>'.$string.'</code>';

?>