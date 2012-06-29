<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/
/*
 * Script used to mark a job in the database as closed
 * @author: David O Carroll
 */
require($_SERVER['DOCUMENT_ROOT'].'/scripts/init.php');

$job_id = intval(IO::get_val('job_id'));
//$cnlf is a flag that is set to 1 if the current user navigated to this page from the CNLF server
$cnlf = intval(IO::get_val('cnlf'));
//$date is the current date and time
$date = date('Y-m-d H:i:s', time());
$sql = new MySqlHandler();
$sql->init();
$q = 'UPDATE jobs
		SET closed_date = "'.$date.'"
		WHERE job_id = '.$job_id;
$sql->Update($q);

if($cnlf == 1)
{
	//Go back to the CNLF server
	$settings = new Settings();
	header('Location: '.$settings->get('cnlf.url').'/');
}
else
{
	//Go to the Export options
	header('Location: /pm/export_options.php?job_id='.$job_id.'' ) ;
}
die;