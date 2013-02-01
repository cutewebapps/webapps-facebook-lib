<?php


class Facebook_Vote_Rank_Table extends DBx_Table
{
    protected $_name = 'facebook_vote_rank';
    protected $_primary = 'fbvr_id';
    
    public function findById( $nItemId, $nVoteId ) 
    {
	    $select = $this->select()
	        ->where( 'fbvr_item_id = ? ', $nItemId )
	        ->where( 'fbvr_vote_id = ? ', $nVoteId );
	    return $this->fetchRow( $select );
    }

    public function recalc( $nVoteId = 1 )
    {
        $this->getAdapterWrite()->queryWrite( 'DELETE FROM '.$this->_name.' WHERE fbvr_vote_id = '.$nVoteId  );

        $tblVote = Facebook_Vote::Table();
        $_select = $tblVote->select()
            ->from( Facebook_Vote::TableName(), array( 'fbv_item_id','COUNT(*) as Total' ) )
            ->where( 'fbv_vote_id = ?', $nVoteId )
            ->group( 'fbv_item_id' )
            ->order( 'Total DESC' )
			->limit( 20, 0 );
        $lstObjects = $tblVote->fetchAll( $_select );
        $nIterator = 1;
        foreach ( $lstObjects as $objItem ) {
            $objRecord = $this->createRow();
            $objRecord->fbvr_item_id = $objItem->fbv_item_id;
            $objRecord->fbvr_vote_id = $nVoteId;
            $objRecord->fbvr_rank = $nIterator++;
            $objRecord->save();
        }
    }
}

class Facebook_Vote_Rank_List extends DBx_Table_Rowset
{
}

class Facebook_Vote_Rank_Form_Filter extends App_Form_Filter
{
    public function createElements()
    {
        $this->allowFiltering( array( 'fbvr_vote_id', 'fbvr_item_id', 'fbvr_rank' ) );
    }
}

class Facebook_Vote_Rank_Form_Edit extends App_Form_Edit
{
}

class Facebook_Vote_Rank extends DBx_Table_Row
{
    public static function getClassName() { return 'Facebook_Vote_Rank'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }
    public static function FormClass( $name ) { return self::getClassName().'_Form_'.$name; }
    public static function Form( $name ) { $strClass = self::getClassName().'_Form_'.$name; return new $strClass; }

   
}
