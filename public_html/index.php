<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/

/*
 * This is the home page of the LKR
 * @author: David O Carroll
 */

require(__DIR__.'/scripts/init.php');
$header = Array('title' => 'LKR');
Template::header($header);
$settings = new Settings();
?>

<p>The Localisation Knowledge Repository (LKR) is a pre-translation quality assurance system designed to facilitate the development of usable, readable and translatable source language digital content.</p>
<p>The LKR operates as a web application enabling authors and project managers to improve the quality of source language content, in order to reduce the time and resources required to localise it for international audiences.</p>

<h3><a href="<?php echo $settings->path_to_domain_root($_SERVER) ?>/login.php">SOLAS Area</a></h3> 
<p>Upload a text file, segment into a string list, run a series of quality checks, rectify any errors identified, extract an edited text file, and generate an XLIFF-based project file.</p>

<h3><a href="<?php echo $settings->path_to_domain_root($_SERVER) ?>/digital_library.php">Digital Library</a></h3>
<p>Access content development guidelines designed to facilitate the production of usable, readable and translatable content for international audiences.</p>

<h3><a href="<?php echo $settings->path_to_domain_root($_SERVER) ?>/virtual_community.php">Virtual Community</a></h3>
<p>Share knowledge and resources, and connect to other LKR users.</p>
<p>
     <a href="mailto:lorcan.ryan@ul.ie">Contact Us</a><br />
</p>
<?php
Template::footer();
