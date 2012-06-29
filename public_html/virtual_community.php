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
$header = Array('title' => 'LKR - Virtual Community');
Template::header($header);
?>
<h2>Virtual Community</h2>
<p>The LKR Virtual Community is an area where authors may connect to share content development assets, ideas and resources.</p>

<?php
Template::footer();
