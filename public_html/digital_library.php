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
$header = Array('title' => 'LKR - Digital Library');
Template::header($header);
?>
<h2>Digital Library</h2>
<p>The LKR Digital Library is a repository of guidelines for the development of usable, readable and translatable digital content</p>

<?php
Template::footer();
