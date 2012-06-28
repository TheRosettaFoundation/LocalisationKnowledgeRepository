/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/

/*
 * This is a script for checking/unchecking all checkboxes
 * It is used in manage_guideline_settings.php and import.solas.php
 * @author: David O Carroll
 */

function checkAll()
{
	var elements = document.forms[0].elements;
	for(var i = 0 ; i < elements.length ; i++)
	{
		if(elements[i].type == "checkbox")
		{
			elements[i].checked = true;
		}
	}
}

function checkNone()
{
	var elements = document.forms[0].elements;
	for(var i = 0 ; i < elements.length ; i++)
	{
		if(elements[i].type == "checkbox")
		{
			elements[i].checked = false;
		}
	}
}
