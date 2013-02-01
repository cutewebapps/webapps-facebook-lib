<?php

class Facebook_IndexCtrl extends App_AbstractCtrl
{
    /**
     * empty action for static pages (tabs and canvases)
     * @return void
     */
    public function pageAction()
    {
        if ( $this->_getParam( 'application' ) == '' )
            throw new Facebook_Exception( 'Application parameter was not specified' );

        $objApplication = new Facebook_Application( $this->_getParam( 'application' ) );
        $this->view->objFacebookApp = $objApplication;
    }

    protected function _fetchUrl( $url )
    {
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
	$res = curl_exec( $ch );
	curl_close( $ch );
	return $res;
    }
    /**
     * please set up redirect in the template
     * @return void
     */
    public function authAction()
    {
        if ( $this->_getParam( 'application' ) == '' )
            throw new Facebook_Exception( 'Application parameter was not specified' );

        $objApplication = new Facebook_Application( $this->_getParam( 'application' ) );
        $this->view->objFacebookApp = $objApplication;
        $this->view->strRedirectUrl = '';

        $objSession  = new App_Session_Namespace( 'facebook' );

        // Sys_Io::out( 'Facebook App Initialized' );

        // $objStorage = new App_Auth_Storage_Session( $strAuthSessionStorage );
        // App_Auth::getInstance()->setStorage( $objStorage );

        if ( is_object($objSession->user) ) {
            // Sys_Io::out( 'ALREADY REGISTERED' );
            // $this->view->strRedirectUrl = '/';
        } else {

            // sample parameters when user denies access are:
            //   error_reason=user_denied
            //  &error=access_denied
            //  &error_description=The+user+denied+your+request.
            if ( $this->_getParam('error' ) != '' ) {
                    $this->view->error = $this->_getParam('error');
                    $this->view->error_reason      = $this->_getParam('error_reason');
                    $this->view->error_description = $this->_getParam('error_description');
                    return ;
            }

            $paramFacebookCode = $this->_getParam( 'code');
            if ( $paramFacebookCode == '' ) {
                
                throw new Facebook_Exception( 'Code was not received' );
                
                // redirecting to dialog of authorization
                $strRedirectUrl = implode( '&', array(
                    'https://www.facebook.com/dialog/oauth?scope=' . $objApplication->getScope() ,
                    'client_id=' . $objApplication->getId(),
                    'redirect_uri='. rawurlencode( $objApplication->getUri() ) ) );
                $this->view->strRedirectUrl = $strRedirectUrl;

            } else {
                // Sys_Io::out( 'Code: '. $paramFacebookCode );

                // accepting data after dialog of authorization
                $strTokenUrl = implode( '&', array(
                    'https://graph.facebook.com/oauth/access_token?client_id=' . $objApplication->getId(),
                    'redirect_uri=' . rawurlencode( $objApplication->getUri() ),
                    'client_secret=' . $objApplication->getSecret(),
                    'code=' . $paramFacebookCode ) );

                // requesting Graph API for details
                $strAccessToken = $this->_fetchUrl( $strTokenUrl );

		if ( substr( $strAccessToken, 0, 1 ) == '{' ) {
		    $dataInsteadOfAccessToken = json_decode( $strAccessToken );
                    // Sys_Debug::dumpDie( $strTokenUrl );
		    throw new Facebook_Exception( 'Oauth Error: '. $dataInsteadOfAccessToken->error->message );
		    // Sys_Debug::dump( $dataInsteadOfAccessToken );
		}
        
                
                $strGraphUrl = 'https://graph.facebook.com/me?' . $strAccessToken;
                // Sys_Io::out( 'ACCESS TOKEN: '. $strAccessToken );
                // Sys_Io::out( 'GRAPH URL : '. $strGraphUrl );
                
                
                $strGraphData = $this->_fetchUrl( $strGraphUrl );

$dir = new Sys_Dir( App_Application::getInstance()->getConfig()->cache_dir.'/facebook' );
$fileLog = new Sys_File( $dir->getName().'/'.time().'.txt' );
$fileLog->append( date('Y-m-d H:i:s' )."\t".$strGraphData."\n"  );

                $this->view->lstProps = json_decode( $strGraphData );
		$arrProps = array();
                foreach ($this->view->lstProps as $strKey => $strValue ) $arrProps[ $strKey ] = $strValue;
                // Sys_Debug::dump ( $arrProps );

                $objFacebookUser = Facebook_User::Table()->identify( $arrProps );
                $this->view->objFacebookUser = $objFacebookUser;
                $objSession->user = $objFacebookUser;
                $objSession->code = $paramFacebookCode;

                $objFbLogs = Facebook_Log::Table()->createRow();
                $objFbLogs->fbl_user_id = $objFacebookUser->fbu_id;
                $objFbLogs->fbl_status = Facebook_Log::STATUS_SUCCESS;
                $objFbLogs->save();
            }
            // Sys_Io::out( 'Syssessfully Registered' );
        }
    }
    public function logoutAction()
    {
        $objSession  = new App_Session_Namespace( 'facebook' );
        $objSession->user = null;
        $objSession->code = null;
    }
    
    /**
     * the errors can be handled by the template
     * @return void
     */
    public function errorAction()
    {
        $this->view->strErrorReason       = $this->_getParam( 'error_reason' );
        $this->view->strErrorCode         = $this->_getParam( 'error' );
        $this->view->strErrorDescription  = $this->_getParam( 'error_description' );
    }


    public function fbauthAction()
    {
        if ( $this->_getParam( 'application' ) == '' )
            throw new Facebook_Exception( 'Application parameter was not specified' );

        $objApplication = new Facebook_Application( $this->_getParam( 'application' ) );
        $this->view->objFacebookApp = $objApplication;
        $objSession  = new App_Session_Namespace( 'facebook' );
        if ( ! is_object($objSession->user) ) {
            // if not logged in before,

            $objFacebookUser = Facebook_User::Table()->identify( $this->_getParam( 'user') );
            $objFacebookUser->fbu_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '?';
            $objFacebookUser->fbu_raw = serialize( $this->_getParam( 'user') );
            $objFacebookUser->save();

            $this->view->objFacebookUser = $objFacebookUser;
            $objSession->user = $objFacebookUser;
            $objSession->access_token = $this->_getParam('accessToken');
            $objSession->signed_request = $this->_getParam('signedRequest');

            $objFbLogs = Facebook_Log::Table()->createRow();
            $objFbLogs->fbl_user_id = $objFacebookUser->fbu_id;
            $objFbLogs->fbl_status = Facebook_Log::STATUS_SUCCESS;
            $objFbLogs->save();
        }
    }
}
