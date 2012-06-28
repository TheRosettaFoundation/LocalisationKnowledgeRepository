<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/

/*
 * This is a page that shows all job information.
 * Can be viewed by either the PM or the Author.
 * @author: David O Carroll
 */
require('./scripts/init.php');

$header = array('title' => 'LKR - Job Archive',
				'extra_scripts' => '<script type="text/javascript" src="/resources/js/jquery.jeditable.js"></script>
				 					<script type="text/javascript" src="/resources/js/editable.js"></script>'
);
Template::header($header);
?>

<h2>Job Archive</h2>
<p>The following is a list of all jobs available at the LKR.</p>
<?php
$visitor = $_GET['visitor'];
if($visitor == 'author')
{
	echo '<p>Back to <a href="/author/">Author Central</a> or <a href="/pm/">Log In As PM</a></p>';
}
elseif($visitor = 'pm')
{
	echo '<p>Back to <a href="/pm/">PM Central</a> or <a href="/author/">Log In As Author</a></p>';
}

echo '<ul>';
if($jobs = Job::getAllJobs())
{
	foreach($jobs as $job)
	{
		echo '<li><a href="/'.$visitor.'/view/'.$job->getJobID().'/">Job '.$job->getJobID().'</a> ';
		$job->html_status();
		echo '</li>';
	}
}

Template::footer();
