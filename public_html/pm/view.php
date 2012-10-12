<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/

/*
 * This script allows a project manager to examine the author's work and
 * close and export a file or send it back to the author
 * @author: David O Carroll
 */

require(__DIR__.'/../scripts/init.php');

$settings = new Settings();
$domain_root = $settings->path_to_domain_root($_SERVER);

// Incoming parameters.
$job_id = intval(IO::get_val('job_id'));
$cnlf = intval(IO::get_val('cnlf'));
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

// Generate a new report
$report = new Report($job);
if(!$job->isClosed())
{
	$report->printTooltips();
}
?>
<h2>Job <?php echo $job->getJobID(); ?></h2>
<?php
// Display the status bar
$job->printPMStatusBar($report, $cnlf);
if ($job)
{
?>
	<div id="segments">
		<table class="segments">
			<col id="col_segment" />
			<col id="col_original" />
			<col id="col_editable" />
			<col id="col_comment" />
			<thead>
				<tr>
					<th id="segment_nb">&nbsp;</th>
					<th>Original</th>
					<th>Edited</th>
					<th>Comments</th>
				</tr>
			</thead>
			<?php
				if ($segments = $job->getSegments())
				{
					foreach($segments as $segment)
					{
						// Check if there is any text in the segment
						if($segment->getSource() != '' || trim($segment->getTargetRaw()) != '')
						{
							//if there is print it, otherwise ignore the segment altogether
							echo '<tr>';
								// Print the segment id
								echo '<td class="segment_nb">'.$segment->getSegmentID().'</td>';		
								echo '<td>';
									echo $segment->getSourceParsed();
								echo '</td>';
								//if it has a warning highlight it, if it has been edited mark it
								echo '<td class="'.((!$job->isClosed() && $segment->hasWarning()) ? ' highlight' : '').($segment->isEdited() ? ' edited' : '').'">';
									echo '<div id="tooltip_'.$segment->getSegmentID().'">';
										echo $segment->getTargetRaw();
									echo '</div>';
								echo '</td>';
								echo '<td>';
									$comment = $segment->getComment();
                                    if(!$segment->isTranslatable()) {
                                        echo "This segment has been marked as untranslatable";
                                    }
									else if(!$job->isClosed())
									{
										// Job is open, allow the PM to add a comment
										echo '<div class="edit_area" id="'.$job->getJobID().'_'.$segment->getSegmentID().'_comment">';
											if($comment != NULL)
											{
												echo $comment;
												echo '</div>';
											} 
											else 
											{
												echo '</div>';
												echo '<button ';
												echo 'onclick="javascript:editComment('.$job->getJobID().', '.$segment->getSegmentID().')">';
												echo 'Add Comment</button>';
											}
									} 
									else 
									{
										//Job is closed don't allow the PM to add a comment
										if($comment != null)
										{
											echo $comment;
										}
									}
								echo '</td>';
							echo '</tr>';
						}
					}
				}
			?>
		</table>
	</div>
	<br />
<?php	
$job->printPMStatusBar($report, $cnlf);
echo "<br />";
$job->printLegend();
}
Template::footer();
?>
