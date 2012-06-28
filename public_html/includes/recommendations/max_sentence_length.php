<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/
 /*
 * Provides information for the tooltips that get printed to the screen on hover over a
 * segment with a warning
 * @author: David O Carroll
 */
?>
<h3>Long sentence</h3>
<p>Current word length: <?php echo $params['length']; ?></p>
<p>Sentences should not be longer than <?php echo $params['max_length']; ?> words for descriptive text.
Consider omitting unnecessary words, or splitting into two or more shorter sentences.</p>