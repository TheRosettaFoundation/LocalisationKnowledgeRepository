<?php
/*------------------------------------------------------------------------*
 * © 2010 University of Limerick. All rights reserved. This material may  *
 * not be reproduced, displayed, modified or distributed without the      *
 * express prior written permission of the copyright holder.              *
 *------------------------------------------------------------------------*/

/*
 * A Class responsible for the tasks carried out on stopwords
 * This is basically an interface with the SQL DB
 * @author: David O Carroll
 */
Class Stopword {
	private $stopword_id;
	
	function Stopword($stopword_id)
	{
		$this->stopword_id = $stopword_id;
	}
	
	/*
	 * @returns: the id of the current stopword
	 */
	public function getStopwordID()
	{
		return $this->stopword_id;
	}
	
	/*
	 * Returns the current stopword
	 */
	public function getStopword()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT stopword
				FROM stopwords
				WHERE stopword_id = '.$this->getStopwordID();
		$r = $sql->Select($q);
		return $r[0][0];
	}
	
	/*
	 * Returns the title of the warning associated with this stopword
	 */
	public function getTitleOfWarning()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT title_of_warning
				FROM stopwords
				WHERE stopword_id = '.$this->getStopwordID();
		$r = $sql->Select($q);
		return $r[0][0];
	}
	
	/*
	 * Returns the description of the warning associated with the current stopword
	 */
	public function getWarningDescription()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT warning_description
				FROM stopwords
				WHERE stopword_id = '.$this->getStopwordID();
		$r = $sql->Select($q);
		return $r[0][0];
	}
	
	/*
	 * Returns the type of the current guideline
	 */
	public function getGuidelineType()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT guideline_type
				FROM stopwords
				WHERE stopword_id = '.$this->getStopwordID();
		$r = $sql->Select($q);
		return $r[0][0];
	}
	
	/*
	 * Rteurns true if the current stopword has been enabled
	 */
	public function isEnabled()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'SELECT enabled
				FROM stopwords
				WHERE stopword_id='.$this->getStopwordID();
		$r = $sql->Select($q);
		return ($r[0][0] == 1) ? true : false;
	}
	
	/*
	 * Mark the current stopword as enabled
	 */
	public function enable()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'UPDATE stopwords
				SET enabled = 1
				WHERE stopword_id = '.$this->getStopwordID();
		$sql->Update($q);
	}
	
	/*
	 * Mark the current stopword as not enabled
	 */
	public function disable()
	{
		$sql = new MySQLHandler();
		$sql->init();
		$q = 'UPDATE stopwords
				SET enabled = null
				WHERE stopword_id = '.$this->getStopwordID();
		$sql->Update($q);
	}
}