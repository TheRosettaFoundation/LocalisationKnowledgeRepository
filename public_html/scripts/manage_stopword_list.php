<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/
/*
 * Script used to add new stopwords
 * @author: David O Carroll
 */
require(__DIR__.'/init.php');

$settings = new Settings();
$domain_root = $settings->path_to_domain_root($_SERVER);

// If the form was used
if(isset($_POST['operation']))
{
	$operation = $_POST['operation'];
	if($operation == 'Add')
	{
		// Get the details entered by the user and add them to the database
		if(isset($_POST['stopword']) && $_POST['stopword'] != '')
		{
			// A stopword must be entered
			$newStopword = $_POST['stopword'];
			$title = $_POST['title_of_warning'];
			$description = $_POST['warning_description'];
			if($stopwords = Report::getAllStopwords())
			{
				foreach($stopwords as $stopword)
				{
					if($stopword->getStopword() == $newStopword)
					{
						//Already exists so return to the previous page
						header('Location: /pm/configuration/guidelines/');
						die;
					}
				}
			}
			$sql = new MySQLHandler();
			$sql->init();
			$q = 'INSERT INTO stopwords (stopword, title_of_warning, warning_description)
						VALUES ("'.$newStopword.'", "'.$title.'", "'.$description.'")';
			$sql->Insert($q);
		}
		else
		{
			// No stopword entered
			echo '<p>ERROR: you must enter a stopword before clicking the Add button</p>';
		}
	}
	elseif($operation == 'Save')
	{
		$stopword_id = $_POST['stopword_id'];
		$stopword = new Stopword($stopword_id);
		$newStopword = $_POST['stopword'];
		$title = $_POST['title_of_warning'];
		$description = $_POST['warning_description'];
		if($newStopword != '')
		{
			if($stopwords = Report::getAllStopwords())
			{
				foreach($stopwords as $stopword)
				{
					if($stopword->getStopword() == $newStopword)
					{
						header('Location: /pm/configuration/guidelines/');
						die;
					}
				}
			}
			$sql = new MySQLHandler();
			$sql->init();
			$q = 'UPDATE stopwords
					SET stopword = "'.$newStopword.'",
						title_of_warning = "'.$title.'",
						warning_description = "'.$description.'"
					WHERE stopword_id = '.$stopword_id;
			$sql->Update($q);
		}
		else
		{
			echo '<p>ERROR: you must enter a stopword before clicking the Save button</p>';
		}
	}
	elseif($operation == 'Cancel')
	{
		header('Location: '.$domain_root.'/pm/configuration/guidelines/');
		die;
	}
	else
	{
		echo 'Unknown operation';
	}
}
// Go back a page
header('Location: '.$domain_root.'/pm/configuration/guidelines/');
die;
