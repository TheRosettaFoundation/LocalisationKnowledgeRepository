/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/
/*
 * A function used to confirm if the PM would really like to delete a custom guideline
 * @author: David O Carroll
 */
function confirmDelete(stopword_id)
{
	var answer = confirm('Delete this guideline?');
	if(answer)
	{
		window.location="/scripts/delete_stopword.php?stopword_id=" + stopword_id;
	}
}