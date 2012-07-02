/* jEditable jQuery plugin
 * http://www.appelsiini.net/projects/jeditable
 */
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/
function startEditable(domain_root) {
	// Setup editable divs. Any div with class .edit_area will be editable.
	$('.edit_area').editable(domain_root+'/scripts/save_segment.php', {
		type      : 'textarea',
		cancel    : 'Cancel',
		submit    : 'OK',
		placeholder: '',
		indicator : '<img src="'+domain_root+'/img/indicator.gif">'
	});

	// Highlight the currently selected segment based on the anchor.
	// E.g. if url ends in #seg_10, then highlight that segment.
	$('table.segments tr').click(function() {
		$('table.segments tr').removeClass('editing');
  		$(this).addClass('editing');
	});
	// If the user clicks on an editable area, catch that click to highlight the row.
	$('.edit_area').click(function() {
		$('table.segments tr').removeClass('editing');
		$(this).parents('tr').addClass('editing');
	});
	
	var myFile = document.location.toString();
	if (myFile.match('#seg_')) { // the URL contains an anchor
		// click the navigation item corresponding to the anchor
		var mySeg = myFile.split('#')[1];
		$('tr#'+myFile.split('#')[1]).click();
		
	} else {
  		// click the first navigation item
  		//$('ol#nav li:first').click();
	}
		

}

/* Manually create a click event on an editable area, let's you edit empty divs. */
function editComment(job_id, segment_id)
{
	$('div#'+job_id+'_'+segment_id+'_comment').click();
}

