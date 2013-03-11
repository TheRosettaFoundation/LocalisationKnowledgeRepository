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

class GlossaryEntry
{
    private $glossary_id;
    private $job_id;
    
    public function GlossaryEntry($glossary_id, $job_id)
    {
        $this->glossary_id = $glossary_id;
        $this->job_id = $job_id;
    }

    public function getGlossaryId()
    {
        return $this->glossary_id;
    }

    public function getJobId()
    {
        return $this->job_id;
    }

    public function getRef()
    {
        $sql = new MySQLHandler();
        $sql->init();
        $q = "SELECT ref
               FROM glossaryEntries
               WHERE glossary_id = ".$this->getGlossaryId();
        $ret = $sql->Select($q);
        return $ret[0][0];
    }

    public function getTerm()
    {
        $sql = new MySQLHandler();
        $sql->init();
        $q = "SELECT term
               FROM glossaryEntries
               WHERE glossary_id = ".$this->getGlossaryId();
        $ret = $sql->Select($q);
        return $ret[0][0];
    }

    public function getTranslation()
    {
        $sql = new MySQLHandler();
        $sql->init();
        $q = "SELECT translation
               FROM glossaryEntries
               WHERE glossary_id = ".$this->getGlossaryId();
        $ret = $sql->Select($q);
        return $ret[0][0];
    }

    public static function insert(&$sql, $job_id, $ref, $term, $translation)
    {
        $data = array();
        $data['job_id'] = $sql->cleanse($job_id);
        $data['ref'] = '\''.$sql->cleanseHTML($ref).'\'';
        $data['term'] = '\''.$sql->cleanseHTML($term).'\'';
        $data['translation'] = '\''.$sql->cleanseHTML($translation).'\'';
        return $sql->InsertArr('glossaryEntries', $data);
    }
}
