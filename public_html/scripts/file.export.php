<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/

/*
 * This script is used to export a job
 * it can export in three ways:
 *		1. As an XLIFF file
 * 		2. As a txt
 *		3. As XLIFF to the LocConnet server
 * @author: David O Carroll
 * Modified by: Eoin Ó Conchúir
 */
require(__DIR__.'/init.php');

$settings = new Settings();
$domain_root = $settings->path_to_domain_root($_SERVER);

$job_id = intval(IO::get_val('job_id'));
$job = new Job($job_id);

//Retrieve all the metadata from the form
$export_job_id = isset($_POST['job_id']) ? IO::post_val('job_id') : false;
$CNLF_id = isset($_POST['CNLF_id']) ? IO::post_val('CNLF_id') : false;
$author_name = isset($_POST['author_name']) ? IO::post_val('author_name') : false;
$email_address = isset($_POST['email_address']) ? IO::post_val('email_address') : false;
if (isset($_POST['include_domain']))
{
	$domain = isset($_POST['domain']) ? IO::post_val('domain') : false;
}
$original = isset($_POST['original']) ? IO::post_val('original') : false;
$company_name = isset($_POST['company_name']) ? IO::post_val('company_name') : false;
$word_count = isset($_POST['word_count']) ? IO::post_val('word_count') : false;
$segment_count = isset($_POST['segment_count']) ? IO::post_val('segment_count') : false;
$character_count = isset($_POST['character_count']) ? IO::post_val('character_count') : false;
$language_source = isset($_POST['language_source']) ? IO::post_val('language_source') : false;
$target_language = isset($_POST['target_language']) ? IO::post_val('target_language') : false;
$tool = isset($_POST['tool']) ? IO::post_val('tool') : false;
$comment = isset($_POST['comment']) ? IO::post_val('comment') : false;

$data = '';

$file_extention = $job->fileExtention(); 
if ($file_extention == 'xlf' || $file_extention == 'xliff' || $job->isSolasJob())
{
	// Get the XML from the database, update the metadata and segment.
	$simple_xml = new SimpleXMLElement($job->getInputXLIFFStr());
	$simple_xml = $job->xmlUpdateMetadata($simple_xml, $domain, $word_count, $segment_count, $character_count, $author_name, $email_address, $company_name, $comment);
	$simple_xml = $job->xmlUpdateSegments($simple_xml);
	$data = $simple_xml->asXML();
}
else if(isset($_POST['save_to_xliff']))
{
	// Generate the contents of the file to be exported
	//Concatenate all the text file to the data variable for use later
	$data = "<?xml version='1.0' encoding='UTF-8'?>\n";
	$data .= "<xliff version='1.2' xmlns='urn:oasis:names:tc:xliff:document:1.2'\t
			xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'\t
			xsi:schemaLocation='urn:oasis:names:tc:xliff:document:1.2\t
			xliff-core-1.2-transitional.xsd'>\n";
	//Add the meta data
	$data .= "\t<file original='".$original."' source-language='".$language_source."' target-language='".$target_language."' datatype='plaintext'\n";
	$data .= "\tcategory='".$domain."' tool='".$tool."'>\n";
	$data .= "\t\t<header>\n";
	$data .= "\t\t\t<phase-group>\n";
	$data .= "\t\t\t\t<phase phase-name='Quality Assurance' company-name='".$company_name."' process-name='authoring'\n";
	$data .= "\t\t\t\t\tcontact-name='".$author_name."' contact-email='".$email_address."' job-id='".$job_id."' tool-id='LKR' />\n";
	$data .= "\t\t\t</phase-group>\n";
	$data .= "\t\t\t<tool tool-name='LKR' tool-id='LKR' tool-version='v1'></tool>\n";
	if(isset($_POST['comment']))		//If the user posted a comment from the previous page
	{
		$comment = $_POST['comment'];
		$data .= "\t\t\t<note>".$comment."</note>\n";
	}
	$data .= "\t\t</header>\n";
	$data .= "\t\t<body>\n\n";
	// add the count metadata
	$data .= "\t\t\t<group>\n";
	$data .= "\t\t\t\t<count-group name='word_count'>\n";
	$data .= "\t\t\t\t\t<count count-type='total' unit='word'>".$word_count."</count>\n";
	$data .= "\t\t\t\t</count-group>\n";
	$data .= "\t\t\t\t<count-group name='segment_count'>\n";
	$data .= "\t\t\t\t\t<count count-type='total' unit='segment'>".$segment_count."</count>\n";
	$data .= "\t\t\t\t</count-group>\n";
	$data .= "\t\t\t\t<count-group name='character_count'>\n";
	$data .= "\t\t\t\t\t<count count-type='total' unit='character'>".$character_count."</count>\n";
	$data .= "\t\t\t\t</count-group>\n";
	$data .= "\t\t\t</group>\n\n";
	if($segments = $job->getSegments())
	{
		foreach($segments as $segment)
		{
			//Add the segment data for each segment
			$data .= "\t\t\t<trans-unit id='#".$segment->getSegmentID()."'>\n";
			$data .= "\t\t\t\t".$segment->getTargetRaw()."\n\t\t\t\t\n";
			$data .= "\t\t\t\t<target></target>\n";		//This remains blank
			$data .= "\t\t\t</trans-unit>\n";
		}
	}
	$data .= "\t\t</body>\n";
	$data .= "\t</file>\n";
	$data .= "</xliff>";
}

if(isset($_POST['save_to_xliff']))
{
	header('Content-type: application/xlf');
	header('Content-Disposition: attachment; filename="Job_'.$job_id.'.xlf"');
	
	// Echo the file to the browser
	echo $data;
	die;
}
else if(isset($_POST['save_to_txt']))
{
	header('Content-type: application/txt');
	header('Content-Disposition: attachment; filename="Job_'.$job_id.'.txt"');
	if($segments = $job->getSegments())
	{
		foreach($segments as $segment)
		{
			echo $segment->getTargetRaw();
		}
	}
	die;
}
else if (isset($_POST['save_to_locconnect']))
{
	// Send the xliff file to the LocConnect server and remove any indication in the database the it belongs to the cnlf
	// Send feedback
	$solas_job_id = $job->getCNLFid();
	if (Solas::sendOutput($solas_job_id, $data))
	{
		$feedback = 'Job is now quality assured with a total of '.(isset($segment_count) ? $segment_count : 0).' segment'.( (isset($segment_count) && $segment_count != 1) ? 's' : '').'.';
		$feedback_sent = Solas::sendFeedback($solas_job_id, $feedback);
		if ($feedback_sent !== true)
		{
			echo "Could not set Solas feedback: ".$feedback_sent;
			die;
		}
		$status_set = Solas::setStatus($solas_job_id, 'complete');
		if ($status_set !== true)
		{
			echo "Could not set Solas stats: ".$status_set;
		}

		// Cleaning up - policy is to remove LocConnect job information
		// once it has been exported to LocConnect.
		$sql = new MySQLHandler();
		$sql->init();
		//remove any reference to it from the DB in cnlf_guidelines
		$q = 'DELETE FROM cnlf_guidelines
				WHERE CNLF_id = "'.$job->getCNLFid().'"';
		$sql->Delete($q);
		//then disconnect the completed job from the CNLF to prevent it from accidentaly being updated
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'UPDATE jobs
				SET CNLF_id = null
				WHERE job_id = '.$job->getJobID();
		$sql->Update($q);
	}
}

//Reload page when finished or return to pm central if finished
if(isset($_POST['done'])) {
	header('Location: '.$domain_root.'/pm/');
} else {
    header('Location: '.$domain_root.'/pm/export/'.$job_id.'/');
}
die;
