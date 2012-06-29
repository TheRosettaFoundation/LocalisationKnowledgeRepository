<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/

/*
 * This is the screen where the PM selects the export type
 * NOTE: Once a CNLF job has been sent back to the CNLF server it becomes a normal job
 * with all trace of the CNLF id removed in case it would attempt to update an old CNLF job
 * @author: David O Carroll
 */
require(__DIR__.'/../scripts/init.php');
$settings = new Settings();
$domain_root = $settings->path_to_domain_root($_SERVER);

$job_id = intval(IO::get_val('job_id'));
$header = array('title' => 'LKR - Job '.$job_id);
Template::header($header);
$job = new Job($job_id);
// if the comment has been set remember it for later
if(isset($_POST['comment']))
{
	$comment = $_POST['comment'];
}
?>
<h2>Export Job <?php echo $job_id; ?></h2><br />
<p>This file was maked as completed on <?php echo $job->getCompleteDate(); ?> and was closed on <?php echo $job->getClosedDate(); ?>.</p>
<p>To export this file as an XLIFF (XML Localisation Interchange File Format) click on the Save As XLIFF button below.</p><br />

<?php
echo '<form action="'.$domain_root.'/scripts/file.export.php?job_id='.$job->getJobID().'" method="post">';
?>
	<input type="submit" name="save_to_xliff" value="Save As XLIFF" />&nbsp;
	<input type="submit" name="save_to_txt" value="Save As txt" />&nbsp;
	<?php
	$CNLF_id = $job->getCNLFid();
	if($CNLF_id != null)
	{
		// pass the completed job to the cnlf server
		echo '<input type="submit" name="save_to_locconnect" value="Send To LocConnect" />&nbsp;';
	}
	?>
	<input type="submit" name="done" value="Done" />

<h3>Meta-Data</h3>
<p>Here is the meta data that will be exported with the xliff file.</p>
<?php
// Print the metadata with checkboxes for choosing what gets exported
echo '<p>';
	echo '<input type="checkbox" name="export_job_id" value="'.$job->getJobID().'" checked> Job ID: '.$job->getJobID();
echo '</p>';
if($CNLF_id != null)
{
	echo '<p>';
		echo '<input type="checkbox" name="CNLF_id" value="'.$CNLF_id.'" checked> LocConnect ID: '.$CNLF_id;
	echo '</p>';
}
echo '<p>';
	echo '<input type="checkbox" name="author_name" value="'.$job->getAuthorName().'" checked> Author Name: '.$job->getAuthorName();
echo '</p>';
echo '<p>';
	echo '<input type="checkbox" name="email_address" value="'.$job->getEmailAddress().'" checked> Email Address: '.$job->getEmailAddress();
echo '</p>';
?>
<p>
	<input type="checkbox" name="include_domain" checked="checked"> 
	Domain: <input type="text" name="domain" value="<?php echo $job->getDomain(); ?>">
</p>
<?php
echo '<p>';
	echo '<input type="checkbox" name="original" value="'.$job->getOriginalFile().'" checked> Original: '.$job->getOriginalFile();
echo '</p>';
echo '<p>';
	echo '<input type="checkbox" name="company_name" value="'.$job->getCompanyName().'" checked> Company Name: '.$job->getCompanyName();
echo '</p>';
echo '<p>';
	$wordCount = $job->getWordCount();
	echo '<input type="checkbox" name="word_count" value="'.$wordCount.'" checked> Word Count: '.$wordCount;
echo '</p>';
echo '<p>';
	$segmentCount = $job->getSegmentCount();
	echo '<input type="checkbox" name="segment_count" value="'.$segmentCount.'" checked> Segment Count: '.$segmentCount;
echo '</p>';
echo '<p>';
	$characterCount = $job->getCharacterCount();
	echo '<input type="checkbox" name="character_count" value="'.$characterCount.'" checked> Character Count: '.$characterCount;
echo '</p>';
echo '<p>';
	echo '<input type="checkbox" name="language_source" value="'.$job->getSourceLanguage().'" checked> Source language: '.$job->getSourceLanguage();
echo '</p>';
echo '<p>';
	echo '<input type="checkbox" name="target_language" value="'.$job->getTargetLanguage().'" checked> Target language: '.$job->getTargetLanguage();
echo '</p>';
echo '<p>';
	echo '<input type="checkbox" name="tool" value="Localisation Knowledge Repository" checked> Tool: Localisation Knowledge Repository';
echo '</p>';
echo '<p>';
	$comment_value = '';	
	if (isset($comment))
	{
		echo $comment_value = $comment;
	}
	echo '<input type="checkbox" name="comment" value="'.$comment_value.'" checked> Comment: '.$comment_value;
echo '</p>';
echo '</form>';

Template::footer();
