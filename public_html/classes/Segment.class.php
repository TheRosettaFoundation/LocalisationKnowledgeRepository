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
	private $job_id;
	
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
     * Get the raw source text, parse it for ITS rules and replace its
     * tags with HTML for display
     */
    function getSourceParsed()
    {
        $source_raw = "<source>".$this->getSourceRaw()."</source>";
        $source_parsed = "";
        $doc = new DOMDocument();
        $doc->loadXML($source_raw);
        $source = $doc->getElementsByTagName("source")->item(0);
        $source_parsed = $this->parseElement($source);
        return $source_parsed;
    }

    private function parseElement($node)
    {
        $source_parsed = "";
        if (get_class($node) == "DOMText") {
            $source_parsed = $node->nodeValue;
        } else {
            $closingTag = "";
            if(strcasecmp($node->nodeName, "mrk") == 0) {
                $mtype = $node->getAttribute("mtype");
                if(strcasecmp($mtype, "phrase") == 0) {
                    $source_parsed .= " ".$node->nodeValue;
                    $ref = $node->getAttribute("url");
                    if($ref == NULL) {
                        $ref = $node->getAttribute("disambigIdentRef");
                    }
                    if($ref == NULL) {
                        $ref = $node->getAttribute("comment");
                    }
                    if($ref != NULL) {
                        $source_parsed .= "<sup><a target='_blank' href='$ref'>[ref]</a></sup>";
                    }
                } elseif(strcasecmp($mtype, "x-DNT") == 0 || strcasecmp($mtype, "preserve") == 0
                             || strcasecmp($mtype, "protected") == 0) {
                    $source_parsed .= " <span class='no-translate'>";
                    $closingTag .= "</span>";
                } elseif(strcasecmp($mtype, "x-its-Translate-Yes") == 0) {
                    $source_parsed .= " <span class='translate'>";
                    $closingTag .= "</span>";
                } elseif(strcasecmp($mtype, "term") == 0 || $node->getAttribute("terminology") == "yes") {
                    $confidence = $node->getAttribute("its:termConfidence");
                    if ($confidence == "") {
                        $confidence = $node->getAttribute("termConfidence");
                    }
                    $ref = $node->getAttribute("its:termInfoRef");
                    if ($ref == "") {
                        $ref = $node->getAttribute("termInfoRef");
                    }
                    $source_parsed .= "<span class='term' title='Confidence: $confidence'>";
                    $closingTag .= "</span><sup><a href='$ref'>[$ref]</a></sup>";
                } elseif(strcasecmp($mtype, "x-its") || strcasecmp($mtype, "xits")) {
                    $comment = $node->getAttribute("comment");
                    if ($comment != "") {
                        $source_parsed .= " <span class=\"comment\" title=\"$comment\">";
                        $closingTag .= "</span>";
                    }
                }
            }
            $annotatorsRef = $node->getAttribute("annotatorsRef");
            if ($annotatorsRef == NULL) {
                $annotatorsRef = $node->getAttribute("its:annotatorsRef");
            }
            if ($annotatorsRef != NULL) {
                $category = substr($annotatorsRef, 0, strpos($annotatorsRef, "|"));
                $ref = substr($annotatorsRef, strpos($annotatorsRef, "|") + 1, strlen($annotatorsRef) - 1);
                $source_parsed .= "<a href='$ref' title='$category' target='_blank'>";
                $closingTag .= "</a>";
            }
            if($node->hasChildNodes()) {
                $child = $node->firstChild;
                while($child != NULL) {
                    $source_parsed .= $this->parseElement($child);
                    $child = $child->nextSibling;
                }
            }
            $source_parsed .= $closingTag;
        }

        return $source_parsed;
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
     * Returns the file id of this segment
     */
    function getFileId()
    {
        $sql = new MySQLHandler();
        $sql->init();
        $q = 'SELECT file_id
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
	
	public static function insert(&$sql, $job_id, $text, $fileId = 1, $trans_unit_id = false, $translate = 1)
	{
		$segment = array();
		$segment['job_id'] = $sql->cleanse($job_id);
		$segment['source_raw'] = '\''.$sql->cleanseHTML($text).'\'';
		$segment['source'] = '\''.$sql->cleanseHTML(trim($text)).'\'';
		$segment['target_raw'] = '\''.$sql->cleanseHTML($text).'\'';
        $segment['translate'] = $sql->cleanse($translate);
        $segment['file_id'] = $sql->cleanse($fileId);
		if (!empty($trans_unit_id))
		{
			$segment['trans_unit_id'] = '\''.$sql->cleanse($trans_unit_id).'\'';
		}
		return $sql->InsertArr('segments', $segment);
	}
}
