<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/

/*
 * Called through jEditable AJAX call. Echos the response.
 * @author: David O Carroll
 */

require(__DIR__.'/init.php');

/*
 * Input param $job_segment arrives in the form of "jobid_segmentid". This was the easiest way to submit
 * job_id data through jEditable calls. Below the parameter is distilled to get the two different values.
 */
$job_segment = IO::post_val('id');
$text = IO::post_val('value');
$job_id = false;
$segment_id = false;

if ($job_segment)
{
	// Distill the input parameter to get job_id and segment_id value.
	$arr = explode('_', $job_segment);
	$job_id = $arr[0];
	$segment_id = $arr[1];
	$comment = $arr[2];
}
$job = new Job($job_id);
if($comment == 'comment')
{
	if($job->updateComment($segment_id, $text))
	{
		echo $text;
	}
	else
	{
		echo "Debug: save was unsuccessful.<br />(P.S. we're tracking down the developer right now. He's tagged, don't worry too much about it. Oh wait, he's behind you.)";
	}
}
else
{
	if ($job->updateSegment($segment_id, $text))
	{
		 echo $text; // This works without outputting as JSON.
	}
	else
	{
		echo "Debug: save was unsuccessful.<br />(P.S. we're tracking down the developer right now. He's tagged, don't worry too much about it. Oh wait, he's behind you.)";
	}
}
