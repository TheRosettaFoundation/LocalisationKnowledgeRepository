<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/
 
/*
 * This is the AUTHOR's main hub
 * Here the author can import a job, select a job to work on, or review completed jobs
 * There is also a link to the job archive for a list of all jobs
 * @author: David O Carroll
 */
require($_SERVER['DOCUMENT_ROOT'].'/scripts/init.php');
$header = array('title' => 'LKR - Author Central');
Template::header($header);
?>

<h2>Author Central</h2>

<?php
// If a job was just completed display the information
$action = IO::get_val('action');
if($action == 'completed')
{
	echo '<div class=\'success\'>';
	$job_id = intval(IO::get_val('job_id'));
	echo '<p class="highlight">Job '.$job_id.' has now been marked as for review and has been sent to your project manager.</p>';
	echo '</div>';
}
?>

<a class="button" href="/author/import.solas.php">Import LocConnect Jobs</a>

<form action="/scripts/file.upload.php" method="post" enctype="multipart/form-data">
	<h3>Create a Job</h3>
	<p>
<?php
	echo '*Full Name:<br />';
	echo '<input name="full_name" type="textarea" value="'.$_COOKIE['author_name'].'"/>';
	if($action == 'invalid_name')//Full Name field is a mandatory field
	{
		echo '<div class="status"> Invalid Author Name</div>';
	}
	echo '<br>';
?>
	</p>
	<p>
		Email:<br /> <input name="email" type="textarea" value="<?php echo $_COOKIE['email_address'];?>"/><br>
	</p>
	<p>
		Domain:<br /> <input name="domain" type="textarea" value="<?php echo $_COOKIE['domain'];?>"/><br>
	</p>
	<p>
		Company/Product Name: <br /> <input name='company_name' type='textarea' value="<?php echo $_COOKIE['company_name']; ?>" /><br />
	</p>
	<p>1. Upload a .txt file:</p>
	<blockquote>
		<p>
			<input name="import_file" type="file"/><br>
		</p>
		<p>
			<input type="submit" name="operation" value="Import file"/>
		</p>
	</blockquote>
	<p>2. Or paste text directly:</p>
	<blockquote>
		<p>
			<textarea rows="10" cols="50" wrap="virtual" name="import_textarea"></textarea>
		</p>
		<p>
			<input type="submit" name="operation" value="Import text"/>
		</p>
	</blockquote>
</form>

<h3>Jobs In Progress</h3>
<?php
//display a list of all the jobs currently under inspection by the author
$recent_jobs = Job::getOpenJobs();
if ($recent_jobs)
{
	echo '<ul>'."\n";
	foreach($recent_jobs as $job)
	{
		echo '<li><a href="/author/view/'.$job->getJobID().'/analyse/">Job '.$job->getJobID().'</a>';
		$job->html_status();
		echo '</li>'."\n";
	}
	echo '</ul>'."\n";
} 
else 
{
	echo '<p>You have no jobs to complete.</p>';
}
?>

<h3>For Review</h3>
<?php
//Print the jobs that have been passed to the PM
$completedJobs = Job::getCompletedJobs();
if($completedJobs)
{
	echo '<ul>'."\n";
	foreach($completedJobs as $job)
	{?>
		<li><a href="/author/view/<?php echo $job->getJobID(); ?>/analyse/">Job
		<?php echo $job->getJobId().'</a>';
		if($job->isClosed())
		{
			$job->html_status();
		} 
		else 
		{
			$job->html_status();
		}
		echo '</li>'."\n";
	}
} 
else
{
	echo '<p>There are no jobs currently up for review</p>';
}
//link to the job archive which holds all the jobs from the DB
echo '<br /><p><a href="/archive/author/">Job Archive</a></p>';
Template::footer();
