<?php
// Options Vars
$spreadsheet_url = get_option( 'sheets_url' );

/**
 * Preview Headers Information
 */
echo '<em>To help, here are your column headers: </em>';

// Pull the CSV file and parse it into an array.
// NOTE:  This should be switched out for fgetcsv instead, 
// as in processing.php line 61.	
$csv = array_map( 'str_getcsv', file( $spreadsheet_url ) );

// Get only the first row of header data.
$string = implode( ",", $csv[0] );
echo '<code>' . $string . '</code>';
