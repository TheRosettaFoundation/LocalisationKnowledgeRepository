<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/
/*
 * A script that runs a report before returning the user to the segment he/she was working on
 * @author: David O Carroll
 */

require($_SERVER['DOCUMENT_ROOT'].'/scripts/init.php');

// get incomming parameters
$job_id = intval(IO::get_val('job_id'));
$seg_id = intval(IO::get_val('seg_id'));
$job = new Job($job_id);
//Run the report
$report = new Report($job);

header('Location: /author/view/'.$job_id.'/analyse/#seg_'.$seg_id);
die;