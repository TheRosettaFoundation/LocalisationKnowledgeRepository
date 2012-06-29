<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/

// http://www.phpit.net/article/create-settings-class-php/
// Loads the file /conf/conf.php.

Class Settings {
    var $_settings = array();

	function Settings() 
	{
		$file = __DIR__.'/../conf/conf.php';
		$this->load($file);
	}

    function get($var) {
        $var = explode('.', $var);

        $result = $this->_settings;
        foreach ($var as $key) {
            if (!isset($result[$key])) {
                $result = false;
            } else {
                $result = $result[$key];
            }
        }

        return $result;
    }

	function load ($file) {
        if (file_exists($file) == false) { return false; }

        // Include file
        include ($file);
        unset($file);

        // Get declared variables
        $vars = get_defined_vars();

        // Add to settings array
        foreach ($vars as $key => $val) {
            if ($key == 'this') continue;

            $this->_settings[$key] = $val;
        }

    }

    function path_to_domain_root($srvr) {
        $ret = '';
        $domainRoot = $this->get('domain.root');
        if($domainRoot != '') {
            $ret = substr($srvr['REQUEST_URI'], 0, strpos($srvr['REQUEST_URI'], $domainRoot) + strlen($domainRoot));
        }

        return $ret;
    }

}
