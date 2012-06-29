<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/
/*
 * This is used to turn on/off guideline settings
 * @author: David O Carroll
 */
require($_SERVER['DOCUMENT_ROOT'].'/scripts/init.php');

if(isset($_POST['submit']))
{
	//Navigated here from the form
	$CNLF_id = IO::post_val('CNLF_id');
	//get the value of the submit to decide what action to undertake
	$operation = $_POST['submit'];
	if($operation == 'Save')
	{
		if($CNLF_id == '')
		{
			// It isn't a CNLF job
			if($reports = Report::getAllReports())
			{
				foreach($reports as $report)
				{
					$name = 'report_'.$report['report_id'];
					// if this report was checked
					if(isset($_POST[$name]))
					{
						// Enable the rep[ort
						Report::enableReport($report['report_id']);
					}
					else
					{
						//disable the report
						Report::disableReport($report['report_id']);
					}
				}
			}
			if($stopwords = Report::getAllStopwords())
			{
				foreach($stopwords as $stopword)
				{
					if(isset($_POST['stopword_'.$stopword->getStopwordID()]))
					{
						$stopword->enable();
					}
					else
					{
						$stopword->disable();
					}
				}
			}
		}
		else
		{
			// It is a CNLF job
			$sql = new MySQLHandler();
			$sql->init();
			//reset the current gidelines
			$q = 'DELETE FROM cnlf_guidelines
					WHERE CNLF_id = "'.$CNLF_id.'"';
			$sql->Delete($q);
			if($reports = Report::getAllReports())
			{
				foreach($reports as $report)
				{
					$name = 'report_'.$report['report_id'];
					if(IO::post_val($name))
					{
						//it was checked so add it to the database
						$q = 'INSERT INTO cnlf_guidelines (CNLF_id, machine_name, type)
									VALUES ("'.$CNLF_id.'", "'.$report['machine_name'].'", "report")';
						$sql->Insert($q);
					}
				}
			}
			if($stopwords = Report::getAllStopwords())
			{
				foreach($stopwords as $stopword)
				{
					if(IO::post_val('stopword_'.$stopword->getStopwordID()))
					{
						$q = 'INSERT INTO cnlf_guidelines (CNLF_id, machine_name, type)
									VALUES ("'.$CNLF_id.'", "'.$stopword->getStopword().'", "stopword")';
						$sql->Insert($q);
					}
				}
			}
		}						
	}
}
//this happens whether operation = Save or Cancel so there is no need for an else
if($CNLF_id == '')
{
	//Return to PM central
	header('Location: /pm/' ) ;
}
else
{
	//Return To the CNLF server
	header('Location: http://10.100.13.155/locConnect/');
}
die;