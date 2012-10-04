<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/

/*
 * Class responsible for retrieving and altering segment data
 * @author: David O Carroll
 */
class Segment {
	private $segment_id;
	private $job;
	
	function Segment($job_id, $segment_id)
	{
		$this->job_id = $job_id;
		$this->segment_id = $segment_id;
	}
	
	/*
	 * Return the job_id
	 */
	function getJobID()
	{
		return $this->job_id;
	}
	
	/*
	 * Return the segment_id
	 */
	function getSegmentID()
	{
		return $this->segment_id;
	}
	
	/*
	 * Return the raw source text
	 */
	function getSourceRaw()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT source_raw
				FROM segments
				WHERE segment_id = '.$this->getSegmentID().'
				AND job_id = '.$this->getJobID();
		$ret = $sql->Select($q);
		return $ret[0][0];
	}
	
	/*
	 * Returns the source column
	 */
	function getSource()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT source
				FROM segments
				WHERE segment_id = '.$this->getSegmentID().'
				AND job_id = '.$this->getJobID();
		$ret = $sql->Select($q);
		return $ret[0][0];
	}
	
	/* 
	 * Return the trans-unit id for this segment. Such a value will be
	 * present where the segment was originally extracted from an
	 * XLIFF file.
	 */
	function getTransUnitID()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT trans_unit_id
				FROM segments
				WHERE segment_id = '.$this->getSegmentID().'
				AND job_id = '.$this->getJobID().'
				AND trans_unit_id IS NOT NULL';
		return ($r = $sql->Select($q)) ? $r[0][0] : false;
	}
	
	/*
	 * Checks if the current segment has been edited
	 */
	function isEdited()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT edited
				FROM segments
				WHERE segment_id = '.$this->getSegmentID().'
				AND job_id = '.$this->getJobID();
		$ret = $sql->Select($q);
		return ($ret[0][0] == 1) ? true : false;
	}
	
	/*
	 * Marks the current segment as edited
	 */
	function markEdited()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'UPDATE segments
				SET edited = 1
				WHERE segment_id = '.$this->getSegmentID().'
				AND job_id = '.$this->getJobID();
		$sql->Update($q);
	}
	
	/*
	 * Mark the current segment as unedited
	 */
	function unmarkEdited()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'UPDATE segments
				SET edited = NULL
				WHERE segment_id = '.$this->getSegmentID().'
				AND job_id = '.$this->getJobID();
		$sql->Update($q);
	}

    /*
     * Returns 1 if the segment is translatable
     * Returns 0 if the segment is not
     */
    function isTranslatable()
    {
        $db = new MySQLHandler();
        $db->init();
        $q = "SELECT translate
                FROM segments
                WHERE segment_id = ".$this->getSegmentID()."
                AND job_id = ".$this->getJobID();
        if($ret = $db->Select($q)) {
            $ret = $ret[0][0];
        }

        return $ret;
    }
	
	/*
	 * Return the target_raw column from DB
	 */
	function getTargetRaw()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT target_raw
				FROM segments
				WHERE job_id = '.$this->getJobID().'
				AND segment_id = '.$this->getSegmentID();
		$ret = $sql->Select($q);
		return ($ret[0][0] != NULL) ? $ret[0][0] : false;
	}
	
	/*
	 * Set the target_raw column to the text in param $text
	 */
	function setTargetRaw($text)
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'UPDATE segments
				SET target_raw = "'.$text.'"
				WHERE job_id = '.$this->getJobID().'
				AND segment_id = '.$this->getSegmentID();
		$sql->Update($q);
	}
	
	/*
	 * Return the comment associated with the current segment
	 */
	function getComment()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT comment
				FROM segments
				WHERE job_id = '.$this->getJobID().'
				AND segment_id = '.$this->getSegmentID();
		$ret = $sql->Select($q);
		return ($ret[0][0] != NULL) ? $ret[0][0] : false;
	}
	
	/*
	 * Set the comment for the current segment
	 */
	function setComment($text)
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'UPDATE segments
				SET comment = "'.$text.'"
				WHERE job_id = '.$this->getJobID().'
				AND segment_id = '.$this->getSegmentID();
		$sql->Update($q);
	}
	
	/*
	 * Returns true if the current segment has a warning associated with it, flase otherwise
	 */
	function hasWarning()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT has_warning
				FROM segments
				WHERE job_id = '.$this->getJobID().'
				AND segment_id = '.$this->getSegmentID();
		$ret = $sql->Select($q);
		return ($ret[0][0] > 0) ? true : false;
	}
	
	/*
	 * A function that checks if the current segment contains the given regex
	 */
	public function containsRegex($regex)
	{
		$text = $this->getTargetRaw();
		if(preg_match('"'.$regex.'"', $text) == 1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/*
	 * Check if current segment has been edited
	 */
	function checkEdited()
	{
		if($this->getSourceRaw() == $this->getTargetRaw())
		{
			$this->unmarkEdited();
		} 
		else 
		{
			$this->markEdited();
		}
	}
	
	/*
	 * This function splits a segment up into its constituent sentences
	 * @returns: false if there is only one segment or an array of sentences otherwise
	 */
	function testSegment()
	{
		$delimiter = "([\.|\?|\!]\s)";
		$testString = $this->getTargetRaw();
		$sentences = preg_split($delimiter, $testString);
		return (count($sentences) > 1) ? $sentences : false;
	}
	
	public static function insert(&$sql, $job_id, $text, $trans_unit_id = false, $translate = 1)
	{
		$segment = array();
		$segment['job_id'] = $sql->cleanse($job_id);
		$segment['source_raw'] = '\''.$sql->cleanseHTML($text).'\'';
		$segment['source'] = '\''.$sql->cleanseHTML(trim($text)).'\'';
		$segment['target_raw'] = '\''.$sql->cleanseHTML($text).'\'';
        $segment['translate'] = $sql->cleanse($translate);
		if (!empty($trans_unit_id))
		{
			$segment['trans_unit_id'] = '\''.$sql->cleanse($trans_unit_id).'\'';
		}
		return $sql->InsertArr('segments', $segment);
	}
}
