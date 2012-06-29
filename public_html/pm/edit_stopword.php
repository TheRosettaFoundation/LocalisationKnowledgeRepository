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
require(__DIR__.'/../scripts/init.php');

$stopword_id = intval(IO::get_val('stopword_id'));
$stopword = new Stopword($stopword_id);

// Give the page a title
$header = array('title' => 'LKR - Edit Custom Guideline');
Template::header($header);
$settings = new Settings();
$domain_root = $settings->path_to_domain_root($_SERVER);
?>
<h2>Edit Custom Guideline</h2>
	<p>Enter the new values for your custom guideline and then hit save.</p>
    <?php
	echo '<form action="'.$domain_root.'/scripts/manage_stopword_list.php" method = "POST">';
		echo '<input type="hidden" name="stopword_id" value="'.$stopword_id.'">';
		echo '<p>Stopword: <input type="textarea" name="stopword" value="'.$stopword->getStopword().'"></p>';
   		echo '<p>Title of Warning: <input type="textarea" name="title_of_warning" value="'.$stopword->getTitleOfWarning().'"></p>';
		echo '<p>Warning Description: <input type="textarea" name="warning_description" value="'.$stopword->getWarningDescription().'"></p>';
    ?>
	<br />
		<input type="submit" name="operation" value="Save">
		<input type="submit" name="operation" value="Cancel">
	</form>



<?php
Template::footer();
