<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/

/*
 * Script used for changing values in a custom guideline
 * @author: David O Carroll
 */ 
require($_SERVER['DOCUMENT_ROOT'].'/scripts/init.php');

$stopword_id = intval(IO::get_val('stopword_id'));
$stopword = new Stopword($stopword_id);

// Give the page a title
$header = array('title' => 'LKR - Edit Custom Guideline');
Template::header($header);
?>
<h2>Edit Custom Guideline</h2>
	<p>Enter the new values for your custom guideline and then hit save.</p>
	<form action="/scripts/manage_stopword_list.php" method = "POST">
		<input type="hidden" name="stopword_id" value="<?php echo $stopword_id;?>">
		<p>Stopword: <input type="textarea" name="stopword" value="<?php echo $stopword->getStopword();?>"></p>
		<p>Title of Warning: <input type="textarea" name="title_of_warning" value="<?php echo $stopword->getTitleOfWarning();?>"></p>
		<p>Warning Description: <input type="textarea" name="warning_description" value="<?php echo $stopword->getWarningDescription();?>"></p>
	<br />
		<input type="submit" name="operation" value="Save">
		<input type="submit" name="operation" value="Cancel">
	</form>



<?php
Template::footer();