<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/
/*
 * This is a page that is used to choose who the user logs in as first
 * @author: David O Carroll
 */
 
REQUIRE('./scripts/init.php');
$header = Array('title' => 'LKR - Login');
Template::header($header);
$settings = new Settings();
?>
<h3>Author</h3>
<p>The role of the Author is to upload content to the LKR, and check its usability, readability and translatability according to the guidelines defined by the Project Manager. Authors navigate to text strings conflicting with the pre-defined guidelines, edit these strings to rectify any issues identified, and send the updated content to the Project Manager for review.</p>
<?php
echo '<a href="'.$settings->path_to_domain_root($_SERVER).'/author/">Log in as author</a>';
?>

<h3>Project Manager</h3>
<p>The role of the Project Manager (PM) is to define which content development guidelines will be used to check the usability, readability and translatability of content uploaded to the LKR. PMs review edits made by Authors, and leave comments for them if necessary. When the LKR quality checks are complete, PMs export the content as either an edited version of the original file format, or as a monolingual XLIFF file with project metadata.</p>
<?php
echo '<a href="'.$settings->path_to_domain_root($_SERVER).'/pm/">Log in as PM</a>';
?>

<?php
Template::footer();
