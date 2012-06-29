<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/
/*
 * Script used to import a file and store it in the database
 * @author: David O Carroll
 * @edited: Eoin Ó Conchúir
 */
require(__DIR__.'/../scripts/init.php');

/* Generate new job id */
$sql = new MySQLHandler();
$settings = new Settings();
$sql->init();
$name = IO::post_val('full_name');
if(strlen($name) != 0)
{
	// a name was entered, store it in a cookie
	setcookie('author_name', $name, 0, '/');
}
else
{
	// the name field is mandatory, go back and enter it again
	header('Location: '.Settings::path_to_public_html($_SERVER).'/author/invalid_name/');
	die;
}
$email = IO::post_val('email');
if(strlen($email) != 0)
{
	// An email address was entered, place it in a cookie for later
	setcookie('email_address', $email, 0, '/');
}
$domain = IO::post_val('domain');
if(strlen($domain) != 0)
{
	// A domain was entered store it in a cookie
	setcookie('domain', $domain, 0, '/');
}
$companyName = IO::post_val('company_name');
if(strlen($companyName) != 0)
{
	//A company name was entered, store it in a cookie
	setcookie('company_name', $companyName, 0, '/');
}

if (is_uploaded_file($_FILES['import_file']['tmp_name'])) // Checks for an uploaded file
{
	$job_id = false;
	// Check if it's an XLIFF file being uploaded.
	$ext = strtolower(array_pop(explode('.',$_FILES['import_file']['name'])));
	if ($ext == 'xlf' || $ext == 'xliff')
	{
		$xliff_file_contents = file_get_contents($_FILES['import_file']['tmp_name']);

		// Override provided domain field with the one in XLIFF, if it's set!
		$doc = new DOMDocument();
		$doc->loadXML($xliff_file_contents);
		if ($r = $doc->getElementsByTagName('file'))
		{
			$domain_from_file = $r->item(0)->getAttribute('category');
			if (!empty($domain) && !empty($domain_from_file))
			{
				$domain = $domain_from_file; // Ignore the information submitting through the form, take from the file instead.
			}
		}
		
	  	$job_id = Job::insert($sql, $name, $email, $companyName, $domain, null, null, $_FILES['import_file']['name'], $xliff_file_contents);
	  	IO::createSegmentsFromXLIFF($sql, $job_id, $xliff_file_contents);
	}
	else if ($ext == 'txt')
	{
		$job_id = Job::insert($sql, $name, $email, $companyName, $domain);
		// Import the file, which includes saving the file to the file system.
		$imported_file_path = IO::saveImport($settings, $job_id);
		if ($imported_file_path)
		{
			// Segment the imported file.
			IO::segmentFile($settings, $job_id);
			// Get the info into the database.
			$count_segments = IO::importXML($settings, $sql, $job_id);
		}
		else
		{
			echo "Couldn't import file. Please go back and try again"; die;
		}
	}
	if ($job_id)
	{
		//set up initial warnings
		$job = new Job($job_id);
		$report = new Report($job);
		$init_warnings = $job->countWarnings($report);
		$q = 'UPDATE jobs
				SET initial_warnings = '.$init_warnings.', import_date = "'.date("Y-m-d H:i:s",time()).'"
				WHERE job_id = '.$job_id;
		$sql->Update($q);

		// Forward to view.
		$host  = $_SERVER['HTTP_HOST'];
		//$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
		$extra = Settings::path_to_public_html($_SERVER).'/author/view/'.$job_id.'/analyse/';
		header("Location: http://$host$uri$extra");
	}
	else 
	{
		echo "Could not obtain a job_id for this new job. Something's gone wrong.";
	}
}
//elseif($operation == 'Import This Text')
else if (!empty($_POST['import_textarea']))
{
	// Import the text written in the text area
	$settings = new Settings();
	$sql = new MySQLHandler();
	$sql->init();
	$q = 'INSERT INTO jobs(author_name, email_address, domain) VALUES("'.$name.'", "'.$email.'", "'.$domain.'")';
	$job_id = $sql->Insert($q);
	$text = $_POST['import_textarea'];
	// Save the text area to a file
	if(IO::saveTextArea($settings, $job_id, $text))
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
		
		// Forward to view.
		$host  = $_SERVER['HTTP_HOST'];
		//$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
		$extra = Settings::path_to_html($_SERVER).'/author/view/'.$job_id.'/analyse/';
		header("Location: http://$host$uri$extra");
	}
}
else
{
	echo 'Please either select a file to import, or paste the text in the text area provided.';
}
die;
