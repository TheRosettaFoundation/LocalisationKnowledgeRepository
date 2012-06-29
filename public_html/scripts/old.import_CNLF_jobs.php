<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/

/*
 * This script gets all the jobs from the CNLF and imports them into the LKR
 * @author: David O Carroll
 */
//serror_reporting(E_ALL);
//ini_set('display_errors', '1');
require($_SERVER['DOCUMENT_ROOT'].'/scripts/init.php');
//get a list of jobs from the CNLF server
header ("Content-Type:text/html; charset=utf-8");
$settings = new Settings();
$request = new HTTP_Request2($settings->get('cnlf.url').'/fetch_job.php', HTTP_Request2::METHOD_GET);
$request->setHeader('Accept-Charset', 'utf-8');
$url = $request->getUrl();
$url->setQueryVariable('com', 'LKR');         // set your component name here
// This will get a list of pending jobs from the CNLF server and store them $jobs variable;
$jobs = $request->send()->getBody();
// Get the job ids using xpath to navigate the xml file
$xml = new SimpleXMLElement($jobs);
$job_ids = $xml->xpath('/jobs/job');

// if there are no job ids then xpath returns false
if($job_ids)
{
	foreach($job_ids as $jobID)
	{
		// get this specific job's data
		header ("Content-Type:text/html; charset=utf-8");
		$request = new HTTP_Request2($settings->get('cnlf.url').'/get_job.php', HTTP_Request2::METHOD_GET);
		$request->setHeader('Accept-Charset', 'utf-8');
		$url = $request->getUrl();
		$url->setQueryVariable('id', $jobID);   //set the job id here
		$url->setQueryVariable('com', 'LKR');		  // set your component name here
		// This will fetch the given job from the CNLF server and store content in $file variable;
		$file = $request->send()->getBody();
		$sql = new MySQLHandler();
		$settings = new Settings();
		$sql->init();
				
		// when using xpath this must be changed, otherwise an empty array is returned
		$file = str_replace('xmlns=', 'ns=', $file);
				
		$xml = new SimpleXMLElement($file);
		//find all the meta data within the xliff file
		// Starting with the phase element
		$phase = $xml->xpath('/content/xliff/file/header/phase-group/phase');
		foreach($phase[0]->attributes() as $attribute => $value)
		{
			if($attribute == 'contact-name')
			{
				$author_name = $value;
			}
			elseif($attribute == 'contact-email')
			{
				$email_address = $value;
			}
			elseif($attribute == 'company-name')
			{
				$company_name = $value;
			}
		}
		// Then the file element
		$file_tag = $xml->xpath('/content/xliff/file');
		foreach($file_tag[0]->attributes() as $attribute => $value)
		{
			if($attribute == 'category')
			{
				$domain = $value;
			}
			elseif($attribute == 'target-language')
			{
				$target_language = $value;
			}
			elseif($attribute == 'source-language')
			{
				$source_language = $value;
			}
			elseif($attribute == 'original')
			{
				$original_file = $value;
			}
		}
		// Create a new job
		$q = 'INSERT INTO jobs (CNLF_id, author_name, email_address, company_name, domain, source_language, target_language, original_file) 
				VALUES ("'.$jobID.'", "'.$author_name.'", "'.$email_address.'", "'.$company_name.'", "'.$domain.'", 
						"'.$source_language.'", "'.$target_language.'", "'.$original_file.'")';
		// Insert returns the id of the new job
		$job_id = $sql->Insert($q);
		
		// Extract the file from the xliff
		$file = $xml->xpath('/content/xliff/file/header/reference/internal-file/converted-file');
		$file = $file[0];
		// save the file to disc
		if(IO::saveTextArea($settings, $job_id, $file))
		{
			// Segment the imported file.
			IO::segmentFile($settings, $job_id);
			// Get the info into the database.
			$count_segments = IO::importXML($settings, $sql, $job_id);
			
			//set up initial warnings
			$job = new Job($job_id);
			$report = new Report($job);
			$init_warnings = $job->countWarnings($report);
			$q = 'UPDATE jobs
					SET initial_warnings = '.$init_warnings.', import_date = "'.date("Y-m-d H:i:s",time()).'"
					WHERE job_id = '.$job_id;
			$sql->Update($q);
			
			//Update the job on the CNLF server
			header ("Content-Type:text/html; charset=utf-8");
			$request = new HTTP_Request2($settings->get('cnlf.url').'/set_status.php', HTTP_Request2::METHOD_GET);
			$request->setHeader('Accept-Charset', 'utf-8');
			$url = $request->getUrl();
			$url->setQueryVariable('com', 'LKR');         // set your component name here
			$url->setQueryVariable('id', $jobID);         // set job id here
			$url->setQueryVariable('msg', 'processing');         // set status id here

			// This will get the server response 
			$response=$request->send()->getBody();
			$words = array('<response>', '</response>', '<msg>', '</msg>');
			$response = str_replace($words, '', $response);
			if(!($response == 'Status Updated'))
			{
				echo '<p>Job ID: '.$jobID.'</p>';
				echo $response;
			}
		}
		else
		{
			echo 'Couldn\'t import file. Go back and try again';
		}
	}
}
// Go back to author central
header('Location: /author/');
die;