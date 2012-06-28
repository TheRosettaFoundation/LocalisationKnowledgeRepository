<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/

/*
 * Here, the LocConnect job ids are retrieved by the fetch_jobs method in the API
 * the author then chooses the CNLF jobs he/she would like to import
 * @author: David O Carroll
 * Modified by: Eoin Ó Conchúir
 */
require($_SERVER['DOCUMENT_ROOT'].'/scripts/init.php');
//Call the fetch_jobs method on the cnlf server
header ("Content-Type:text/html; charset=utf-8");
$settings = new Settings();		//get the url from conf
$request = new HTTP_Request2($settings->get('cnlf.url').'/fetch_job.php', HTTP_Request2::METHOD_GET);
$request->setHeader('Accept-Charset', 'utf-8');
$url = $request->getUrl();
$url->setQueryVariable('com', 'LKR');         // set your component name here
// This will get a list of pending jobs from the CNLF server and store them $jobs variable;
$jobs = $request->send()->getBody();
// Get the job ids using xpath to navigate the xml file
$xml = new SimpleXMLElement($jobs);
$job_ids = $xml->xpath('/jobs/job');

//include the script to check/uncheck all checkboxes
$header = array('title' => 'LKR - CNLF Import',
				'extra_scripts' => '<script language="JavaScript" src="/resources/js/check_uncheck.js"></script>');
Template::header($header);

echo '<h3>Import LocConnect Jobs</h3><br />';
if($job_ids)
{
	// create a form for sending the ids to the import function
	echo '<form name="form1" method="POST" action="/scripts/import.solas.php">';
	//add the buttons to check/uncheck all checkboxes
	echo '<p><input type="button" value="Select All" onClick="javascript:checkAll();" />';
	echo '<input type="button" value="Deselect All" onClick="javascript:checkNone();" /></p>';
	foreach($job_ids as $job_id)
	{
		// This places the ids in the POST variable under and array called job
		echo '<p><input type="checkbox" name="job[]" value="'.$job_id.'" checked />&nbsp;&nbsp;Import job '.$job_id.'</p>';
	}
	//add the submit buttons
	echo '<p><input type="submit" name="submit" value="Import" /></p>';
}
else
{
	//if fetch jobs returns false
	echo '<br /><p>No LocConnect jobs currently available</p>';
}

Template::footer();
