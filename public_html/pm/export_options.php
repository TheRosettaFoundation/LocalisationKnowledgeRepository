<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/

/*
 * Script used for adding a comment to a job before exporting
 * @author: David O Carroll
 */
require(__DIR__.'/../scripts/init.php');

//Add a title to the page
$header = array('title' => 'LKR - Export Options');
Template::header($header);
$settings = new Settings();
$domain_root = $settings->path_to_domain_root($_SERVER);
$job_id = intval(IO::get_val('job_id'));
$job = new Job($job_id);
?>
<h2>Job <?php echo $job->getJobID(); ?>: Export Options</h2>
<p>In this area you can add a comment to the job which will be saved in the comments section of the xliff file you export.</p>
<?php
echo '<p><a class="button" href="'.$domain_root.'/pm/export/'.$job->getJobID().'/">Export without comment</a></p>';

echo '<form action="'.$domain_root.'/pm/export/'.$job->getJobID().'/" method="POST" enctype="multipart/form-data">';
?>
	<textarea rows="15" cols="50" wrap="virtual" name="comment"></textarea>
	<p>
		<br /><input type="submit" value="Add Comment and Export"/>
	</p>
</form>

<?php

Template::footer();
