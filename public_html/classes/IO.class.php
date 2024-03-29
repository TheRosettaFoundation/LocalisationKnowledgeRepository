<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/

/*
 * Class responsible for saving and processing XML files, and cleaning input and output parameters.
 * @author: David O Carroll
 */
class IO {

	/* Processes a file submission to receive the uploaded file and to save it
	 * to a set location. Later, that file will be read and segmented.
	 * Returns: path to imported file.
	 */
	static function saveImport(&$settings, $job_id)
	{
		$ret = false;
		if (is_uploaded_file($_FILES['import_file']['tmp_name']))
		{
			// This bit is memory intensive. Could use fread instead.
			$destination_file = $settings->get('files.dir_raw').'/'.$job_id.'.txt';
			if (move_uploaded_file($_FILES['import_file']['tmp_name'], $destination_file))
			{
				$ret = $destination_file;
			}
		}
		return $ret;
	}
	
	/*
	 * This takes text from the text area and saves it in the files/raw directory
	 */
	static function saveTextArea(&$settings, $job_id, $textContent)
	{
		$destination_file = $settings->get('files.dir_raw').'/'.$job_id.'.txt';
		$handle = fopen($destination_file, 'w');
		if($handle != NULL)
		{
			// a new segment is added at the end to ensure it behaves the same as the other method of importing
			fwrite($handle, $textContent.'<segment></segment><segment></segment>');	
			fclose($handle);
		}
		else
		{
			echo 'Error: unable to save text area';
		}
		return $destination_file;
	}
	
	/*
	 * Calls external Java tool to create a segmented XML file of the input.
	 */
	static function segmentFile(&$settings, $job_id)
	{
		$input_file = $settings->get('files.dir_raw').'/'.$job_id.'.txt';
		$output_file = $settings->get('files.dir_segmented').'/'.$job_id.'.xml';
		$segmenter = $settings->get('files.segmenter');
		$srx = $settings->get('files.srx');
		$language = $settings->get('files.language');

        $prog = "\"$segmenter\" -s \"$srx\" -l $language -b \"<segment>\" -e \"</segment>\" -i \"$input_file\" -o \"$output_file\" 2>&1";

		// Create the segmented file by running the segmenter from the command line
		$output = shell_exec($prog);
        if($output == NULL) {
          echo "<p>Segmenter failed, output is null</p>";
        } else {
          echo "<p>".$output."</p>";
        }

		// Add XML elements to the output file to complete it.
		// Implemented for smaller files that can be taken into memory.
		
		if (file_exists($output_file))
		{
			$handle = fopen($output_file, 'r+');
			if(!$handle)
			{
				die("Couldn't open file <i>$output_file</i>");
			}
			$old_content = file_get_contents($output_file);
			fwrite($handle, '<?xml version="1.0" encoding="utf-8" ?>'."\n".'<segments>'."\n".$old_content."\n".'</segments>');
			fclose($handle);
		}
		else
		{
			echo "file <i>$output_file</i> doesn't exist."; die;
		}
	}
	
	static function createSegmentsFromXLIFF(&$sql, $job_id, $xliff_str)
	{
		// Parse the XML, extracting the segment text.
		$doc = new DOMDocument();
		$doc->loadXML($xliff_str);
        foreach($doc->getElementsByTagName('source') as $source)
        {

        	$segment = strip_tags($doc->saveXML($source), '<g><x><bx><ex><bpt><ept><ph><it><mrk>');
        	$trans_unit_id = $source->parentNode->getAttribute('id'); //parent
        	Segment::insert($sql, $job_id, $segment, $trans_unit_id);
        }
	}
	
	// Returns the number of items imported.
	static function importXML(&$settings, &$sql, $job_id)
	{
		$xml_file = $settings->get('files.dir_segmented').'/'.$job_id.'.xml';
		// Get the XML
		$s = simplexml_load_file($xml_file);
		// Insert into DB
		$count = count($s->segment);
		$i = 0;
		foreach ($s->segment as $segment)
		{
		    if ($i<($count)) // The segmenter pushes in one last newline segment that shouldn't be there. Ignore the last empty <segment></segment> created by the segmenter.
		    {
				Segment::insert($sql, $job_id, $segment);
		    	$i++;
		    }
		}
		return $i;
	}
	
	/*
	 * Make input data safe. Used by get_val and post_val.
	 */
	static private function input_val($arr, $key)
	{
		$ret = false;
		if (isset($arr[$key]))
		{
			$val = $arr[$key];
			if (get_magic_quotes_gpc())
			{
              	$val = stripslashes($val);
            }
            $ret = strip_tags($val);
		}
		return $ret;
	}
	
	/* Used to cleanly get data from the $_GET variable. */
	static function get_val($key)
	{
		return IO::input_val($_GET, $key);
	}
	
	/* Used to cleanly get data from the $_POST variable. */
	static function post_val($key)
	{
		return IO::input_val($_POST, $key);
	}
	
	//Used to get the server the lkr is running on
	static function server()
    {
		$protocol = (empty($_SERVER['HTTPS'])) ? 'http://' : 'https://';
		$port = (($_SERVER['SERVER_PORT'])==80) ? '' : ':'.$_SERVER['SERVER_PORT'];
        return $protocol.$_SERVER['SERVER_NAME'].$port.'/'; // like "http://www.lkr.ie/"
	}
}
