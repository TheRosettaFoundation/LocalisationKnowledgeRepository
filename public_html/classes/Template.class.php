<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/

/*
 * Output header, footer.
 */
class Template 
{
	/*
	 * Output the header
	 */
	static function header($params = null)
	{
		include (__DIR__.'/../includes/header.php');
	}
	
	/*
	 * Output the footer
	 */
	static function footer()
	{
		include (__DIR__.'/../includes/footer.php');
	}
}
