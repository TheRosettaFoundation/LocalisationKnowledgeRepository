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
require(__DIR__.'/scripts/init.php');

$settings = new Settings();

$header = array('title' => 'LKR - Job Archive',
				'extra_scripts' => '<script type="text/javascript" src="'.$settings->path_to_domain_root($_SERVER).'/resources/js/jquery.jeditable.js"></script>
				 					<script type="text/javascript" src="'.$settings->path_to_domain_root($_SERVER).'/resources/js/editable.js"></script>'
);
Template::header($header);
?>

<h2>Job Archive</h2>
<p>The following is a list of all jobs available at the LKR.</p>
<?php
$visitor = $_GET['visitor'];
if($visitor == 'author')
{
	echo '<p>Back to <a href="'.$settings->path_to_domain_root($_SERVER).'/author/">Author Central</a> or ';
    echo '<a href="'.$settings->path_to_domain_root($_SERVER).'/pm/">Log In As PM</a></p>';
}
elseif($visitor = 'pm')
{
	echo '<p>Back to <a href="'.$settings->path_to_domain_root($_SERVER).'/pm/">PM Central</a>';
    echo ' or <a href="'.$settings->path_to_domain_root($_SERVER).'/author/">Log In As Author</a></p>';
}

echo '<ul>';
if($jobs = Job::getAllJobs())
{
	foreach($jobs as $job)
	{
		echo '<li><a href="'.$settings->path_to_domain_root($_SERVER).'/'.$visitor.'/view/'.$job->getJobID().'/">Job '.$job->getJobID().'</a> ';
		$job->html_status();
		echo '</li>';
	}
}

Template::footer();
