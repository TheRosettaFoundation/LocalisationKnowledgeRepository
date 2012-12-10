<?php

/*
 * To handle interaction with Solas/LocConnect. As a legacy, some
 * functions that should be found here are actually in IO.class.php.
 */
class Solas
{
	
	/*
	 * Request the file from LocConnect. Return the XML string if
	 * successful.
	 */
	static function getJob($jobID)
	{
		$ret = false;
		// get this specific job's data
		header ("Content-Type:text/html; charset=utf-8");
		$settings = new Settings();
		$request = new HTTP_Request2($settings->get('cnlf.url').'/get_job.php', HTTP_Request2::METHOD_GET);
		$request->setHeader('Accept-Charset', 'utf-8');
		$url = $request->getUrl();
		$url->setQueryVariable('id', $jobID);   //set the job id here
		$url->setQueryVariable('com', 'LKR');		  // set your component name here
		// This will fetch the given job from the CNLF server and store content in $file variable;
		if ($response = $request->send()->getBody())
		{
			// $response contains an XML document. Within the <content> 
			// tags is the XLIFF content.
			$xliff_xml = false;
			$xliff_container = new DOMDocument();
                        $xliff_container->loadXML($response);
                        $xliff_xml=$xliff_container->getElementsByTagName("xliff")->item(0);
			if ($xliff_xml)
			{
                            // Success. Return the XLIFF string.
                            $ret = $xliff_container->saveXML($xliff_xml);
			}
		}
		return $ret;		
	}
	
	/*
	 * Possible status strings:
	 * 'processing', 'complete' .....
	 */
	static function setStatus($solas_job_id, $status)
	{
		$settings = new Settings();
		header ("Content-Type:text/html; charset=utf-8");
		$request = new HTTP_Request2($settings->get('cnlf.url').'/set_status.php', HTTP_Request2::METHOD_GET);
		$request->setHeader('Accept-Charset', 'utf-8');
		$url = $request->getUrl();
		$url->setQueryVariable('com', 'LKR');         // set your component name here
		$url->setQueryVariable('id', $solas_job_id);         // set job id here
		$url->setQueryVariable('msg', $status);  // set status id here

		// This will get the server response 
		$response=$request->send()->getBody();
		$words = array('<response>', '</response>', '<msg>', '</msg>');
		$response = str_replace($words, '', $response);
		return ($response == 'Status Updated') ? true : $response;
	}
	
	static function sendFeedback($solas_job_id, $msg)
	{
		$settings = new Settings();
		header ("Content-Type:text/html; charset=utf-8");
		$request = new HTTP_Request2($settings->get('cnlf.url').'/send_feedback.php', HTTP_Request2::METHOD_GET);
		$request->setHeader('Accept-Charset', 'utf-8');
		$url = $request->getUrl();
		$url->setQueryVariable('com', 'LKR');         // set your component name here
		$url->setQueryVariable('id', $solas_job_id);  // set job id here
		// Set your component's feedback here:
		$url->setQueryVariable('msg', $msg);
		// This will get the server response 
		$response = $request->send()->getBody();
		$words = array('<response>', '</response>', '<msg>', '</msg>');
		$response = str_replace($words, '', $response);
		return ($response == 'Feedback Updated') ? true : $response;
	}
	
	static function sendOutput($solas_job_id, $data)
	{
		$ret = false;
		header ("Content-Type:text/xml");
		$settings = new Settings();
		$request = new HTTP_Request2($settings->get('cnlf.url').'/send_output.php');
		$request->setMethod(HTTP_Request2::METHOD_POST)
			->addPostParameter('id', $solas_job_id)
			->addPostParameter('com', 'LKR')
			->addPostParameter('data', $data);	
		try {
			// Send the file
			$response = $request->send();
			if (200 == $response->getStatus())
			{
				$words = array('<response>', '</response>', '<msg>', '</msg>');
				$response = str_replace($words, '', $response->getBody());
				if ($response == 'Output Accepted')
				{
					$ret = true;
				}
				else
				{
					//There was an error, display its details
					echo $response;
					die;
				}
			} else {
				echo 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
					 $response->getReasonPhrase();
				die;
			}
		} catch (HTTP_Request2_Exception $e) {
			echo 'Error: ' . $e->getMessage();
			die;
		}
		return $ret;
	}
}
