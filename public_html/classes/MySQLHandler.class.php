<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/

/*
 * MySQLHandler :: mySQL Wrapper. Version 1.3
 */

class MySQLHandler {

	var $DATABASE;
	var $USERNAME;
	var $PASSWORD;
	var $SERVER;

	var $LOGFILE = ''; 
	var $LOGGING = false; // debug on or off
	var $SHOW_ERRORS = false; // output errors. true/false
	var $SHOW_SQL = false; // turn off for production version. shows the sql statement that errored.
	var $USE_PERMANENT_CONNECTION = false;

	// Do not change the variables below
	var $CONNECTION;
	var $FILE_HANDLER;
	var $ERROR_MSG = '';
	var $SQL_ERRORED = '';
	var $RETURN_XML;
	var $XSLT;

	function MySQLHandler($return_xml = false) 
	{
		$this->RETURN_XML = $return_xml;

		$settings = new Settings();
		$this->DATABASE = $settings->get('db.database');
		$this->USERNAME = $settings->get('db.username');
		$this->PASSWORD = $settings->get('db.password');
		$this->SERVER = $settings->get('db.server');
		$this->LOGFILE = $settings->get('db.log_file'); // full path to debug LOGFILE. Use only in debug mode!
		$this->LOGGING = (strlen($this->LOGFILE)>0) ? true : false;
		$this->SHOW_ERRORS = ($settings->get('db.show_errors') == 'y') ? true : false;
		$this->SHOW_SQL = ($settings->get('db.show_sql') == 'y') ? true : false;
		
	}
	###########################################
	# Function:    init
	# Parameters:  N/A
	# Return Type: boolean
	# Description: initiates the MySQL Handler
	###########################################
	function init() 
	{
		$this->logfile_init();
		if ($this->OpenConnection()) 
		{
		  return true;
		} 
		else 
		{
		  return false;
		}
	}

	function initSelectDB($db, $username, $password) 
	{
		$this->DATABASE = $db;
		$this->USERNAME = $username;
		$this->PASSWORD = $password;
		$this->init();
	}

	###########################################
	# Function:    getXMLHeader
	# Parameters:  N/A
	# Return Type: string
	# Description: returns XML header
	###########################################
	function getXMLHeader() 
	{
		$str = '<?xml version="1.0" encoding="ISO-8859-1"?>'."\n";
		if (strlen($this->XSLT) > 0) 
		{
			$str .= '<?xml-stylesheet type="text/xsl" href="'.$this->XSLT.'"?>'."\n";
		}
		$str .= '<MySQLHandler-results>'."\n";
		return $str;
	}

	###########################################
	# Function:    setXSLT
	# Parameters:  filename : string
	# Return Type: N/A
	# Description: sets the XSLT for the XML
	###########################################
	function setXSLT($filename) 
	{
		$this->XSLT = $filename;
	}

	###########################################
	# Function:    getXMLFooter
	# Parameters:  N/A
	# Return Type: string
	# Description: returns XML footer
	###########################################
	function getXMLFooter() 
	{
		$str = '</MySQLHandler-results>'."\n";
		return $str;
	}

	###########################################
	# Function:    OpenConnection
	# Parameters:  N/A
	# Return Type: boolean
	# Description: connects to the database
	###########################################
	function OpenConnection()	
	{
		if ($this->USE_PERMANENT_CONNECTION) 
		{
			$conn = mysql_pconnect($this->SERVER,$this->USERNAME,$this->PASSWORD);
		} 
		else 
		{
			$conn = mysql_connect($this->SERVER,$this->USERNAME,$this->PASSWORD);
		}
		if ((!$conn) || (!mysql_select_db($this->DATABASE,$conn)))
		{
			$this->ERROR_MSG = "\r\n" . "Unable to connect to database - " . date('H:i:s');
			$this->debug();
			return false;
		} 
		else 
		{
	//		mysql_set_charset("latin1", $conn);
			$this->CONNECTION = $conn;
			return true;
		}
	}

	###########################################
	# Function:    CloseConnection
	# Parameters:  N/A
	# Return Type: boolean
	# Description: closes connection to the database
	###########################################
	function CloseConnection() 
	{
		if (mysql_close($this->CONNECTION)) 
		{
			return true;
		} 
		else 
		{
			$this->ERROR_MSG = "\r\n" . "Unable to close database connection - " . date('H:i:s');
			$this->debug();
			return false;
		}
	}

	###########################################
	# Function:    logfile_init
	# Parameters:  N/A
	# Return Type: N/A
	# Description: initiates the logfile
	###########################################
	function logfile_init() 
	{
		if ($this->LOGGING) 
		{
			$this->FILE_HANDLER = fopen($this->LOGFILE,'a') ;
			$this->debug();
		}
	}

	###########################################
	# Function:    logfile_close
	# Parameters:  N/A
	# Return Type: N/A
	# Description: closes the logfile
	###########################################
	function logfile_close() 
	{
		if ($this->LOGGING) 
		{
			if ($this->FILE_HANDLER) 
			{
				fclose($this->FILE_HANDLER) ;
			}
		}
	}

	###########################################
	# Function:    debug
	# Parameters:  N/A
	# Return Type: N/A
	# Description: logs and displays errors
	###########################################
	function debug() 
	{
		if ($this->SHOW_ERRORS) 
		{
			echo $this->ERROR_MSG;
			if (strlen($this->SQL_ERRORED) > 0)
			{
				echo "<br />" . $this->SQL_ERRORED;
			}
		}
		if ($this->LOGGING) 
		{
			if ($this->FILE_HANDLER) 
			{
				fwrite($this->FILE_HANDLER,$this->ERROR_MSG);
			} 
			else 
			{
				return false;
			}
		}
	}

	###########################################
	# Function:    Insert
	# Parameters:  sql : string
	# Return Type: integer
	# Description: executes a INSERT statement and returns the INSERT ID
	###########################################
	function Insert($sql) 
	{
		if ((empty($sql)) || (!preg_match("/^insert/i",$sql)) || (empty($this->CONNECTION))) 
		{
			$this->ERROR_MSG = "\r\n" . "SQL Statement is <code>null</code> or not an INSERT - " . date('H:i:s');
			$this->SQL_ERRORED = $sql;
			$this->debug();
			return false;
		} 
		else 
		{
			$conn = $this->CONNECTION;
			$results = mysql_query($sql,$conn);
			if (!$results) 
			{
				$this->ERROR_MSG = "\r\n" . mysql_error()." - " . date('H:i:s');
				$this->SQL_ERRORED = $sql;
				$this->debug();
				return false;
			} 
			else 
			{
				$result = mysql_insert_id();
				return $result;
			}
		}
	}
	
	function InsertArr($table_name, $insert_arr)
	{
		$keys = array_keys($insert_arr);
		$vals = array_values($insert_arr);
		$str = 'INSERT INTO '.$this->cleanse($table_name).' ('.implode(',', $keys).')
				VALUES ('.implode(',', $vals).')';
		return $this->Insert($str);
	}

	###########################################
	# Function:    Select
	# Parameters:  sql : string
	# Return Type: array
	# Description: executes a SELECT statement and returns a
	#              multidimensional array containing the results
	#              array[row][fieldname/fieldindex]
	###########################################
	function Select($sql)	
	{
		if ((empty($sql)) || (!preg_match("/^select/i",$sql)) || (empty($this->CONNECTION)))
		{
			$this->ERROR_MSG = "\r\n" . "SQL Statement is <code>null</code> or not a SELECT - " . date('H:i:s');
			$this->SQL_ERRORED = $sql;
			$this->debug();
			return false;
		} 
		else 
		{
			$conn = $this->CONNECTION;
			$results = mysql_query($sql,$conn);
			if ((!$results) || (empty($results))) 
			{
				$this->ERROR_MSG = "\r\n" . mysql_error()." - " . date('H:i:s');
				$this->SQL_ERRORED = $sql;
				$this->debug();
				return false;
			} 
			else 
			{
				$i = 0;
				$data = array();
				if ($this->RETURN_XML) 
				{
					$data = $this->getXMLString($results);
				} 
				else 
				{
					while ($row = mysql_fetch_array($results)) 
					{
						$data[$i] = $row;
						$i++;
					}
				}
				mysql_free_result($results);
				return $data;
			}
		}
	}

	###########################################
	# Function:    Count Select
	# Parameters:  sql : string
	# Return Type: array
	# Description: returns an int value for the result
	#			of an, e.g., count(*) statement.
	###########################################
	function CountSelect($sql)
	{
		if ((empty($sql)) || (!preg_match("/^select/i",$sql)) || (empty($this->CONNECTION))) {
			$this->ERROR_MSG = "\r\n" . "SQL Statement is <code>null</code> or not a SELECT - " . date('H:i:s');
			$this->SQL_ERRORED = $sql;
			$this->debug();
			return false;
		}
		else
		{
			$conn = $this->CONNECTION;
			$results = mysql_query($sql,$conn);

			if ((!$results) || (empty($results))) {
				$this->ERROR_MSG = "\r\n" . $sql . mysql_error()." - " . date('H:i:s');
				$this->SQL_ERRORED = $sql;
		  $this->SQL_ERRORED = $sql;
				$this->debug();
				return false;
			}
			else
			{
				$data = 0;
				if ($this->RETURN_XML) {
					$data = $this->getXMLString($results);
				}
				else
				{
					$data = mysql_num_rows($results);
				}
				mysql_free_result($results);
				return $data;
			}
		}
	}


	###########################################
	# Function:    getXMLString
	# Parameters:  results : SQL results set
	# Return Type: string
	# Description: Creates an XML string from the
	#              SQL results set
	###########################################
	function getXMLString($results) 
	{
		$header = $this->getXMLHeader();
		$footer = $this->getXMLFooter();
		$i = 0;
		$str ='';
		while ($row = mysql_fetch_array($results)) 
		{
			$keys = array_keys($row);
			$str .='<row>'."\n";
			for ($j=0; $j<count($row); $j++) 
			{
				if (!is_numeric($keys[$j])) 
				{
					if (is_numeric($row[$keys[$j]]) || is_string($row[$keys[$j]])) 
					{
						$datastr = $row[$keys[$j]];
					} 
					else 
					{
					$datastr = '<![CDATA["'.$row[$keys[$j]].'"]]>';
					}
				$str .="  <".$keys[$j].">".$datastr."</".$keys[$j].">\n";
				}
			}
			$str .="</row>\n";
			$i++;
			#exit;
		}
		return $header.$str.$footer;
	}

	###########################################
	# Function:    Update
	# Parameters:  sql : string
	# Return Type: integer
	# Description: executes a UPDATE statement
	#              and returns number of affected rows
	# Note: if no rows were updated because the values were identical,
	# then a value of 0 is returned.
	###########################################
	function Update($sql)
	{
		if ((empty($sql)) || (!preg_match("/^update/i",$sql)) || (empty($this->CONNECTION)))
		{
			$this->ERROR_MSG = "\r\n" . "SQL Statement is <code>null</code> or not an UPDATE - " . date('H:i:s');
			$this->debug();
			return false;
		}
		else
		{
			$conn = $this->CONNECTION;
			$results = mysql_query($sql,$conn);
			if (!$results)
			{
				$this->ERROR_MSG = "\r\n" . mysql_error()." - " . date('H:i:s');
				$this->SQL_ERRORED = $sql;
				$this->debug();
				return false;
			}
			else
			{
				return (mysql_affected_rows() > -1);
			}
		}
	}

	###########################################
	# Function:    Replace
	# Parameters:  sql : string
	# Return Type: boolean
	# Description: executes a REPLACE statement
	###########################################
	function Replace($sql) 
	{
		if ((empty($sql)) || (!preg_match("/^replace/i",$sql)) || (empty($this->CONNECTION))) 
		{
			$this->ERROR_MSG = "\r\n" . "SQL Statement is <code>null</code> or not a REPLACE - " . date('H:i:s');
			$this->debug();
			return false;
		} 
		else 
		{
			$conn = $this->CONNECTION;
			$results = mysql_query($sql,$conn);
			if (!$results) 
			{
				$this->ERROR_MSG = "\r\n" . "Error in SQL Statement : ($sql) - " . date('H:i:s');
				$this->SQL_ERRORED = $sql;
				$this->debug();
				return false;
			} 
			else 
			{
				return true;
			}
		}
	}

	###########################################
	# Function:    Delete
	# Parameters:  sql : string
	# Return Type: boolean
	# Description: executes a DELETE statement
	###########################################
	function Delete($sql)	
	{
		if ((empty($sql)) || (!preg_match("/^delete/i",$sql)) || (empty($this->CONNECTION))) 
		{
			$this->ERROR_MSG = "\r\n" . "SQL Statement is <code>null</code> or not a DELETE - " . date('H:i:s');
			$this->debug();
			return false;
		} 
		else 
		{
			$conn = $this->CONNECTION;
			$results = mysql_query($sql,$conn);
			if (!$results) 
			{
				$this->ERROR_MSG = "\r\n" . mysql_error()." - " . date('H:i:s');
				$this->SQL_ERRORED = $sql;
				$this->debug();
				return false;
			} 
			else 
			{
				return true;
			}
		}
	}

	###########################################
	# Function:    Query
	# Parameters:  sql : string
	# Return Type: boolean
	# Description: executes any SQL Query statement
	###########################################
	function Query($sql)	
	{
		if ((empty($sql)) || (empty($this->CONNECTION))) 
		{
			$this->ERROR_MSG = "\r\n" . "SQL Statement is <code>null</code> - " . date('H:i:s');
			$this->debug();
			return false;
		} 
		else 
		{
			$conn = $this->CONNECTION;
			$results = mysql_query($sql,$conn);
			if (!$results) 
			{
				$this->ERROR_MSG = "\r\n" . mysql_error()." - " . date('H:i:s');
				$this->SQL_ERRORED = $sql;
				$this->debug();
				return false;
			} 
			else 
			{
				return true;
			}
		}
	}

	function getCharacterSet() 
	{
		if (empty($this->CONNECTION)) 
		{
			$this->ERROR_MSG = "\r\n" . "No connection open - " . date('H:i:s');
			$this->debug();
			return false;
		}
		else 
		{
			$conn = $this->CONNECTION;
			return mysql_client_encoding($conn);
		}
	}

	###########################################
	# Function:    cleanseSQL
	# Parameters:  sql : string
	# Return Type: string
	# Description: cleanes variable for SQL, so escapes quotation marks, etc.
	###########################################
	function cleanseSQL($str)
	{
		if (get_magic_quotes_gpc())
		{
			$str = stripslashes($str);
		}
		return mysql_real_escape_string(strip_tags($str));
	}
	
	// Maintain tags, don't strip them.
	function cleanseHTML($str)
	{
		if (get_magic_quotes_gpc())
		{
			$str = stripslashes($str);
		}
		return mysql_real_escape_string($str);
	}
	
	/*
	 * A clone of cleanseSQL, in the name of shorter function calls.
	 */
	function cleanse($str)
	{
		return $this->cleanseSQL($str);
	}
}
