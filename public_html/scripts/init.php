<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/
/*
 * Ensure all necessary scripts can be seen
 * @author: David O Carroll
 */

//error_reporting(E_ALL);

require(__DIR__.'/../classes/Settings.class.php');
require(__DIR__.'/../classes/MySQLHandler.class.php');
require(__DIR__.'/../classes/IO.class.php');
require(__DIR__.'/../classes/Job.class.php');
require(__DIR__.'/../classes/Template.class.php');
require(__DIR__.'/../classes/Report.class.php');
require(__DIR__.'/../classes/Segment.class.php');
require(__DIR__.'/../classes/AnnotatorsRef.class.php');
require(__DIR__.'/../classes/GlossaryEntry.class.php');
require(__DIR__.'/../classes/Stopword.class.php');
require(__DIR__.'/../classes/Solas.class.php');
require('HTTP/Request2.php');
