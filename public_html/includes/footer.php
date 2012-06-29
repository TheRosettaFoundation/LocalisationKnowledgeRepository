<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/
/*
 * Display the footer with copyright notice and logos
 */
$settings = new Settings();
?>
</div><!--conent-->
	<div id="footer">
		<p>
			&copy; Eoin Ó Conchúir, Lorcan Ryan, Dimitra Anastasiou and David O Carroll. Developed as part of CNGL at the University of Limerick.
		</p>
		<div id="logos">
			<a href="http://www.cngl.ie/"><img src="<?php echo $settings->path_to_domain_root($_SERVER) ?>/resources/images/cngl.jpg"
                 width="110" height="40" alt="CNGL" /></a>
			<a href="http://www.ndp.ie/"><img src="<?php echo $settings->path_to_domain_root($_SERVER) ?>/resources/images/ndp.png" alt="NDP" /></a>
			<a href="http://www.sfi.ie/"><img src="<?php echo $settings->path_to_domain_root($_SERVER) ?>/resources/images/sfi.png" alt="SFI" /></a>
			<a href="http://www.ul.ie/"><img src="<?php echo $settings->path_to_domain_root($_SERVER) ?>/resources/images/ul.png" alt="UL" /></a>
		</div>
	</div>	
</div><!--body_wrapper-->
</body>
</html>
