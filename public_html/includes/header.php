<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/
 
/*
 * Display the header with logos and possibly links to author central and pm central
 */

$settings = new Settings();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
            "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<title><?php echo $params['title']; ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo $settings->path_to_domain_root($_SERVER) ?>/resources/css/reset.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $settings->path_to_domain_root($_SERVER) ?>/resources/css/style.css" />
	<script type="text/javascript" src="<?php echo $settings->path_to_domain_root($_SERVER) ?>/resources/js/jquery.js"></script>
	<script type="text/javascript" src="<?php echo $settings->path_to_domain_root($_SERVER) ?>/resources/js/jquery.qtip.js"></script>
	<?php 
		if (isset($params['extra_scripts']))
		{
			echo $params['extra_scripts'];
		} 
	?>
	<!-- 
		Hello source readers. Nice to meet you. 
		
		Q: What do you call a 3-legged donkey?
		A: A wonky.
		
		Q: Where does the wonky live?
		A: In an unstable.
	-->
</head>
<body>
<div id="body_wrapper">
	<div id="header">
		<a href="http://www.cngl.ie/"><img id="cngl" src="<?php echo $settings->path_to_domain_root($_SERVER) ?>/resources/images/cngl.jpg"
             width="110" height="40" alt="CNGL" /></a>
		<div><h1><a href="<?php echo $settings->path_to_domain_root($_SERVER) ?>">Localisation Knowledge Repository (LKR)</a></h1></div>
		<?php
		$parent_dir = $_SERVER['PHP_SELF'];
		if(strstr($parent_dir, "pm"))
		{
		?>
			<p align="right">
			<a href="<?php echo $settings->path_to_domain_root($_SERVER) ?>/author/">Log in as Author</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="<?php echo $settings->path_to_domain_root($_SERVER) ?>/pm/">PM Central</a>
			</p>
		<?php
		} 
		elseif(strstr($parent_dir, "author")) 
		{
		?>
			<p align="right"><a href="<?php echo $settings->path_to_domain_root($_SERVER) ?>/author/">Author Central</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="<?php echo $settings->path_to_domain_root($_SERVER) ?>/pm/">Log in as Project Manager</a></p>
		<?php
		}
		?>
	</div>
	<div id="content">


