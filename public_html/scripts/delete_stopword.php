<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/
/*
 * A script used to delete a custom guideline from the database
 * @author: David O Carroll
 */
require(__DIR__.'/init.php');

$settings = new Settings();
$domain_root = $settings->path_to_domain_root($_SERVER);

$stopword_id = intval(IO::get_val('stopword_id'));
$sql = new MySQLHandler();
$sql->init();
$q = 'DELETE FROM stopwords
		WHERE stopword_id = '.$stopword_id;
$sql->Delete($q);
header('Location: '.$domain_root.'/pm/configuration/guidelines/');
die;
