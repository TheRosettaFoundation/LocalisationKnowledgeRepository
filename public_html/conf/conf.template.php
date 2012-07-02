<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/

/** Database **/
$db = array();

// MySQL settings
$db['database'] = 'lkr';
$db['server'] = 'localhost';
$db['username'] = 'ulkr';
$db['password'] = 'lkr';
$db['show_errors'] = 'y';
$db['show_sql'] = 'n';
$db['log_file'] = '';

// File locations
$files['dir_raw'] = __DIR__.'/../files/raw'; // Ensure the folder is writable.
$files['dir_segmented'] = __DIR__.'/../files/segmented'; // Ensure the folder is writable.
$files['segmenter'] = __DIR__.'/../segment-java/bin/segment';
$files['srx'] = __DIR__.'/../segment-java/example/simple.srx';
$files['language'] = 'english';

/*
    $domain['root'] is the directory in which the lkr is installed
    e.g. for path /opt/lampp/htdocs/lkr/index.php $domain['root'] = 'lkr'
    No leading or trailing slashes are required
    If installed in the web server root just leave blank
*/
$domain['root'] = 'root_dir';
$cnlf['url'] = 'http://193.1.97.50/locconnect/';

?>
