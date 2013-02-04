<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/

/*
 * Here the author edits the job in adherence to the guidelines attached to each segment
 * @author: David O Carroll
 */
require(__DIR__.'/../scripts/init.php');
$settings = new Settings();
$domain_root = $settings->path_to_domain_root($_SERVER);

// Incoming parameters.
$job_id = intval(IO::get_val('job_id'));
$analyse = (intval(IO::get_val('analyse')) == 1) ? 1 : false;
$job = new Job($job_id);
$header = array('title' => 'LKR - Job '.$job_id,
				'extra_scripts' => '<script type="text/javascript" src="'.$domain_root.'/resources/js/jquery.jeditable.js"></script>
				 					<script type="text/javascript" src="'.$domain_root.'/resources/js/editable.js"></script>',
                'head-extra' => '<script type="text/javascript"> 
                                    $(document).ready( function() { 
                                        startEditable("'.$domain_root.'"); 
                                    });
                                </script>'
);

Template::header($header);
/* Run an analysis on this job. */
if ($analyse)
{
	if(!$job->isComplete()) //dont show tooltips if job is complete
	{
		$report = new Report($job);
		$report->printTooltips(); // set the rollover warnings.
	}
}
?>
<h2>Job <?php echo $job->getJobID(); ?></h2>
<?php
// Print the number of warnings and the actions that can be taken
$job->printAuthorStatusBar($report);
if ($job)
{			//print the table for editing the job
	?>
	<div id="segments">
		<table class="segments">
			<thead>
				<col id="col_segment" />
				<col id="col_original" />
				<col id="col_editable" />
				<col id="col_comment" />
				<tr>
					<th id="segment_nb">&nbsp;</th>
					<th>Original</th>
					<th>Editable
					<?php 
					// if the job is complete don't show the analyse button
					if(!$job->isComplete())
					{
						echo '<form action="'.$domain_root.'/author/view/'.$job_id.'/analyse/" method="get">';?>
							    <input type="submit" value="<?php if ($analyse) echo 'Re-analyse'; else echo 'Analyse';?>" />
							</form>
					<?php
					} ?>
					</th>
					<th>Comments</th>
				</tr>
			</thead>
			<?php
				//for each sentence in the file...
				if ($segments = $job->getSegments())
				{
                    $currentFileId = 0;
					foreach($segments as $segment)
					{
						//if its not a blank line
						if($segment->getSource() != '' || trim($segment->getTargetRaw()) != '')
						{
                            if ($segment->getFileID() != $currentFileId) {
                                $currentFileId = $segment->getFileID();
                                echo "<tr>";
                                echo "<td></td><td></td>";
                                echo "<td>File #$currentFileId</td>";
                                echo "</tr>";
                            }
							echo '<tr id=seg_'.$segment->getSegmentID().'>';
								//print the segment id and mark it so it can be linked from the status bar
								echo '<td class="segment_nb"><a name="seg_'.$segment->getSegmentID().'">'.$segment->getSegmentID().'</a></td>';
								//print the original text
								echo '<td class="segment_original">';
									echo $segment->getSourceParsed();
								echo '</td>';
								//print the editable text, only highlight it if there is a warning and it is not complete
								echo '<td class="segment_editable '.(($segment->hasWarning() && !$job->isComplete()) ? ' highlight' : '').($segment->isEdited() ? ' edited' : '').'">';
									//if the job is complete dont allow it to be edited
									if($job->isComplete() || !($segment->isTranslatable()))
									{
										echo '<div id="tooltip_'.$segment->getSegmentID().'" class="no-translate">';
											echo $segment->getTargetRaw();
										echo '</div>';
									} 
									else 
									{
										echo '<div id="tooltip_'.$segment->getSegmentID().'">';
											echo '<div class="edit_area" id="'.$job->getJobID().'_'.$segment->getSegmentID().'">';
												echo $segment->getTargetRaw();
											echo '</div>';
										echo '</div>';
										//if it has been altered or has a warning display a reanalyse button
										if($segment->isEdited() || $segment->hasWarning())
										{
											echo '<form action="'.$domain_root.'/scripts/redirect.php?job_id='.$job_id.'&seg_id='.$segment->getSegmentID().'" method="post">';
												echo '<button type="submit">';
													if ($analyse) echo 'Re-analyse'; else echo 'Analyse';
												echo '</button>';
											echo '</form>';
										}
									}
								echo '</td>';
								//print the PM's comments
								echo '<td>';
									$comment = $segment->getComment();
									if($comment != NULL)
									{
										echo $comment;
									}
                                    if(!$segment->isTranslatable()) {
                                        echo "NOTE: this segment has been marked as untranslatable";
                                    }
								echo '</td>';
							echo '</tr>';
						}
					}
					if(!$job->isComplete())
					{
						// Show re-analyse button.
						echo '<tr>';
							echo '<td></td>';
							echo '<td></td>';
							echo '<td><form action="'.$domain_root.'/author/view/'.$job_id.'/analyse/" method="get">
											<input type="submit" value="Re-analyse" />
										</form>
								  </td>';
							echo '<td></td>';
						echo '</tr>';
					}
				}
			?>
		</table>
	</div>
<p>
	<?php
	// reShow this at the bottom of the table for usability
	$job->printAuthorStatusBar($report);
    echo "<br />";
    $job->printLegend();
echo '</p>';
}
Template::footer();
