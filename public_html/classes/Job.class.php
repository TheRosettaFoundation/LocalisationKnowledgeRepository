<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/

/*
 * Class responsible for accessing and changing data relating to jobs
 * @author: David O Carroll
 */
class Job {
	private $job_id;
	private $segments;
	
	function Job($job_id)
	{
		$this->job_id = $job_id;
		// Automatically set the segments from the database.
		$this->setSegments();
	}
	
	/*
	 * Gets the job's segments from the object rather than from the DB. Use setSegments
	 * to get from the DB. May be better to always just get from DB.
	 */
	function getSegments()
	{
		return (count($this->segments)>0) ? $this->segments : false;
	}
	
	/*
	 * Counts the number of warnings in the current job
	 */
	function countWarnings($report)
	{
		return $report->countWarnings();
	}
	
	/*
	 * Called from the constructor this gets the segments from the DB
	 */
	function setSegments()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$str = 'SELECT *
				FROM segments
				WHERE job_id ='.$sql->cleanseSQL($this->job_id).'
				ORDER BY segment_id';
		$row = $sql->Select($str);
		$this->segments = array();
		foreach($row as $r)
		{
			// Make them into an array of type Segment.class.php
			$this->segments[$r['segment_id']] = new Segment($this->job_id, $r['segment_id']);
		}
		return (count($this->segments)>0) ? true : false;
	}

    public function getAnnotatorsRefs($file_id = 1)
    {
        $refs = null;
        $sql = new MySQLHandler();
        $sql->init();
        $q = "SELECT *
                FROM annotatorsRefs
                WHERE job_id = ".$sql->cleanse($this->job_id)."
                AND file_id = ".$sql->cleanse($file_id);
        $result = $sql->Select($q);
        if ($result) {
            $refs = array();
            foreach ($result as $row) {
                $refs[] = new AnnotatorsRef($row['ref_id'], $this->job_id);
            }
        }
        return $refs;
    }

    public function getGlossaryEntries()
    {
        $entries = null;
        $sql = new MySQLHandler();
        $sql->init();
        $q = "SELECT *
               FROM glossaryEntries
               WHERE job_id = ".$sql->cleanse($this->job_id);
        $result = $sql->Select($q);
        if ($result) {
            $refs = array();
            foreach ($result as $row) {
                $entries[] = new GlossaryEntry($row['glossary_id'], $this->job_id);
            }
        }
        return $entries;
    }

    /*
     * This functions prints a table of Glossary Terms
     */
    public function printGlossaryEntries()
    {
        $entries = $this->getGlossaryEntries();
        if ($entries) {
            echo "<center>";
                echo "<h3>Glossary Entries</h3>";
            echo "</center>";
            echo "<table class='segments' border='0' cellpadding='3' cellspacing='0' align='center'>";
            echo "<tbody>";
            echo "<tr><th>ID</th><th>Term</th><th>Translation</th></tr>";
            foreach ($entries as $entry) {
                echo "<tr>";
                echo "<td><a name=\"".$entry->getRef()."\">".$entry->getRef()."</td>";
                echo "<td>".$entry->getTerm()."</td>";
                echo "<td>".$entry->getTranslation()."</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            echo "<br />";
        }
    }
	
	/*
	 * This is a function that checks to see if any segments contain more than one sentence
	 */
	function testSegments()
	{
		if($segments = $this->getSegments())
		{
			foreach($segments as $segment)
			{
				//can't contain more than one sentence if its hasn't been edited yet
				if($segment->isEdited())
				{
					if($new_sentences = $segment->testSegment())
					{
						$this->moveDownSegments($segment->getSegmentID());
						$this->insertNewSegment($segment->getSegmentID(), $new_sentences);
					}
				}
			}
		}
	}
	
	/*
	 * This is a function that moves down the segments who's ids are greater than a given id
	 */
	private function moveDownSegments($seg_id)
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'UPDATE segments
					SET segment_id = segment_id + 1
					WHERE job_id = '.$this->getJobID().'
					AND segment_id > '.$seg_id.'
					ORDER BY segment_id DESC';
		$sql->Update($q);
	}
	
	/*
	 * This function replaces the old sentence with the new one and places the second sentences after the original
	 */
	private function insertNewSegment($seg_id, $new_sentences)
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'UPDATE segments
					SET target_raw = "'.addslashes($new_sentences[0]).'."
					WHERE job_id = '.$this->getJobID().'
					AND segment_id = '.$seg_id;
		$sql ->Update($q);
		
		$new_seg_id = $seg_id + 1;
		$q = 'INSERT INTO segments (job_id, segment_id, target_raw, edited)
					VALUES ('.$this->getJobID().', '.$new_seg_id.', " '.$new_sentences[1].'", 1)';
		$sql->Insert($q);
	}
	
	/*
	 * Return the current job's id
	 */
	function getJobID()
	{
		return (intval($this->job_id) > 0) ? $this->job_id : false;
	}
	
	function xmlUpdateMetadata(&$simple_xml, $domain, $word_count, $segment_count, $character_count, $author_name, $email_address, $company_name, $comment)
	{
        if (isset($simple_xml->file->attributes()->category)) {
            // Only bother changing the XLIFF if the new $domain value
            // is different to the existing one.
            $category = $simple_xml->file->attributes()->category[0];
            if ($category != $domain) {
                $simple_xml->file->attributes()->category = $domain;
            }
        } elseif (!empty($domain)) {
            // Create the category attribute in the XLIFF.
            $simple_xml->file['category'] = $domain;
        }

        if ($simple_xml->attributes()->version[0] == "2.0") {
            $metadata = $simple_xml->file->{'mda:metadata'};
            if ($metadata->count == 0) {
                $metadata = $simple_xml->file->addChild('mda:metadata');
            } else {
                $metadata = $metadata[0];
            }

            $group = $metadata->addChild('mda:metagroup');
            $group->addAttribute('category', 'phase');

            $element = $group->addChild('mda:meta', 'Quality Assurance');
            $element->addAttribute('type', 'phase-name');

            if (!empty($company_name)) {
                $element = $group->addChild('mda:meta', $company_name);
                $element->addAttribute('type', 'company-name');
            }

            $element = $group->addChild('mda:meta', 'authoring');
            $element->addAttribute('type', 'process-name');

            if (!empty($author_name)) {
                $element = $group->addChild('mda:meta', $author_name);
                $element->addAttribute('type', 'contact-name');
            }

            if (!empty($email_address)) {
                $element = $group->addChild('mda:meta', $email_address);
                $element->addAttribute('type', 'contact-email');
            }

            $element = $group->addChild('mda:meta', $this->getJobID());
            $element->addAttribute('type', 'job-id');

            $element = $group->addChild('mda:meta', 'LKR');
            $element->addAttribute('type', 'tool-id');

            $group = $metadata->addChild('mda:metagroup');
            $group->addAttribute('category', 'tool');

            $element = $group->addChild('mda:meta', 'LKR');
            $element->addAttribute('type', 'tool-id');

            $element = $group->addChild('mda:meta', 'LKR');
            $element->addAttribute('type', 'tool-name');

            $element = $group->addChild('mda:meta', 'v1');
            $element->addAttribute('type', 'tool-version');

            if (!empty($comment)) {
                $group = $metadata->addChild('mda:metagroup');
                $group->addAttribute('category', 'note');

                $element = $group->addChild('mda:meta', $comment);
                $element->addAttribute('type', 'note');
            }

            $group = $metadata->addChild('mda:metagroup');
            $group->addAttribute('category', 'count');

            $element = $group->addChild('mda:meta', $word_count);
            $element->addAttribute('type', 'word_count');

            $element = $group->addChild('mda:meta', $segment_count);
            $element->addAttribute('type', 'segment_count');

            $element = $group->addChild('mda:meta', $character_count);
            $element->addAttribute('type', 'character_count');
        } else {
    		// Enter tool and author information.
	    	$phase_group = false;
		    $phase = false;
    		if ($simple_xml->file->head->{'phase-group'} == null) {
	    		$phase_group = $simple_xml->file->header->addChild('phase-group');
		    	$phase = $phase_group->addChild('phase');
	    	} else {
		    	$phase_group = $simple_xml->file->header->{'phase-group'};
	    		$phase = $phase_group[0]->addChild('phase');
    		}
	    	$phase->addAttribute('phase-name', 'Quality Assurance');
    		if (!empty($company_name)) {
    			$phase->addAttribute('company-name', $company_name);
    		} 
	    	$phase->addAttribute('process-name', 'authoring');
    		if (!empty($author_name)) {
		    	$phase->addAttribute('contact-name', $author_name);
	    	} 
    		if (!empty($email_address)) {
	    		$phase->addAttribute('contact-email', $email_address);
    		}
		    $phase->addAttribute('job-id', $this->getJobID());
	    	$phase->addAttribute('tool-id', 'LKR');
    		// Add <tool>
		    $tool = $simple_xml->file->header->addChild('tool');
	    	$tool->addAttribute('tool-name', 'LKR');
    		$tool->addAttribute('tool-id', 'LKR');
		    $tool->addAttribute('tool-version', 'v1');
	    	// Add <note>
    		if (!empty($comment)) {
			    $simple_xml->file->header->addChild('note', $comment);
		    }
		
	    	// Create the statistics elements in a <group>.
    		if (!empty($word_count)) {
	    		$simple_xml->file->body->group->{'count-group'}[0]->count = $word_count;
    			$simple_xml->file->body->group->{'count-group'}[0]['name'] = 'word_count';
			    $simple_xml->file->body->group->{'count-group'}[0]->count['count-type'] = 'total';
		    	$simple_xml->file->body->group->{'count-group'}[0]->count['unit'] = 'word';	
	    	}
		
    		if (!empty($segment_count)) {
	    		$simple_xml->file->body->group->{'count-group'}[1]->count = $segment_count;
    			$simple_xml->file->body->group->{'count-group'}[1]['name'] = 'segment_count';
			    $simple_xml->file->body->group->{'count-group'}[1]->count['count-type'] = 'total';
		    	$simple_xml->file->body->group->{'count-group'}[1]->count['unit'] = 'segment';
	    	}
		
    		if (!empty($character_count)) {
    			$simple_xml->file->body->group->{'count-group'}[2]->count = $character_count;
			    $simple_xml->file->body->group->{'count-group'}[2]['name'] = 'character_count';
		    	$simple_xml->file->body->group->{'count-group'}[2]->count['count-type'] = 'total';
	    		$simple_xml->file->body->group->{'count-group'}[2]->count['unit'] = 'character';
    		}
        }
		
		return $simple_xml;
	}
	
	/*
	 * Given a SimpleXMLElement object of this job, update it with
	 * edited target segments found in the database.
	 * Used for during export of XML when completing job.
	 */
	function xmlUpdateSegments(&$simple_xml)
	{
        $version = $simple_xml->attributes()->version[0];
		if ($segments = $this->getSegments())
		{
			foreach($segments as $segment)
			{
				if ($segment->isEdited())
				{
					// Update segment text in the XML.
					$trans_unit_id = $segment->getTransUnitID();
                    if ($version == "2.0") {
                        $internal_errors = libxml_use_internal_errors(true);
                        $doc = new DOMDocument();
                        $doc->loadXML($segment->getTargetRaw()."</source>", LIBXML_NOWARNING);
                        $sources = $doc->getElementsByTagName('source');
                        if ($sources->length > 0) {
                            $source = $sources->item(0);
                        } else {
                            $doc->loadXML("<source>".$segment->getTargetRaw()."</source>");
                            $source = $doc->getElementsByTagName('source')->item(0);
                        }

                        $fileSegments = $simple_xml->xpath("//segment");
                        if (count($fileSegments) >= $segment->getSegmentID()) {
                            $dom = dom_import_simplexml($fileSegments[$segment->getSegmentID() - 1]);
                            $newSource = $dom->ownerDocument->importNode($source, true);
                            $oldSource = $dom->getElementsByTagName('source')->item(0);
                            $dom->replaceChild($newSource, $oldSource);
                        } else {
                            $dom = dom_import_simplexml($fileSegments[$segment->getSegmentID() - 2]);
                            $segment = $doc->createElement("segment");
                            $segment->appendChild($source);
                            $newSegment = $dom->ownerDocument->importNode($segment, true);
                            $parent = $dom->parentNode;
                            $parent->appendChild($newSegment);
                        }
                        libxml_use_internal_errors($internal_errors);
                    } else {
    					foreach ($simple_xml->file->body->{'trans-unit'} as $trans_unit)
	    				{
		    				if ($trans_unit->attributes()->id[0] == $trans_unit_id)
			    			{
                                $doc = new DOMDocument();
                                $doc->loadXML($segment->getTargetRaw());
                                if ($doc->getElementsByTagName('seg-source')->length > 0) {
                                    $segSource = $doc->getElementsByTagName('seg-source')->item(0);
                                    $parent = $segSource->parentNode;
                                    if ($parent) {
                                        $segSource->parentNode->removeChild($segSource);
                                    }
                                    $children = $trans_unit->children();
                                    $children['seg-source'][0] = $doc->saveXML($segSource);
                                } else {
                                    $source = $doc->getElementsByTagName('source')->item(0);
                                    $source->parentNode->removeChild($source);
                                    $trans_unit->children()->source[0] = $doc->saveXML($source);
                                }
                            }
						}
					}
				}
			}
		}
		return $simple_xml;
	}
	
	/*
	 * Gets jobs that are currently being worked on by the author
	 * @returns An array of jobs
	 */
	public static function getOpenJobs()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT *
				FROM jobs
				WHERE complete_date IS NULL
				ORDER BY job_id DESC';
		$r = $sql->Select($q);
		$jobs = array();
		if($r)
		{
			foreach ($r as $job_row)
			{
				$jobs[] = new Job($job_row['job_id']);
			}
		}
		return (count($jobs)>0) ? $jobs : false;
	}
	
	/* 
	 * @returns an array of all jobs
	 * @params $order_by: determines what order the list of jobs is in
	 */
	public static function getAllJobs($order_by = 'job_id')
	{
		if($order_by != 'import_date')
		{
			$order_by = 'job_id';
		}
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT *
				FROM jobs
				ORDER BY '.$order_by.' DESC';
		$r = $sql->Select($q);
		$jobs = array();
		foreach ($r as $job_row)
		{
			$jobs[] = new Job($job_row['job_id']);
		}
		return (count($jobs)>0) ? $jobs : false;
	}
	
	/*
	 * Returns an array of the last five completed jobs
	 */
	static public function getCompletedJobs()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT *
				FROM jobs
				WHERE complete_date IS NOT NULL
				ORDER BY complete_date DESC
				LIMIT 5';
		$r = $sql->Select($q);
		$jobs = array();
		if($r)
		{
			foreach ($r as $job_row)
			{
				$jobs[] = new Job($job_row['job_id']);
			}
		}
		return (count($jobs)>0) ? $jobs : false;
	}
	
	/*
	 * Returns an array of the last five closed jobs
	 */
	public static function getClosedJobs()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT *
				FROM jobs
				WHERE closed_date IS NOT NULL
				ORDER BY job_id DESC
				LIMIT 5';
		$r = $sql->Select($q);
		$jobs = array();
		foreach ($r as $job_row)
		{
			$jobs[] = new Job($job_row['job_id']);
		}
		return (count($jobs)>0) ? $jobs : false;
	}
	
	/*
	 * Change the text in a segment
	 */
	public function updateSegment($segment_id, $segment_text)
	{
		$ret = false;
		if ($segment_id)
		{
			$this->segments[$segment_id]->setTargetRaw($segment_text);
			$ret = true;
		}
		return $ret;
	}
	
	/*
	 * Change the text in a comment
	 */
	public function updateComment($segment_id, $comment_text)
	{
		$ret = false;
		if($segment_id)
		{
			$this->segments[$segment_id]->setComment($comment_text);
			$ret = true;
		}
		return $ret;
	}
	
	public function isSolasJob()
	{
		$ret = false;
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT *
				FROM jobs
				WHERE job_id = '.$this->getJobID().'
				AND CNLF_id IS NOT NULL';
		if ($r = $sql->Select($q))
		{
			$ret = true;
		}
		return $ret;
	}
	
	/*
	 * Checks to see if the job has been marked as completed
	 */
	public function isComplete()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT *
				FROM jobs
				WHERE job_id = '.$this->getJobID().'
				AND complete_date IS NOT NULL';
		$r = $sql->Select($q);
		$jobs = array();
		foreach($r as $job_row)
		{
			$jobs[] = new Job($job_row['job_id']);
		}
		return (count($jobs) > 0) ? true : false;
	}
	
	/*
	 * Return the date the job was marked as completed
	 */
	public function getCompleteDate()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT complete_date
				FROM jobs
				WHERE job_id = '.$this->getJobID();
		$r = $sql->Select($q);
		$date = $r[0][0];
		return $date;
	}
	
	/*
	 * Checks to see if the job has been closed
	 */
	public function isClosed()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT *
				FROM jobs
				WHERE job_id = '.$this->getJobID().'
				AND closed_date IS NOT NULL';
		$r = $sql->Select($q);
		$jobs = array();
		foreach($r as $job_row)
		{
			$jobs[] = new Job($job_row['job_id']);
		}
		return (count($jobs) > 0) ? true : false;
	}
	
	/* 
	 * Return the date the current job was marked as closed
	 */
	public function getClosedDate()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT closed_date
				FROM jobs
				WHERE job_id = '.$this->getJobID();
		$r = $sql->Select($q);
		$date = $r[0][0];
		return $date;
	}
	
	/* 
	 * Get the date that the current job was imported
	 */
	public function getImportDate()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT import_date
				FROM jobs
				WHERE job_id = '.$this->getJobID();
		$r = $sql->Select($q);
		return $r[0][0];
	}
	
	public function setInitialWarnings()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$report = new Report($this);
		$init_warnings = $this->countWarnings($report);
		$q = 'UPDATE jobs
				SET initial_warnings = '.$sql->cleanse($init_warnings).', import_date = NOW()
				WHERE job_id = '.$sql->cleanse($this->getJobID()).'
				LIMIT 1';
		return $sql->Update($q);
	}
	
	/*
	 * Returns the number of warnings that were fixed since the last iteration
	 */
	public function fixedWarningsCount($currentWarnings)
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT initial_warnings
				FROM jobs
				WHERE job_id = '.$this->job_id;
		$initialWarnings = $sql->Select($q);
		
		$fixedWarnings = $initialWarnings[0][0] - $currentWarnings;
		return $fixedWarnings;
	}
	
	/*
	 * Returns an array with the ids of the infected segments
	 */
	public function getInfectedSegments($report)
	{
		$infectedSegments = array();
		$infectedSegments = $report->getInfectedSegments($this->getJobID());
		return $infectedSegments;
	}
	
	/*
	 * Returns the number of times the job has been sent back by the PM
	 * to be further reviewed by the author
	 */
	public function getSendBacks()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT total_send_backs
				FROM jobs
				WHERE job_id='.$this->getJobID();
		$r = $sql->Select($q);
		return $r[0][0];
	}
	
	/*
	 * Returns the CNLF id of the current job or null if there is none
	 */
	public function getCNLFid()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT CNLF_id
				FROM jobs
				WHERE job_id = '.$this->getJobID();
		$r = $sql->Select($q);
		return $r[0][0];
	}
	
	/*
	 * Returns the name of the author, entered on import or retrieved from the cnlf
	 */
	public function getAuthorName()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT author_name
				FROM jobs
				WHERE job_id = '.$this->getJobID();
		$r = $sql->Select($q);
		return $r[0][0];
	}
	
	/*
	 * Returns the email address of the author
	 */
	public function getEmailAddress()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT email_address
				FROM jobs
				WHERE job_id = '.$this->getJobID();
		$r = $sql->Select($q);
		return $r[0][0];
	}
	
	/*
	 * Returns the domain of the job as specified when the file was imported
	 */
	public function getDomain()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT domain
				FROM jobs
				WHERE job_id='.$this->getJobID();
		$r = $sql->Select($q);
		return $r[0][0];
	}
	
	/*
	 * Returns the name of the original file
	 */
	public function getOriginalFile()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT original_file
				FROM jobs
				WHERE job_id = '.$this->getJobID();
		$r = $sql->Select($q);
		return $r[0][0];
	}
	
	// Return the file extension of the originally uploaded file. It'll be false
	// if not file was uploaded (such as a LocConnect upload).
	function fileExtention()
	{
		$ret = false;
		if ($file_name = $this->getOriginalFile())
		{
			$ret = strtolower(array_pop(explode('.', $file_name)));
		}
		return $ret;
	}
		
	/*
	 * @returns the source language of the file
	 */
	public function getSourceLanguage()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT source_language
				FROM jobs
				WHERE job_id = '.$this->getJobID();
		$r = $sql->Select($q);
		return $r[0][0];
	}
	
	/*
	 * @returns the target language of the file
	 */
	public function getTargetLanguage()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT target_language
				FROM jobs
				WHERE job_id = '.$this->getJobID();
		$r = $sql->Select($q);
		return $r[0][0];
	}
	
	/*
	 * @returns the company name entered on import or retrieved from the cnlf
	 */
	public function getCompanyName()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT company_name
				FROM jobs
				WHERE job_id = '.$this->getJobID();
		$r = $sql->Select($q);
		return $r[0][0];
	}
	
	/*
	 * Returns the number of words in the job
	 */
	public function getWordCount()
	{
		$count = 0;
		if($segments = $this->getSegments())
		{
			foreach($segments as $segment)
			{
				$count += str_word_count($segment->getTargetRaw());
			}
		}
		return $count;
	}
	
	/*
	 * Returns a count of the segments of the job
	 */
	public function getSegmentCount()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT MAX(segment_id)
				FROM segments
				WHERE job_id = '.$this->getJobID();
		$r = $sql->Select($q);
		return $r[0][0];
	}
	
	/*
	 * @returns the number of characters in all segments
	 */
	public function getCharacterCount()
	{
		$count = 0;
		if($segments = $this->getSegments())
		{
			foreach($segments as $segment)
			{
				$count += strlen($segment->getTargetRaw());
			}
		}
		return $count;
	}
	
	/*
	 * This is a function that returns the HTML refering to the amount of time since last event
	 */
	public function html_status()
	{
		echo '<span class="status"> ';
		$cnlf_id = $this->getCNLFid();
		if($cnlf_id != null)
		{
			echo '(CNLF server job &ndash; ';
		}
		else
		{
			echo '(';
		}
		if($this->isClosed())
		{
			echo 'Closed ';
			$timeDiff = $this->calculateDateDiff(time(), strtotime($this->getClosedDate()));
		}
		elseif($this->isComplete())
		{
			echo 'Completed ';
			$timeDiff = $this->calculateDateDiff(time(), strtotime($this->getCompleteDate()));
		}
		else
		{
			echo 'Imported ';
			$timeDiff = $this->calculateDateDiff(time(), strtotime($this->getImportDate()));
		}
		// only print the most significant amount of time
		if($timeDiff['days'] > 0)
		{
			echo $timeDiff['days'];
			if($timeDiff['days'] == 1)
			{
				echo ' day';
			}
			else
			{
				echo ' days';
			}
		} 
		elseif($timeDiff['hours'] > 0)
		{
			echo $timeDiff['hours'];
			if($timeDiff['hours'] == 1)
			{
				echo ' hour';
			}
			else
			{
				echo ' hours';
			}
		} 
		elseif($timeDiff['minutes'] > 0)
		{
			echo $timeDiff['minutes'];
			if($timeDiff['minutes'] == 1)
			{
				echo ' minute';
			}
			else
			{
				echo ' minutes';
			}
		} 
		else
		{
			echo $timeDiff['seconds'];
			if($timeDiff['seconds'] == 1)
			{
				echo ' second';
			}
			else
			{
				echo ' seconds';
			}
		}
		echo ' ago)</span>';
	}
	
	/*
	 * This function calculates the difference between two Unix style date/times
	 */
	private function calculateDateDiff($unixDate1, $unixDate2)
	{
		$diff = abs($unixDate1-$unixDate2);
		$days = 0;
		$seconds = 0;
		$hours  = 0;
		$minutes = 0;
		if ($diff % 86400 > 0)
		{
			$rest = ($diff % 86400);
			$days = ($diff - $rest) / 86400;
			if ($rest % 3600 > 0 )
			{
	           $rest1 = ($rest % 3600);
	           $hours = ($rest - $rest1) / 3600;

				if ($rest1 % 60 > 0 )
				{
					$rest2 = ($rest1 % 60);
					$minutes = ($rest1 - $rest2) / 60;
					$seconds = $rest2;
				}
				else
				{
					$minutes = $rest1 / 60;
				}
			}
			else
			{
				$hours = $rest / 3600;
			}
		}
		else
	    {
			$days = $diff / 86400;
		}
		return array( 'days' => $days, 'hours' => $hours, 'minutes' => $minutes, 'seconds' => $seconds);
	}

    /*
     * This function prints the legend for views
     */
    public function printLegend()
    {
        echo "<center>";
            echo "<h3>Legend</h3>";
        echo "</center>";
        echo "<table border='0' cellpadding='3' cellspacing='0' align='center'>";
            echo "<tbody>";
                echo "<tr><td>Format</td><td>Meaning</td></tr>";
                echo "<tr>";
                    echo "<td class='no-translate'>Sample</td>";
                    echo "<td>Text marked as \"Do not translate\"</td>";
                echo "</tr>";
                echo "<tr>";
                    echo "<td class='comment'>Comment</td>";
                    echo "<td>Hover of this text for a comment</td>";
                echo "</tr>";
                echo "<tr>";
                    echo "<td class='term'>Term</td>";
                    echo "<td>This text has been marked as a term</td>";
                echo "</tr>";
            echo "</tbody>";
        echo "</table>";
    }
	
	/*
	 * This function prints the status bar for the author's view
	 */
	public function printAuthorStatusBar($report)
	{
		if(!$this->isComplete())		// The job is still being examined and so the warnings must be shown
		{
            $settings = new Settings();
            $domain_root = $settings->path_to_domain_root($_SERVER);
			$segs_with_warnings = $report->getInfectedSegments($this->job_id);
			$warningCount = $this->countWarnings($report);
			if($warningCount == 0)
			{
				echo '<p>You have 0 warnings.</p>';
				echo '<p><a class="button" href="'.$domain_root.'/scripts/pass_to_pm.php?job_id='.$this->getJobID().'">Pass on to PM</a></p>';
			} 
			else 
			{
				echo '<p><strong>Scroll down to view the warnings and to edit your text, then re-analyse the document.</strong></p>';
				if($warningCount == 1)
				{
					echo '<p>You have 1 warning, in segment <a href="#seg_'.$segs_with_warnings[0].'">'.$segs_with_warnings[0].'</a>.</p>';
				} 
				else 
				{
					echo '<p>You have '.$warningCount.' warnings, in segments ';
					for($i = 0; $i < count($segs_with_warnings) ; $i++)
					{
						if($i == count($segs_with_warnings) - 1)
						{
							echo '<a href="#seg_'.$segs_with_warnings[$i].'">'.$segs_with_warnings[$i].'</a>';
						} 
						elseif($i == count($segs_with_warnings) - 2)
						{
							echo '<a href="#seg_'.$segs_with_warnings[$i].'">'.$segs_with_warnings[$i].'</a> and ';
						} 
						else 
						{
							echo '<a href="#seg_'.$segs_with_warnings[$i].'">'.$segs_with_warnings[$i].'</a>, ';
						}
					}
					echo '.</p>';
				}
				echo '<p><a class="button" href="'.$domain_root.'/scripts/pass_to_pm.php?job_id='.$this->getJobID().'">Pass on to PM with warnings</a></p>';
			}
		} 
		else 
		{
			if($this->isClosed())
			{
				echo '<p>You completed this job on '.$this->getCompleteDate().'. ';
				echo 'It was finished by the Project Manager on '.$this->getClosedDate().'.</p>';
			} 
			else 
			{
				echo '<p>You completed this job on '.$this->getCompleteDate().'. It is currently under review by the Project Manager.</p>';
			}
		}
	}
	
	/*
	 * This function prints the status bar for the PM's view
	 */
	public function printPMStatusBar($report, $cnlf)
	{
		if(!$this->isClosed())
		{
            $settings = new Settings();
            $domain_root = $settings->path_to_domain_root($_SERVER);
			$warningCount = $this->countWarnings($report);
			$fixedWarnings = $this->fixedWarningsCount($warningCount);
			if($fixedWarnings < 0)
			{
				//this can happen if guidelines are introduced after the job was importeed
				$fixedWarnings = 0;
			}
			echo '<p>'.$fixedWarnings;
			if($fixedWarnings == 1)
			{
				echo ' warning was';
			}
			else
			{
				echo ' warnings were';
			}
			echo ' addressed by the author. '.$warningCount;
			if($warningCount == 1)
			{
				echo ' warning remains';
			}
			else
			{
				echo ' warnings remain';
			}
			echo '.</p>';
			// Print the actions available to the PM
			if($warningCount == 0)
			{
				echo '<p><a class="button" href="'.$domain_root.'/scripts/close_job.php?job_id='.$this->getJobID().'&cnlf='.$cnlf.'">Complete Job & Export</a> or ';
				echo '<a class="button" href="'.$domain_root.'/scripts/send_back.php?job_id='.$this->getJobID().'&cnlf='.$cnlf.'">Send Back</a> to author for more work.</p>';
			} 
			else 
			{
				echo '<p><a class="button" href="'.$domain_root.'/scripts/send_back.php?job_id='.$this->getJobID().'&cnlf='.$cnlf.'">Send back</a> to author for more work or ';
				echo '<a class="button" href="'.$domain_root.'/scripts/close_job.php?job_id='.$this->getJobID().'&cnlf='.$cnlf.'">Complete Job & Export</a> anyway.</p>';
			}
		} 
		else 
		{
			//Thi si for a closed job, it allows the PM to re-export the job but not edit it
			echo '<p><a class="button" href="'.$domain_root.'/pm/export_options.php?job_id='.$this->getJobID().'">Export job</a></p>';
		}
	}
	
	public function getInputXLIFFStr()
	{
		/* For LocConnect jobs, the XLIFF file that was sent by
		 * LocConnect has been stored in the database.
		 */
		$ret = false;
		if ($job_id = $this->getJobID())
		{
			/* Get the XLIFF file from the database. Logically only 
			 * applies to LocConnect jobs. */
			$db = new MySQLHandler();
			$db->init();
			$q = 'SELECT xliff_input
					FROM jobs
					WHERE job_id = '.$db->cleanse($job_id);
			if ($r = $db->Select($q))
			{
				$ret = $r[0]['xliff_input'];
				if (substr($ret, 0, 5) != '<?xml')
				{
					$ret = "<?xml version='1.0' encoding='UTF-8'?>\n".$ret; 
				}
			}
		}
		return $ret;
	}
	
	// Return job_id if successful.
	public static function insert(&$db, $author_name = false, $email = false, $companyName = false, $domain = false, $source_language = 'en', $target_language = false, $original_file = false, $xliff_input = false, $cnlf_id = false)
	{
		$job = array();
		if (!empty($author_name))
		{
			$job['author_name'] = '"'.$db->cleanse($author_name).'"';
		}
		if (!empty($email))
		{
			$job['email_address'] = '"'.$db->cleanse($email).'"';
		}
		if (!empty($companyName))
		{
			$job['company_name'] = '"'.$db->cleanse($companyName).'"';
		}
		if (!empty($domain))
		{
			$job['domain'] = '"'.$db->cleanse($domain).'"';
		}
		if (!empty($source_language))
		{
			$job['source_language'] = '"'.$db->cleanse($source_language).'"';
		}
		if (!empty($target_language))
		{
			$job['target_language'] = '"'.$db->cleanse($target_language).'"';
		}
		if (!empty($original_file))
		{
			$job['original_file'] = '"'.$db->cleanse($original_file).'"';
		}
		if (!empty($xliff_input))
		{
			$job['xliff_input'] = '"'.$db->cleanseHTML($xliff_input).'"';
		}
		if (!empty($cnlf_id))
		{
			$job['CNLF_id'] = '"'.$db->cleanse($cnlf_id).'"';
		}
		return $db->InsertArr('jobs', $job);
	}
}
