<?php
 /**
 * Preview Import Information
 **/

	// Options Vars
	$spreadsheet_url = get_option('sheets_url');
	$cptslug = get_option('cpt_slug');
	
	$g_title = get_option('g_title');
	$g_content = get_option('g_content');
	$g_acf_group = get_option('g_acf_group');


	$csv = array_map('str_getcsv', file($spreadsheet_url));
	array_walk($csv, function(&$a) use ($csv) {
	      $a = array_combine($csv[0], $a);
	    });
	    array_shift($csv); # remove column header

	foreach ($csv as $c ) {

	echo $c[$g_title].' - '.$c[$g_content].'</br>';
	break;

	}?>

<hr>



</div>