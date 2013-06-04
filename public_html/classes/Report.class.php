<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/

/*
 * Runs and returns reports based on the guidelines selected by the Project Manager. 
 * The guidelines checked are enabled/disabled through values in the database table 'reports'.
 *
 * @author: David O Carroll
 * @edited: Eoin Ó Conchúir
 */
class Report
{
	private $job;
	private $reports_run = 0;
	private $recommendations; // an array of recommendations for all segments.
	
	function Report($job)
	{
		$this->job = $job;
		$this->executeReport(); 	//run report immediately
	}

	/*
	 * Runs all required reports. After running, the object can be queried for report values
	 * for each specific segment.
	 *
	 * @returns Int value of number of reports run.
	 */
	function executeReport()
	{
		//check if any segment has been broken in two
		$this->job->testSegments();
		if($segments = $this->job->getSegments())
		{
			//assume there are no warnings
			$sql = new MySQLHandler();
			$sql->init();
			$q = 'UPDATE segments
					SET has_warning = 0
					WHERE job_id = '.$this->job->getJobID();
			$sql->Update($q);
			//for every segment...
			foreach($segments as $segment)
			{
				// Check if the current segment has been edited
				$segment->checkEdited();
				// Run all enabled reports on this segment.
				if($report_names = $this->enabledReports())
				{
					//...run all reports
					foreach($report_names as $report)
					{
						$this->runSubReport($report, $segment);
					}
				}
				//run a check on the stopwords
				if($stopwords = $this->getStopwords())
				{
					$this->runStopwordCheck($stopwords, $segment);
				}
			}
			return $this->reports_run;
		}
	}
	
	/*
	 * used to count the warnings found
	 * NOTE: must be run after execute report for accurate results
	 */
	function countWarnings()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT SUM(has_warning)
				FROM segments
				WHERE job_id = '.$this->job->getJobID();
		$r = $sql->Select($q);
		return $r[0][0];
	}
	
	/*
	 * Searches in the database for the name of all eneabled reports, if any.
	 * Eoin: Removing special treatment of LocConnect jobs, as no reports are
	 * running against LocConnect jobs. There's a bug somewhere.
	 */
	private function enabledReports()
	{
		//$CNLF_id = $this->job->getCNLFid();
		$sql = new MySQLHandler();
		$sql->init();
		$report_names = array();
		$q = 'SELECT machine_name
					FROM reports
					WHERE enabled = 1';
		if ($r = $sql->Select($q))
		{
			foreach($r as $row)
			{
				$report_names[] = $row['machine_name'];
			}
		}
		/*
		// If it's a regular job get the reports from the job-independent reports table.
		if($CNLF_id == null)
		{
			$q = 'SELECT machine_name
					FROM reports
					WHERE enabled = 1';
			$r = $sql->Select($q);
			foreach($r as $row)
			{
				$report_names[] = $row['machine_name'];
			}
		}
		else
		{
			//otherwise get the guidelines specific to this LocConnect job
			$q = 'SELECT machine_name
					FROM cnlf_guidelines
					WHERE CNLF_id = "'.$CNLF_id.'"
					AND type = "report"';
			$r = $sql->Select($q);
			foreach($r as $row)
			{
				$report_names[] = $row['machine_name'];
			}
		}
		*/
		return (count($report_names)>0) ? $report_names : false;
	}
	
	/*
	 * Turn on a report
	 */
	static function enableReport($report_id)
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'UPDATE reports
				SET enabled = 1
				WHERE report_id = '.$report_id;
		$sql->Update($q);
	}
	
	/*
	 * Turn off a report
	 */
	static function disableReport($report_id)
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'UPDATE reports
				SET enabled = null
				WHERE report_id = '.$report_id;
		$sql->Update($q);
	}
	
	/*
	 * Check if a report is enabled
	 */
	static function isEnabled($report_id)
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT *
				FROM reports
				WHERE report_id = '.$report_id.'
				AND enabled = 1';
		$r = $sql->Select($q);
		return (count($r) > 0) ? true : false;
	}
	
	/*
	 * Run the requested report.
	 * @params report_name : name of the report with underscores, as found in 'reports' database table.
	 */
	private function runSubReport($report_name, $segment)
	{
		if ($report_name == 'max_sentence_length')
		{
			$this->runMaxLengthSentences($report_name, $segment);
		}
		if($report_name == 'max_length_procedural_sentence')
		{
			$this->runMaxLengthSentences($report_name, $segment);
		}
		elseif($report_name == 'acronyms_and_abbreviations')
		{
			$this->runRegexCheck('acronyms_and_abbreviations', $segment);
		}
		elseif($report_name == 'inches_and_feet')
		{
			$this->runRegexCheck('inches_and_feet', $segment);
		}
		elseif($report_name == 'number_with_apostrophe')
		{
			$this->runRegexCheck('number_with_apostrophe', $segment);
		}
		elseif($report_name == 'firstly_and_secondly')
		{
			$this->runRegexCheck('firstly_and_secondly', $segment);
		}
		elseif($report_name == 'time_format_check')
		{
			$this->runRegexCheck('time_format_check', $segment);
		}
		elseif($report_name == 'GMT_check')
		{
			$this->runRegexCheck('GMT_check', $segment);
		}
		elseif($report_name == 'duplicate_words')
		{
			$this->runRegexCheck($report_name, $segment);
		}
		elseif($report_name == 'capitalise_first_letter')
		{
			$this->runStartOfSentenceRegexCheck('capitalise_first_letter', $segment);
		}
		elseif($report_name == 'numerical_first_letter')
		{
			$this->runStartOfSentenceRegexCheck('numerical_first_letter', $segment);
		}
		elseif($report_name == 'active_voice')
		{
			$this->runStartOfSentenceRegexCheck($report_name, $segment);
		}
		elseif($report_name == 'url_check')
		{
			$this->runURLCheck('url_check', $segment);
		}
		elseif($report_name == 'personal_pronouns')
		{
			$this->runPersonalPronounCheck('personal_pronoun', $segment);
		}
		elseif($report_name == 'phrasal_verbs')
		{
			$this->runPhrasalVerbCheck($report_name, $segment);
		}
		return true;
	}
	
	/*
	 * Run a check on the number of words in a sentence.
	 */
	private function runMaxLengthSentences($report_name, $segment)
	{
		$max_length = $this->reportValue($report_name);
		// Analyse the length of the sentence.
		if (str_word_count($segment->getTargetRaw()) > $max_length)
		{
			// Need to provide a recommendation, as the sentence is too long.
			$params = array(
							'length' =>	str_word_count($segment->getTargetRaw()),
							'max_length' => $max_length
						);
			$recommendation = $this->generateRecommendation($report_name, $params);
			$this->saveRecommendation($segment->getSegmentID(), $recommendation);
			$sql = new MySQLHandler();
			$sql->init();
			$q = 'UPDATE segments
					SET has_warning = has_warning + 1
					WHERE segment_id = '.$segment->getSegmentID().'
					AND job_id = '.$this->job->getJobID();
			$sql->Update($q);
		}
		$this->reports_run++;
	}
	
	/*
	 * Run a check to see if the guideline described by a regex in the DB is in the current segment
	 */
	private function runRegexCheck($report_name, $segment)
	{
        $params = array();
		$regex = $this->reportValue($report_name);
		if($segment->containsRegex($regex))
		{
			$recommendation = $this->generateRecommendation($report_name, $params);	//$params is empty
			$this->saveRecommendation($segment->getSegmentID(), $recommendation);
			$sql = new MySQLHandler();
			$sql->init();
			$q = 'UPDATE segments
					SET has_warning = has_warning + 1
					WHERE segment_id = '.$segment->getSegmentID().'
					AND job_id = '.$this->job->getJobID();
			$sql->Update($q);
		}
		$this->reports_run++;
	}
	
	/*
	 * Runs a check to see if a regex (Contained in the DB) is at the start of a segment
	 */
	private function runStartOfSentenceRegexCheck($report_name, $segment)
	{
        $params = array();
		$regex = $this->reportValue($report_name);
		if(preg_match('/'.$regex.'/', ltrim($segment->getTargetRaw())))
		{
//echo '/'.$regex.'/'.'.'.ltrim($segment->getTargetRaw()).'.';
			$recommendation = $this->generateRecommendation($report_name, $params);	//$params is empty
			$this->saveRecommendation($segment->getSegmentID(), $recommendation);
			$sql = new MySQLHandler();
			$sql->init();
			$q = 'UPDATE segments
					SET has_warning = has_warning + 1
					WHERE segment_id = '.$segment->getSegmentID().'
					AND job_id = '.$this->job->getJobID();
			$sql->Update($q);
		}
		$this->reports_run++;
	}
	
	/*
	 * Run a report to check if urls are written in lower case
	 */
	private function runURLCheck($report_name, $segment)
	{
		if(stripos($segment->getTargetRaw(), 'www.') != false)
		{
			// Where the url begins
			$startPos = stripos($segment->getTargetRaw(), 'www.');
			// Where the url ends
			$endPos = strpos($segment->getTargetRaw(), " ", $startPos);
			if($endPos != false)
			{
				// the url is in the middle/start, cut it out
				$url = substr($segment->getTargetRaw(), $startPos, $endPos - $startPos);
			}
			else
			{
				// the url is at the end of the segment
				$url = substr($segment->getTargetRaw(), $startPos);
			}
			// to check if there are upper case letters convert it to lower and check if its different
			if($url != strtolower($url))
			{
				// generate a recommendation
				$recommendation = $this->generateRecommendation($report_name, $params); //$params is empty
				$this->saveRecommendation($segment->getSegmentID(), $recommendation);
				$sql = new MySQLHandler();
				$sql->init();
				$q = 'UPDATE segments
						SET has_warning = has_warning + 1
						WHERE segment_id = '.$segment->getSegmentID().'
						AND job_id = '.$this->job->getJobID();
				$sql->Update($q);
			}
		}
		$this->reports_run++;
	}
	
	/*
	 * Run a check to see if the current segment contains one of he, she, him, her, his or hers
	 */
	private function runPersonalPronounCheck($report_name, $segment)
	{
		$searchWords = Array(' he ', ' she ', ' him ', ' her ', ' his ', ' hers ');
		foreach($searchWords as $word)
		{
			if(stripos($segment->getTargetRaw(), $word) != false)
			{
				$recommendation = $this->generateRecommendation($report_name, $params);	//$params is empty
				$this->saveRecommendation($segment->getSegmentID(), $recommendation);
				$sql = new MySQLHandler();
				$sql->init();
				$q = 'UPDATE segments
						SET has_warning = has_warning + 1
						WHERE segment_id = '.$segment->getSegmentID().'
						AND job_id = '.$this->job->getJobID();
				$sql->Update($q);
				$this->reports_run++;
				return;
			}
		}
		$this->reports_run++;
	}
	
	/*
	 * A report that checks if certain phrases (located in the DB) are in the segment
	 */
	private function runPhrasalVerbCheck($report_name, $segment)
	{
		$phrases = $this->reportValue($report_name);
		$phrases = explode(', ', $phrases);
		foreach($phrases as $phrase)
		{
			if(stripos($segment->getTargetRaw(), $phrase) != false)
			{
				$params = array('word' => $phrase);
				$recommendation = $this->generateRecommendation($report_name, $params);
				$this->saveRecommendation($segment->getSegmentID(), $recommendation);
				$sql = new MySQLHandler();
				$sql->init();
				$q = 'UPDATE segments
						SET has_warning = has_warning + 1
						WHERE segment_id = '.$segment->getSegmentID().'
						AND job_id = '.$this->job->getJobID();
				$sql->Update($q);
				$this->reports_run++;
				return;
			}
		}
		$this->reports_run++;
	}
	
	/*
	 * return an array of all reports
	 */
	static function getAllReports()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT *
				FROM reports';
		$reports = $sql->Select($q);
		return (count($reports) > 0) ? $reports : false;
	}
	
	/*
	 * This is a static function to get a list of stopwords when no report object exists
	 */
	static function getAllStopwords()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT *
				FROM stopwords';
		$row = $sql->Select($q);			//get the list from SQL DB
		$stopwords = array();
		foreach($row as $r)
		{
			// Convert the rows from the database into stopword objects
			$stopwords[$r['stopword_id']] = new Stopword($r['stopword_id']);
		}
		return (count($stopwords) > 0) ? $stopwords : false;
	}
	
	/*
	 * This returns an array of stopwords taken from the database
	 * Eoin removed special treatment of LocConnect jobs, as some bug
	 * meant that no reports were running against LocConnect jobs.
	 */
	private function getStopwords()
	{
		$sql = new MySQLHandler();
		$sql->init();
		//$CNLF_id = $this->job->getCNLFid();
		$stopwords = array();
		$q = 'SELECT *
				FROM stopwords
				WHERE enabled = 1';
		if ($r = $sql->Select($q))			//get the list from SQL DB
		{
			foreach($r as $row)
			{
				$stopwords[] = new Stopword($row['stopword_id']);
			}
		}
		/*
		if($CNLF_id == null)
		{
			// its not a cnlf job
			$q = 'SELECT *
					FROM stopwords
					WHERE enabled = 1';
			$row = $sql->Select($q);			//get the list from SQL DB
			foreach($row as $r)
			{
				$stopwords[] = new Stopword($r['stopword_id']);
			}
		}
		else
		{
			//retrieve the custom guidelines for this job
			$q = 'SELECT machine_name
					FROM cnlf_guidelines
					WHERE CNLF_id = "'.$CNLF_id.'"
					AND type = "stopword"';
			$row = $sql->Select($q);
			foreach($row as $r)
			{
				$q = 'SELECT stopword_id
						FROM stopwords
						WHERE stopword = "'.$r['machine_name'].'"';
				$s = $sql->Select($q);
				$stopwords[] = new Stopword($s[0]['stopword_id']);
			}
		}
		*/
		return (count($stopwords) > 0) ? $stopwords : false;
	}
	
	/*
	 * This searches the current segment to check if it contains a stopword and generates a recommendation if it does
	 * @params stopwords: is a variable containing all the stopwords in the database
	 */
	private function runStopwordCheck($stopwords, $segment)
	{
		$sql = new MySQLHandler();
		$sql->init();
		$has_warning = 0;
		foreach($stopwords as $stopword)
		{
			$content = $segment->getTargetRaw();
			if(($wordpos = stripos($content, $stopword->getStopword())) !== false)
			{
				//this code enures that the word your looking for is not contained within another word
				//not used
				/*if((($wordpos == 0) && (substr($content, strlen($stopword->getStopword()), 1) == ' ')) || 
					(($wordpos == strlen($content) - strlen($stopword->getStopword())) && (substr($content, $wordpos - 1, 1) == ' ')) ||
					((substr($content, $wordpos - 1, 1) == ' ') && (substr($content, $wordpos + strlen($stopword->getStopword()), 1) == ' ')))
				{*/
				$recommendation = '<h3>'.$stopword->getTitleOfWarning().'</h3><p>'.$stopword->getWarningDescription().'</p>';
				$this->saveRecommendation($segment->getSegmentID(), $recommendation);
				$has_warning = 1;				
			}
		}
		//Note the has_warning field was reset from the first report
		//to reset it again would undo the previous reports
		if($has_warning != 0)
		{
			$q = 'UPDATE segments
					SET has_warning = '.$has_warning.'
					WHERE job_id = '.$this->job->getJobID().'
					AND segment_id = '.$segment->getSegmentID();
			$sql->Update($q);
		}
	}	
	
	/*
	 * @params value_id: is a parameter value specific to a certain report. For example, 
	 *						the number of max words for the "max_sentence_length" sub-report.
	 * @returns value from DB.
	 */
	// The configuration value for a specific report. e.g. the max length of a sentence.
	private function reportValue($value_id)
	{
		$q = 'SELECT value
				FROM report_vals
				WHERE id = \''.$value_id.'\'
				LIMIT 1';
		$sql = new MySQLHandler();
		$sql->init();
		$r = $sql->Select($q);
		$value = false;
		if ($r)
		{
			$value = $r[0]['value'];
		}
		return $value;
	}
		
	/*
	 * Find the file containing the report recommendation
	 * @return: the HTML taken from the report_name.php file
	 */
	private function generateRecommendation($report_name, $params)
	{
		$ret = false;
		$filename = __DIR__.'/../includes/recommendations/'.$report_name.'.php';
		if (is_file($filename))
		{
			ob_start();
			include $filename;
			$contents = ob_get_contents();
			ob_end_clean();
			$ret = $contents;
		}
		else
		{
			echo 'Error: Can\'t get the text for the recommendation expected in '.$filename;
		}
		return $ret;
	}
	
	/*
	 * Store in array for later display to the user.
	 */
	private function saveRecommendation($segment_id, $recommendation)
	{
		$this->recommendations[$segment_id][] = $recommendation;
	}
	
	/*
	 * To be called after reports have been executed.
	 */
	private function segmentRecommendations($segment_id)
	{
		return isset($this->recommendations[$segment_id]) ? $this->recommendations[$segment_id] : false;
	}
	
	/*
	 * TODO comments
	 */
	public function getInfectedSegments($job_id)
	{
		$infected = array();
		$i = 0;
		
		if($segments = $this->job->getSegments())
		{
			foreach($segments as $segment)
			{
				if($segment->hasWarning())
				{
					$infected[$i++] = $segment->getSegmentID();
				}
			}
			return $infected;
		}
	}
	
	/*
	 * Public method. Outputs HTML code to display recommendation tooltips.
	 * Users qTip plugin for jQuery for displaying tooltips.
	 */
	public function printTooltips()
	{
		if (count($this->recommendations)>0)
		{
			echo '
			<script class="example" type="text/javascript">
			// Create the tooltips only on document load
			$(document).ready(function() 
			{
			';
			foreach($this->recommendations as $segment_id => $segment_recommendations)
			{
				$str = '';
				$content = '';
				// Get all recommendations for this segment.
				$i = 0;
				foreach($segment_recommendations as $key => $recommendation)
				{
					if ($i>0)
					{
						$content .= '<hr />';
					}
					$content .= $recommendation;
				}
				$str = '
					// Match all link elements with href attributes within the content div
					//$(\'#tooltip_'.$segment_id.'\').addClass(\'highlight\');
					$(\'#tooltip_'.$segment_id.'\').qtip(
					{
						content: '.json_encode($content).',
						position: {
							corner: {
								tooltip: \'leftTop\', // Use the corner...
								target: \'rightTop\' // ...and opposite corner
							}
						},
						style: {
							border: {
								width: 5,
								radius: 10
							},
							width: {
								min: \'50px\',
								max: \'280px\'
							},
							padding: 10, 
							textAlign: \'left\',
							lineHeight: \'1.3em\',
							tip: true, // Give it a speech bubble tip with automatic corner detection
							name: \'cream\'
						},
						hide: {
							fixed: true
						}
					});				
				';
				echo $str;
			}
			echo '
				});
				</script>';
		}
	}
}
