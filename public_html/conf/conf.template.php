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

$files['dir_raw'] = $_SERVER['DOCUMENT_ROOT'].'/files/raw';
$files['dir_segmented'] = $_SERVER['DOCUMENT_ROOT'].'/files/segmented';
$files['segmenter'] = $_SERVER['DOCUMENT_ROOT'].'/segment-java/bin/segment';
$files['srx'] = $_SERVER['DOCUMENT_ROOT'].'/segment-java/example/simple.srx';
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
