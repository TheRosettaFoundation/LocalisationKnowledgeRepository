<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/
/*
 * A script used to send a job back to the author for more work
 * @author: David O Carroll
 */
require($_SERVER['DOCUMENT_ROOT'].'/scripts/init.php');
 
// Get incoming parameters
$job_id = intval(IO::get_val('job_id'));
// This is a flag showing that the current user came from the cnlf server and should be sent back after script completion
$cnlf = intval(IO::get_val('cnlf'));
$job = new Job($job_id);
$send_backs = ($job->getSendBacks() + 1);
$report = new Report($job);
$init_warnings = $job->countWarnings($report);
$sql = new MySqlHandler();
$sql->init();
// Re-setup initial warnings
$q = 'UPDATE jobs
		SET complete_date = NULL, 
			initial_warnings = '.$init_warnings.',
			total_send_backs = '.$send_backs.'
		WHERE job_id = '.$job_id;
$sql->Update($q);

// send feedback saying that the job has been sent for more work
$CNLF_id = $job->getCNLFid();
if($CNLF_id != null)
{
	//Must be a CNLF job, send the feedback
	header ("Content-Type:text/html; charset=utf-8");
	$settings = new Settings();
	$request = new HTTP_Request2($settings->get('cnlf.url').'/send_feedback.php', HTTP_Request2::METHOD_GET);
	$request->setHeader('Accept-Charset', 'utf-8');
	$url = $request->getUrl();
	$url->setQueryVariable('com', 'LKR');         // set your component name here
	$url->setQueryVariable('id', $CNLF_id);         // set job id here
	// set your component’s feedback here:
	$url->setQueryVariable('msg', 'Sent back to author for more work'); 

	// This will get the server response 
	$response=$request->send()->getBody();
	$words = array('<response>', '</response>', '<msg>', '</msg>');
	$response = str_replace($words, '', $response);
	if($response != 'Feedback Updated')
	{
		echo $response;
	}
}
if($cnlf == 1)
{
	$settings = new Settings();
	header('Location: '.$settings->get('cnlf.url').'/');
}
else
{
	header('Location: /pm/sentback/'.$job_id.'/' ) ;
}
die;