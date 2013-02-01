<?php


class Facebook_Log_Table extends DBx_Table
{
    protected $_name = 'facebook_log';
    protected $_primary = 'fbl_id';
}

class Facebook_Log_List extends DBx_Table_Rowset
{
}

class Facebook_Log_Form_Filter extends App_Form_Filter
{
}

class Facebook_Log_Form_Edit extends App_Form_Edit
{
}

class Facebook_Log extends DBx_Table_Row
{
    public static function getClassName() { return 'Facebook_Log'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }
    public static function FormClass( $name ) { return self::getClassName().'_Form_'.$name; }
    public static function Form( $name ) { $strClass = self::getClassName().'_Form_'.$name; return new $strClass; }

    const STATUS_SUCCESS = 1;
    const STATUS_FAIL    = 0;

    const GENDER_WOMAN = 'W';
    const GENDER_MAN   = 'M';
    

    public static $arrStatuses = array(
        self::STATUS_SUCCESS => 'Success',
        self::STATUS_FAIL    => 'Fail',
    );

    public static $arrGender = array(
        self::GENDER_WOMAN => 'Women',
        self::GENDER_MAN   => 'Men',
    );

    public function getStatus()
    {
        return ($this->fbl_status == 1) ? 'Success' : 'Fail';
    }

    protected function _insert()
    {
	$this->fbl_date = date( 'Y-m-d H:i:s' );
	parent::_insert();
    }
}