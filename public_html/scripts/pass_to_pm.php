<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/
/*
 * Script used to pass a job to the PM
 * @author: David O Carroll
 */
require($_SERVER['DOCUMENT_ROOT'].'/scripts/init.php');

$job_id = intval(IO::get_val('job_id'));
$job = new Job($job_id);
$sql = new MySqlHandler();
$sql->init();
//Set the complete date in the DB equal to the current date and time
$q = 'UPDATE jobs
		SET complete_date = "'.date('Y-m-d H:i:s', time()).'"
		WHERE job_id = '.$job_id;
$sql->Update($q);
if($segments = $job->getSegments())
{
	foreach($segments as $seg)
	{
		$q = 'UPDATE segments
				SET comment=NULL
				WHERE job_id='.$job_id.'
				AND segment_id = '.$seg->getSegmentID();
		$sql->Update($q);
	}
}
// send feedback saying that the job has been sent for review
$CNLF_id = $job->getCNLFid();
if($CNLF_id != null)
{
	header ("Content-Type:text/html; charset=utf-8");
	$settings = new Settings();
	$request = new HTTP_Request2($settings->get('cnlf.url').'/send_feedback.php', HTTP_Request2::METHOD_GET);
	$request->setHeader('Accept-Charset', 'utf-8');
	$url = $request->getUrl();
	$url->setQueryVariable('com', 'LKR');         // set your component name here
	$url->setQueryVariable('id', $CNLF_id);         // set job id here
	// set your component's feedback here:
	$server = IO::server();
	$url->setQueryVariable('msg', 'Waiting for <a href="'.$server.'/pm/view/'.$job_id.'/cnlf/">Review</a>'); 

	// This will get the server response 
	$response=$request->send()->getBody();
	$words = array('<response>', '</response>', '<msg>', '</msg>');
	$response = str_replace($words, '', $response);
	if($response != 'Feedback Updated')
	{
		echo $response;
	}
}
$server = IO::server();
header('Location: '.$server.'author/completed/'.$job_id.'/' ) ;
die;