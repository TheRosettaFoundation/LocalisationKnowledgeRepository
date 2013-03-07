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

class AnnotatorsRef
{
    private $ref_id;
    private $job_id;

    public function AnnotatorsRef($ref_id, $job_id)
    {
        $this->ref_id = $ref_id;
        $this->job_id = $job_id;
    }

    public function getRefId()
    {
        return $this->ref_id;
    }

    public function getJobId()
    {
        return $this->job_id;
    }

    public function getFileId()
    {
        $sql = new MySQLHandler();
        $sql->init();
        $q = "SELECT file_id
                FROM annotatorsRefs
                WHERE ref_id = ".$this->getRefId();
        $ret = $sql->Select($q);
        return $ret[0][0];
    }

    public function getRef()
    {
        $sql = new MySQLHandler();
        $sql->init();
        $q = "SELECT ref
                FROM annotatorsRefs
                WHERE ref_id = ".$this->getRefId();
        $ret = $sql->Select($q);
        return $ret[0][0];
    }

    public function getCategory()
    {
        $sql = new MySQLHandler();
        $sql->init();
        $q = "SELECT category
                FROM annotatorsRefs
                WHERE ref_id = ".$this->getRefId();
        $ret = $sql->Select($q);
        return $ret[0][0];
    }

    public static function exists(&$sql, $job_id, $file_id, $ref, $category)
    {
        $ret = false;
        $q = "SELECT *
                FROM annotatorsRefs
                WHERE job_id = ".$sql->cleanse($job_id)."
                AND file_id = ".$sql->cleanse($file_id)."
                AND ref = '".$sql->cleanseHTML($ref)."'
                AND category = '".$sql->cleanse($category)."'";
        $result = $sql->Select($q);
        if ($result[0][0] != NULL) {
            $ret = true;
        }
        return $ret;
    }

    public static function insert(&$sql, $job_id, $file_id, $ref, $category)
    {
        $data = array();
        $data['job_id'] = $sql->cleanse($job_id);
        $data['file_id'] = $sql->cleanse($file_id);
        $data['ref'] = '\''.$sql->cleanseHTML($ref).'\'';
        $data['category'] = '\''.$sql->cleanseHTML($category).'\'';
        return $sql->InsertArr('annotatorsRefs', $data);
    }
}
