<?php

class Facebook_Update extends App_Update
{
    const VERSION = '0.3.1';

    public static function getClassName() { return 'Facebook_Update'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }

    public function isEnabled( $strParam )
    {
        $objConfigFacebook = App_Application::getInstance()->getConfig()->facebook;
        if ( !is_object( $objConfigFacebook ) )
            return false;
        return $objConfigFacebook->$strParam;
    }

    /**
     * @return array
     */
    public function getTables()
    {
        return array(
    	    Facebook_User::TableName(),
            Facebook_Log::TableName(),
            Facebook_Vote::TableName(),
            Facebook_Vote_Rank::TableName()
        );
    }

    public function update()
    {
        //$this->save( '0.0.0' );
	//$this->getDbAdapterWrite()->queryWrite( 'DROP TABLE IF EXISTS facebook_user' );
	//$this->getDbAdapterWrite()->queryWrite( 'DROP TABLE IF EXISTS facebook_log' );
	//$this->getDbAdapterWrite()->queryWrite( 'DROP TABLE IF EXISTS facebook_vote' );
	//$this->getDbAdapterWrite()->queryWrite( 'DROP TABLE IF EXISTS facebook_vote_rank' );
	
	
        if ( $this->isVersionBelow('0.1.0')) {
            $this->_install();
        }
        if ( $this->isVersionBelow( '0.2.0' )) {
    	    $this->_installVoting();
        }
        if ( $this->isVersionBelow( '0.3.0' )) {
    	    $this->_installVotingRank();
        }
        
        if ( $this->isVersionBelow( '0.3.1' )) {
            $tblUser = Facebook_User::Table();
            if ( ! $tblUser->hasColumn( 'fbu_ip' ) ) {
                $tblUser->addColumn( 'fbu_ip','VARCHAR(20) NOT NULL DEFAULT \'xx.xx.xx.xx\' ' );
                Sys_Io::out( 'Adding IP column');
            }
            if ( ! $tblUser->hasColumn( 'fbu_raw' ) ) {
                $tblUser->addColumn( 'fbu_raw','TEXT' );
                Sys_Io::out( 'Adding Facebook Raw info column');
            }
        }



        $this->save( self::VERSION );
    }

    protected function _installVoting()
    {
        if ( !$this->getDbAdapterRead()->hasTable( 'facebook_vote') ) {
            Sys_Io::out( 'Creating Facebook Votes Table' );
            $this->getDbAdapterWrite()->addTableSql( 'facebook_vote', '
                `fbv_id`                INT NOT NULL AUTO_INCREMENT,
                `fbv_user_id`		INT NOT NULL,
                `fbv_item_id`		INT NOT NULL,
                `fbv_vote_id`		INT NOT NULL, -- id of a voting
                `fbv_ip`		CHAR(16) DEFAULT \'\' NOT NULL,
                `fbv_date`		DATETIME NOT NULL DEFAULT \'0000-00-00 00:00:00\',
                
                KEY i_fbv_user_id( fbv_user_id ),
                UNIQUE i_vote ( fbv_vote_id, fbv_user_id )
	    ', 'fbv_id' );
	    }
    }	
	    
	protected function _installVotingRank()
    {
        if ( !$this->getDbAdapterRead()->hasTable( 'facebook_vote_rank') ) {
            Sys_Io::out( 'Creating Facebook Votes Table' );
            $this->getDbAdapterWrite()->addTableSql( 'facebook_vote_rank', '
                `fbvr_id`           INT NOT NULL AUTO_INCREMENT,
                `fbvr_item_id`		INT NOT NULL,
                `fbvr_vote_id`		INT NOT NULL, -- id of a voting
                `fbvr_rank`		    INT NOT NULL DEFAULT -1,
                KEY i_fbvr_item_id( fbvr_item_id ),
                UNIQUE i_vote ( fbvr_vote_id, fbvr_item_id )
	        ', 'fbvr_id' );
	    }
    }	
    
    protected function _install()
    {
        if ( !$this->getDbAdapterRead()->hasTable( 'facebook_user') ) {
            Sys_Io::out( 'Creating Facebook User Table' );
            $this->getDbAdapterWrite()->addTableSql( 'facebook_user', '
                `fbu_id`                INT          NOT NULL AUTO_INCREMENT,
                `fbu_facebook_id`	CHAR(20)     NOT NULL,

                `fbu_first_name`	VARCHAR(255) NOT NULL DEFAULT \'\',
                `fbu_last_name`		VARCHAR(255) NOT NULL DEFAULT \'\',
                `fbu_email`		VARCHAR(255) NOT NULL DEFAULT \'\',
                `fbu_profile_link`	VARCHAR(255) NOT NULL DEFAULT \'\',
                `fbu_gender`		CHAR(100) NOT NULL DEFAULT \'\',
                `fbu_hometown`		VARCHAR(100) NOT NULL DEFAULT \'\',
                `fbu_locale`    	CHAR(100) NOT NULL DEFAULT \'\',
                `fbu_birthday`    	DATETIME NOT NULL DEFAULT \'0000-00-00 00:00:00\',

                `fbu_dt_added`      DATETIME     NOT NULL DEFAULT \'0000-00-00 00:00:00\',
                `fbu_dt_modified`   DATETIME     NOT NULL DEFAULT \'0000-00-00 00:00:00\',
                
                PRIMARY KEY (`fbu_id`),
                KEY i_fbu_email( fbu_email ),
                KEY i_fbu_hometown (fbu_hometown )
                '
            );
        }

        if ( !$this->getDbAdapterRead()->hasTable( 'facebook_log') ) {
            Sys_Io::out( 'Creating Facebook Log Table' );
            $this->getDbAdapterWrite()->addTableSql( 'facebook_log', '
                `fbl_id`            INT(10)      UNSIGNED NOT NULL AUTO_INCREMENT,
		        `fbl_user_id`       INT	 	 NOT NULL,
		        `fbl_status`	    INT(4)	 UNSIGNED NOT NULL,
                `fbl_date`          DATETIME     NOT NULL DEFAULT \'0000-00-00 00:00:00\',
                PRIMARY KEY (`fbl_id`),
                KEY i_fbl_user_id( fbl_user_id ),
                KEY i_fbl_date( fbl_date ) '
            );
        }
    }
}
