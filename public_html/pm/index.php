<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/

/*
 * This script allows the PROJECT MANAGER to select completed jobs to review or closed jobs
 * @author: David O Carroll
 */
require(__DIR__.'/../scripts/init.php');

$header = array('title' => 'LKR - Project Manager');
Template::header($header);

$settings = new Settings();
$domain_root = $settings->path_to_domain_root($_SERVER);

echo '<h2>Project Manager Central</h2>';
$action = IO::get_val('action');
if($action == "sentback")
{
	//a job was recently sent back for more work
	$job_id = IO::get_val('job_id');
	echo '<p class="highlight">Job '.$job_id.' has been sent back to the author for improvement.</p>';
}
?>
<h3>For Review</h3>
<?php
// List of jobs that the PM must work on
$completedJobs = Job::getCompletedJobs();
$noCompletedJobs = true;
if($completedJobs)
{
	echo '<ul>'."\n";
	foreach($completedJobs as $job)
	{
		if(!$job->isClosed())
		{
			echo '<li><a href="'.$domain_root.'/pm/view/'.$job->getJobID().'/">Job';
			echo $job->getJobId().'</a>';
			$job->html_status();
			echo '</li>'."\n";
			$noCompletedJobs = false;
		 }
	}
	echo '</ul>';
}
if($noCompletedJobs)
{
	echo '<p>No jobs currently to review.</p>';
}
echo '<br /><h3>Previously Completed Jobs</h3>';
// List of jobs the PM has finished
$closedJobs = Job::getClosedJobs();
if($closedJobs)
{
	echo '<ul>'."\n";
	foreach($closedJobs as $job)
	{
		echo '<li><a href="'.$domain_root.'/pm/view/'.$job->getJobID().'/">Job';
		echo $job->getJobId().'</a>';
		$job->html_status();
		echo '</li>'."\n";
	}
	echo '</ul>';
}
else
{
	echo '<p>There are no completed jobs available.</p>';
}

// Link to the job archive
echo '<br /><p><a href="'.$domain_root.'/archive/pm/">Job Archive</a></p>';

// Link to guideline configuration page
echo '<br /><br /><p><a href="'.$domain_root.'/pm/configuration/guidelines/">Change your guideline settings</a></p>';

Template::footer();
