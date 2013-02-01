<?php
class Facebook_ConnectHelper extends App_ViewHelper_Abstract
{
    public function connect( $xfbml = true, $appID = '' )
    {
        //$strScheme = 'http';
        //if ( Sys_Mode::isSsl() ) $strScheme = 'https';

        $arrExtra = array();
        if ( $xfbml ) $arrExtra [ 'xfbml' ] = 'xfbml=1';
        if ( $appID != '' )
            $arrExtra[ 'appID'] = 'appID='.$appID;

        $strExtra = '';
        if ( count( $arrExtra )) $strExtra = '#'.implode( "&",$arrExtra );

        // echo '<!-- facebook connect -->';
        $this->getView()->broker()->headScript()->append(
                '//connect.facebook.net/en_US/all.js'. $strExtra );
    }
}

