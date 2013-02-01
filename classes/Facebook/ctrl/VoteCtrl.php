<?php

class Facebook_VoteCtrl extends App_DbTableCtrl
{
    public function addAction()
    {
		$tblVote = Facebook_Vote::Table();
		$objSession = new App_Session_Namespace( 'facebook' );
		
		if ( is_object( $objSession )) {
			$nUserId = $objSession->user->fbu_id;
			$nVoteId = $this->_getIntParam( 'fbv_vote_id', 1 );

			if ( !is_object( $tblVote->findVote( $nUserId, $nVoteId ) )) {
				$objVote = $tblVote->createRow(); 
				$objVote->fbv_user_id = $objSession->user->fbu_id;
				$objVote->fbv_item_id = $this->_getParam( 'fbv_item_id', -1 );
				$objVote->fbv_vote_id = $nVoteId;
				$objVote->fbv_ip = $_SERVER['REMOTE_ADDR'];
				$objVote->fbv_date = date( 'Y-m-d H:i:s' );
				$objVote->save();
				// Sys_Io::out( 'added' );
			}
			// Sys_Io::out( 'continued' );
		}
		$this->view->return = $this->_getParam( 'return' );
    }
    
    /** one can delete his own vote */
    public function cancelAction() 
    {
    		$tblVote = Facebook_Vote::Table();
		$objSession = new App_Session_Namespace( 'facebook' );
		
		if ( is_object( $objSession )) {
			$nUserId = $objSession->user->fbu_id;
			$nVoteId = $this->_getIntParam( 'fbv_vote_id', 1 );
			
			$objVote = $tblVote->findVote( $nUserId, $nVoteId );
			if ( is_object( $objVote ) ) {
    				$objVote->delete();
			}
		}
		$this->view->return = $this->_getParam( 'return' );
    }
    
    
    public function statAction()
    {
		$tblVote = Facebook_Vote::Table();
		$_select = $tblVote->select()
			->from( Facebook_Vote::TableName(), array( 'fbv_item_id','COUNT(*) as Total' ) );


		if ( $this->_getParam( 'fbv_vote_id' ) )
			$_select->where( 'fbv_vote_id = ?', $this->_getIntParam('fbv_vote_id',0) );
			
		if ( $this->_getParam( 'fbv_item_id' ) ) {
			$_select->where( 'fbv_item_id = ?', $this->_getIntParam('fbv_item_id',0) );
		}
		if ( $this->_getParam( 'fbv_user_id' ) ) {
			$_select->where( 'fbv_user_id = ?', $this->_getIntParam('fbv_user_id',0) );
		}
		
		if ( $this->_getParam( 'recent' ) ) {
			$_select->order( 'fbv_date DESC' );
			$this->view->recent = 1;
		}
		if ( $this->_getParam( 'top' ) ) {
			$_select->order( 'Total DESC' );
			$this->view->top = 1;
		}
		
		$_select->group( 'fbv_item_id' );
		$_select->limit( $this->_getIntParam( 'results', 10 ), 0);
		
		//echo $_select;
		
		$lstObjects = $tblVote->fetchAll( $_select );
		$this->view->listObjects = $lstObjects;
		if ( count( $lstObjects ) > 0 ) {
			$this->view->result = $lstObjects->current()->Total;
		} else {
			$this->view->result = 0;
		}
	}
}
