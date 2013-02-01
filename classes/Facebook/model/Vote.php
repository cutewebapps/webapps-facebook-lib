<?php


class Facebook_Vote_Table extends DBx_Table
{
    protected $_name = 'facebook_vote';
    protected $_primary = 'fbv_id';
    
    public function findVote( $nUserId, $nVoteId ) 
    {
    	$select = $this->select()
	        ->where( 'fbv_vote_id = ? ', $nVoteId )
	        ->where( 'fbv_user_id = ? ', $nUserId );
	    return $this->fetchRow( $select );
    }
}

class Facebook_Vote_List extends DBx_Table_Rowset
{
}

class Facebook_Vote_Form_Filter extends App_Form_Filter
{
	public function createElements()
    {
        $this->allowFiltering( array( 'fbv_vote_id', 'fbv_item_id', 'fbv_user_id' ) );
    }
}

class Facebook_Vote_Form_Edit extends App_Form_Edit
{
}

class Facebook_Vote extends DBx_Table_Row
{
    public static function getClassName() { return 'Facebook_Vote'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }
    public static function FormClass( $name ) { return self::getClassName().'_Form_'.$name; }
    public static function Form( $name ) { $strClass = self::getClassName().'_Form_'.$name; return new $strClass; }

    protected function _insert()
    {
	$this->fbv_date = date( 'Y-m-d H:i:s' );
	parent::_insert();
    }
}
