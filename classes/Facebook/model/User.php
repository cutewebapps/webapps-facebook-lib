<?php


class Facebook_User_Table extends DBx_Table
{
    protected $_name = 'facebook_user';
    protected $_primary = 'fbu_id';

    public function identify( $arrProps )
    {
	$select = $this->select()->where( 'fbu_facebook_id = ?', $arrProps[ 'id' ] );
        $objUser = $this->fetchRow( $select );
        if ( !is_object( $objUser ) ) {
/*
// sample properties
// {"id":"100002480269729","name":"Victor Fokin","first_name":"Victor","last_name":"Fokin","link":"http:\/\/www.facebook.com\/profile.php?id=100002480269729","gender":"male","email":"vicf0kin\u0040gmail.com","timezone":3,"locale":"en_US","verified":true,"updated_time":"2011-06-26T21:11:07+0000"}
// {"id":"1287353657","name":"Florian Auer","first_name":"Florian","last_name":"Auer","link":"http:\/\/www.facebook.com\/Auer.Florian","username":"Auer.Florian","hometown":{"id":"115581378455439","name":"Luzern, Switzerland"},"quotes":"Wer k\u00e4mpft, kann verlieren. Wer nicht k\u00e4mpft, hat schon verloren.","gender":"male","email":"florian.auer\u0040gmx.ch","timezone":2,"locale":"de_DE","verified":true,"updated_time":"2011-06-26T21:21:27+0000"}
*/

            $strHomeTown = isset( $arrProps[ 'hometown' ] ) ? json_encode( $arrProps['hometown'] ) : '';
            $objUser = $this->createRow( array(
                'fbu_facebook_id'   => $arrProps[ 'id' ],
                'fbu_first_name'    => $arrProps[ 'first_name' ],
                'fbu_last_name'     => $arrProps[ 'last_name' ],
                'fbu_email'         => isset( $arrProps[ 'email' ] ) ? $arrProps[ 'email' ] : '',
                'fbu_profile_link'  => isset( $arrProps[ 'link' ] ) ? $arrProps[ 'link' ] : '',
                'fbu_gender'        => $arrProps[ 'gender' ],
                'fbu_locale'        => isset( $arrProps[ 'locale' ] ) ? $arrProps[ 'locale'] : '',
                'fbu_hometown'      => $strHomeTown,
            ) );


            if ( isset( $arrProps[ 'birthday' ] ) ) {
                $arrDate = explode( '/', $arrProps[ 'birthday' ] );
                $objUser->fbu_birthday = $arrDate[ 2 ]. '-'. $arrDate[ 0 ] .'-'. $arrDate[ 1 ];
            }
            $objUser->save();
        }
        return $objUser;
    }
}
        
class Facebook_User_List extends DBx_Table_Rowset
{
}
        
class Facebook_User_Form_Filter extends App_Form_Filter
{
}
        
class Facebook_User_Form_Edit extends App_Form_Edit
{
}
        
        
class Facebook_User extends DBx_Table_Row
{
    public static function getClassName() { return 'Facebook_User'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }
    public static function FormClass( $name ) { return self::getClassName().'_Form_'.$name; }
    public static function Form( $name ) { $strClass = self::getClassName().'_Form_'.$name; return new $strClass; }

    /**
     * 15 digits of profile id
     * @return string
     */
    public function getFacebookId()
    {
        return $this->fbu_facebook_id;
    }
    /**
     * First and Last Name
     * @return string
     */
    public function getName()
    {
        return $this->getFirstName().' '.$this->getLastName();
    }
    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->fbu_first_name;
    }
    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->fbu_last_name;
    }
    /**
     *  Email can be a
     *  @return string
     */
    public function getEmail()
    {
        return $this->fbu_email;
    }
    /**
     * @return boolean
     */
    public function isAnonymousEmail()
    {
        return preg_match( '/@proxymail\.facebook\.com$/i', $this->getEmail() );
    }
    /**
     * @return string
     */
    public function getProfileLink()
    {
        return $this->fbu_link;
    }
    /**
     * @return datetime
     */
    public function getBirthday()
    {
        return $this->fbu_birthday;
    }
    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->fbu_locale;
    }
    /**
     * @return boolean
     */
    public function isWoman()
    {
        return $this->fbu_gender == 'female';
    }
    /**
     * @return boolean 
     */
    public function isMan()
    {
        return $this->fbu_gender == 'male';
    }


    public function _insert()
    {
        $this->fbu_dt_added = date( 'Y-m-d H:i:s' );
        parent::_insert();
    }

    public function _update()
    {
        $this->fbu_dt_modified = date( 'Y-m-d H:i:s');
        parent::_update();
    }

}