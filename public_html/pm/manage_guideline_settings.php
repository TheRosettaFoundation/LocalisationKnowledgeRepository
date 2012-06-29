<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/

/*
 * This script allows the PM to check/uncheck his/her desired guidelines
 * NOTE: CNLF jobs have their guidelines stored in the database, all other jobs share guidelines
 * @author: David O Carroll
 */
require(__DIR__.'/../scripts/init.php');

$settings = new Settings();
$domain_root = $settings->path_to_domain_root($_SERVER);
 
$header = array('title' => 'LKR - Manage Guideline Settings',
		'extra_scripts' => '<script language="JavaScript" src="'.$domain_root.'/resources/js/confirmDelete.js"></script>
							<script language="JavaScript" src="'.$domain_root.'/resources/js/check_uncheck.js"></script>'
);
Template::header($header);
$stopwords = Report::getAllStopwords();
$reports = Report::getAllReports();
$CNLF_id = IO::get_val('CNLF_id');
echo '<h2>Guideline Settings</h2>';
echo '<div id="cBoxes">';
echo '<form name="form1" method="POST" action="'.$domain_root.'/scripts/change_guideline_settings.php">';
echo '<h3>Lexicology Guidelines</h3>';
$guideline_applicable = false;		//Checks if a guideline appeared in the list
if($reports)
{
	foreach($reports as $report)
	{
		if($report['guideline_type'] == 'Lexicology')
		{
			$guideline_applicable = true;
			$checkbox = 'unchecked';
			if(Report::isEnabled($report['report_id']))
			{
				// The current guideline is enabled
				$checkbox = 'checked';
			}
			echo '<input type="checkbox" name="report_'.$report['report_id'].'"';
			echo 'value="'.$report['name'].'" '.$checkbox.'>&nbsp;&nbsp;&nbsp;'.$report['name'].'<br />' ;
		}
	}
}
// do the same for the stopwords
if($stopwords)
{
	foreach($stopwords as $stopword)
	{
		if($stopword->getGuidelineType() == 'Lexicology')
		{
			$guideline_applicable = true;
			$checkbox = 'unchecked';
			if($stopword->isEnabled())
			{
				$checkbox = 'checked';
			}
			echo '<input type="checkbox" name="stopword_'.$stopword->getStopwordID().'"';
			echo 'value="'.$stopword->getTitleOfWarning().'" '.$checkbox.'>&nbsp;&nbsp;&nbsp;'.$stopword->getTitleOfWarning().'<br />' ;
		}
	}
}
if(!$guideline_applicable)
{
	echo '<p>No Lexicology Guidelines Available</p>';
}
echo '<h3>Orthography Guidelines</h3>';
$guideline_applicable = false;
if($reports)
{
	foreach($reports as $report)
	{
		if($report['guideline_type'] == 'Orthography')
		{
			$guideline_applicable = true;
			$checkbox = 'unchecked';
			if(Report::isEnabled($report['report_id']))
			{
				$checkbox = 'checked';
			}
			echo '<input type="checkbox" name="report_'.$report['report_id'].'"';
			echo 'value="'.$report['name'].'" '.$checkbox.'>&nbsp;&nbsp;&nbsp;'.$report['name'].'<br />' ;
		}
	}
}
if($stopwords)
{
	foreach($stopwords as $stopword)
	{
		if($stopword->getGuidelineType() == 'Orthography')
		{
			$guideline_applicable = true;
			$checkbox = 'unchecked';
			if($stopword->isEnabled())
			{
				$checkbox = 'checked';
			}
			echo '<input type="checkbox" name="stopword_'.$stopword->getStopwordID().'"';
			echo 'value="'.$stopword->getTitleOfWarning().'" '.$checkbox.'>&nbsp;&nbsp;&nbsp;'.$stopword->getTitleOfWarning().'<br />' ;
		}
	}
}
if(!$guideline_applicable)
{
	echo '<p>No Orthography Guidelines Available</p>';
}

echo '<h3>Morphology Guidelines</h3>';
$guideline_applicable = false;
if($reports)
{
	foreach($reports as $report)
	{
		if($report['guideline_type'] == 'Morphology')
		{
			$guideline_applicable = true;
			$checkbox = 'unchecked';
			if(Report::isEnabled($report['report_id']))
			{
				$checkbox = 'checked';
			}
			echo '<input type="checkbox" name="report_'.$report['report_id'].'"';
			echo 'value="'.$report['name'].'" '.$checkbox.'>&nbsp;&nbsp;&nbsp;'.$report['name'].'<br />' ;
		}
	}
}
if($stopwords)
{
	foreach($stopwords as $stopword)
	{
		if($stopword->getGuidelineType() == 'Morphology')
		{
			$guideline_applicable = true;
			$checkbox = 'unchecked';
			if($stopword->isEnabled())
			{
				$checkbox = 'checked';
			}
			echo '<input type="checkbox" name="stopword_'.$stopword->getStopwordID().'"';
			echo 'value="'.$stopword->getTitleOfWarning().'" '.$checkbox.'>&nbsp;&nbsp;&nbsp;'.$stopword->getTitleOfWarning().'<br />' ;
		}
	}
}
if(!$guideline_applicable)
{
	echo '<p>No Morphology Guidelines Available</p>';
}
echo '<h3>Syntax Guidelines</h3>';
$guideline_applicable = false;
if($reports)
{
	foreach($reports as $report)
	{
		if($report['guideline_type'] == 'Syntax')
		{
			$guideline_applicable = true;
			$checkbox = 'unchecked';
			if(Report::isEnabled($report['report_id']))
			{
				$checkbox = 'checked';
			}
			echo '<input type="checkbox" name="report_'.$report['report_id'].'"';
			echo 'value="'.$report['name'].'" '.$checkbox.'>&nbsp;&nbsp;&nbsp;'.$report['name'].'<br />' ;
		}
	}
}
if($stopwords)
{
	foreach($stopwords as $stopword)
	{
		if($stopword->getGuidelineType() == 'Syntax')
		{
			$guideline_applicable = true;
			$checkbox = 'unchecked';
			if($stopword->isEnabled())
			{
				$checkbox = 'checked';
			}
			echo '<input type="checkbox" name="stopword_'.$stopword->getStopwordID().'"';
			echo 'value="'.$stopword->getTitleOfWarning().'" '.$checkbox.'>&nbsp;&nbsp;&nbsp;'.$stopword->getTitleOfWarning().'<br />' ;
		}
	}
}
if(!$guideline_applicable)
{
	echo '<p>No Syntax Guidelines Available</p>';
}
echo '<h3>Text Structure Guidelines</h3>';
$guideline_applicable = false;
if($reports)
{
	foreach($reports as $report)
	{
		if($report['guideline_type'] == 'Text Structure')
		{
			$guideline_applicable = true;
			$checkbox = 'unchecked';
			if(Report::isEnabled($report['report_id']))
			{
				$checkbox = 'checked';
			}
			$name = 'report_'.$report['report_id'];
			echo '<input type="checkbox" name="'.$name.'"';
			echo 'value="'.$report['name'].'" '.$checkbox.'>&nbsp;&nbsp;&nbsp;'.$report['name'].'<br />' ;
		}
	}
}
if($stopwords)
{
	foreach($stopwords as $stopword)
	{
		if($stopword->getGuidelineType() == 'Text Structure')
		{
			$guideline_applicable = true;
			$checkbox = 'unchecked';
			if($stopword->isEnabled())
			{
				$checkbox = 'checked';
			}
			echo '<input type="checkbox" name="stopword_'.$stopword->getStopwordID().'"';
			echo 'value="'.$stopword->getTitleOfWarning().'" '.$checkbox.'>&nbsp;&nbsp;&nbsp;'.$stopword->getTitleOfWarning().'<br />' ;
		}
	}
}
if(!$guideline_applicable)
{
	echo '<p>No Text Structure Guidelines Available</p>';
}
echo '<h3>Custom Guidelines</h3>';
$guideline_applicable = false;
if($stopwords)
{
	foreach($stopwords as $stopword)
	{
		if($stopword->getGuidelineType() == 'Custom')
		{
			$guideline_applicable = true;
			$checkbox = 'unchecked';
			if($stopword->isEnabled())
			{
				$checkbox = 'checked';
			}
			$word = $stopword->getStopword();
			echo '<input type="checkbox" name="stopword_'.$stopword->getStopwordID().'" value="'.$word.'" '.$checkbox.'>';
			echo '&nbsp;&nbsp;&nbsp;Do not use "'.$word.'"';
			echo '&nbsp;<a href="'.$domain_root.'/pm/edit_stopword.php?stopword_id='.$word.'">edit</a>&nbsp;';
			echo '<a href="javascript:confirmDelete('.$stopword->getStopwordID().')">delete</a>';
			echo '<br />' ;
		}
	}
}
if(!$guideline_applicable)
{
	echo '<p>No custom guidelines available</p>';
}
echo '<br /><input type="button" value="Select All" onClick="javascript:checkAll();" />';
echo '<input type="button" value="Deselect All" onClick="javascript:checkNone();" />';
echo '<br /><br /><input type="submit" name="submit" value="Save">';
echo '&nbsp;&nbsp<input type="submit" name="submit" value="Cancel">';
// Pass the CNLF id as a hidden variable
echo '<input type="hidden" name="CNLF_id" value="'.$CNLF_id.'" />';
echo '</form>';
?>

<h3>Add Custom Guidelines</h3>
<br />
<?php
echo '<form action="'.$domain_root.'/scripts/manage_stopword_list.php" method = "POST">';
?>
	<p>Stopword: <input type="textarea" name="stopword"></p>
	<p>Title of Warning: <input type="textarea" name="title_of_warning" value="Custom Guideline"></p>
	<p>Warning Description: <input type="textarea" name="warning_description" value="A warning goes here."></p>
<br />
	<input type="submit" name="operation" value="Add">
</form>

<?php
Template::footer();
