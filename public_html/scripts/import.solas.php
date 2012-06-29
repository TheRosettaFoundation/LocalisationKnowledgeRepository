<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/

/*
 * This script takes the jobs that were selected and imports them
 * It creates the job in the LKR DB.
 * It updates the LocConnect server by updating its status and feedback.
 * @author: David O Carroll
 */

// Uncomment the two next lines for error reporting (useful for debugging).
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

require(__DIR__.'/init.php');
if(isset($_POST['submit']) && ($_POST['submit'] == 'Import') && isset($_POST['job']))
{
	$job_ids = $_POST['job'];		//this returns an array of cnlf job ids
	if($job_ids)					//or false if there aren't any
	{
		$settings = new Settings();
		//find the ids of the jobs that were selected
		foreach($job_ids as $solas_job_id)	//for every job id create a new job in the lkr
		{
			if ($file_xml = Solas::getJob($solas_job_id))
			{
				// Job was successfully retrieved from LocConnect.
				
				/* When using xpath this must be changed, otherwise an 
				 * empty array is returned
				 */
				$xml = new SimpleXMLElement(str_replace('xmlns=', 'ns=', $file_xml));			

				// Extract meta data.	
				$phase = $xml->xpath('/xliff/file/header/phase-group/phase[@phase-name="Quality Assurance"]');
				$author_name = false;
				$email_address = false;
				$company_name = false;
				if (count($phase)>0)
				{
					foreach($phase[0]->attributes() as $attribute => $value)
					{
						if ($attribute == 'contact-name')
						{
							$author_name = $value;
						}
						elseif ($attribute == 'contact-email')
						{
							$email_address = $value;
						}
						elseif ($attribute == 'company-name')
						{
							$company_name = $value;
						}
					}
				}
				$domain = false;
				$target_language = false;
				$source_language = false;
				$original_file = false;
				//get the rest of the meta data in the file tag
				$file_tag = $xml->xpath('/xliff/file');
				if (count($file_tag[0]) > 0)
				{
					foreach($file_tag[0]->attributes() as $attribute => $value)
					{
						if($attribute == 'category' && !empty($value))
						{
							$domain = $value;
						}
						elseif($attribute == 'target-language' && !empty($value))
						{
							$target_language = $value;
						}
						elseif($attribute == 'source-language' && !empty($value))
						{
							$source_language = $value;
						}
						elseif($attribute == 'original' && !empty($value))
						{
							$original_file = $value;
						}
					}
				}
				$sql = new MySQLHandler();
				$sql->init();
				if ($job_id = Job::insert($sql, $author_name, $email_address, $company_name, $domain, $source_language, $target_language, $original_file, $file_xml, $solas_job_id))
				{
					IO::createSegmentsFromXLIFF($sql, $job_id, $file_xml);
					
					//set up initial warnings
					$job = new Job($job_id);
					$job->setInitialWarnings();
					
					//Update the job on the LocConnect server to processing
					$status_set = Solas::setStatus($solas_job_id, 'processing');
					if($status_set !== true)
					{
						echo '<p>Could not set status of Solas Job ID: '.$job_id.'</p>';
						echo $status_set; die;
					}
					
					//Print a message that links to setting the guidelines
					if (Solas::sendFeedback($solas_job_id, 'Change <a href="'.IO::server().'pm/configuration/guidelines/'.$solas_job_id.'/>Guideline</a> Settings.') != 'Feedback Updated')
					{
						echo $response; die;
					}
				}
			}
		} // foreach
	}
}
header('Location: ../author/');
