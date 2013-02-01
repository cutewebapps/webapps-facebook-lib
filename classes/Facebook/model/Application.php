<?php

class Facebook_Application
{
    protected $_strId     = '';
    protected $_strApiKey = '';
    protected $_strSecret = '';
    protected $_strScope  = 'email,user_birthday';
    protected $_strUrl    = '';
    protected $_strPageUrl    = '';

    /**
     * @param string $strApplicationName - name of application in the config
     */
    public function __construct( $strApplicationName, $config = array() )
    {
        
// TODO: add more validation here to avoid errors inconfig
        if ( empty( $config ) ) {
            $config = App_Application::getInstance()->getConfig()->facebook->apps->$strApplicationName->toArray();
        }
        $this->_strId       = $config[ 'AppID'];
        $this->_strApiKey  = isset( $config[ 'ApiKey'] ) ? $config[ 'ApiKey'] : $config[ 'ApiID'];
        $this->_strSecret  = $config[ 'AppSecret'];
        $this->_strScope   = $config[ 'AppScope'];
        $this->_strUrl     = $config[ 'ApiUrl'];
        $this->_strPageUrl = isset( $config[ 'PageUrl'] ) ? $config[ 'PageUrl'] : '';

        // correcting URL - make it absolute
        if ( substr( $this->_strUrl, 0, 1 ) == '/' && isset( $_SERVER[ 'HTTP_HOST' ] ) ) {
            $this->_strUrl = (( Sys_Mode::isSsl() ) ? 'https' : 'http' )
                    . '://' . $_SERVER[ 'HTTP_HOST' ] . $this->_strUrl;
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->_strId;
    }
    /**
     * can be seen on application settings screen:
     * http://www.facebook.com/developers/apps.php?app_id=XXX
     * @return string
     */
    public  function getApiKey()
    {
        return $this->_strApiKey;
    }
    /**
     * can be seen on application settings screen:
     * http://www.facebook.com/developers/apps.php?app_id=XXX
     * @return string
     */
    public function getSecret()
    {
        return $this->_strSecret;
    }
    /**
     * What data is required by application
     *
     * see full list of permissions here:
     * http://developers.facebook.com/docs/authentication/permissions
     * @return string
     */
    public function getScope()
    {
        return $this->_strScope;
    }
    /**
     * @return string
     */
    public function getUri()
    {
        return $this->_strUrl;
    }

    /** @return string */
    public function getPageUrl()
    {
        if ( $this->_strPageUrl == '' )
                throw new App_Exception('Face Page URL was not provided');
        
        $strUrl = (Sys_Mode::isSsl() ? 'https' : 'http' ).'://www.facebook.com' .$this->_strPageUrl;
        return $strUrl;
    }
    /**
     *
     * @return Array (
            [algorithm] => HMAC-SHA256
            [issued_at] => 1308905771
            [page] => Array (
                [id] => 132648240148230
                [liked] => 1
                [admin] =>
            )
            [user] => Array (
                [country] => ua
                [locale] => en_US
                [age] => Array (
                    [min] => 0
                    [max] => 12
                )
            )
        )
     */
    public function getSignedRequest()
    {
        if ( !isset($_POST['signed_request']) )
            return '';
        
        $signed_request = $_POST['signed_request'];
        list($encoded_sig, $payload) = explode('.', $signed_request, 2);

        // decode the data
        $sig = self::base64UrlDecode($encoded_sig);
        $data = json_decode(self::base64UrlDecode($payload), true);

        if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
            throw new App_Exception('Unknown algorithm. Expected HMAC-SHA256');
            return null;
        }
        // check sig
//        $expected_sig = hash_hmac('sha256', $payload,
//                              $this->getSecret(), $raw = true);
//        if ($sig !== $expected_sig) {
//            throw new App_Exception('Bad Signed JSON signature!');
//            return null;
//        }
        return $data;
    }

    /**
     * if there is no active request, takes the result from session
     * if there is active request, takes it as priority
     *
     * @return boolean
     */
    public function isPageLiked()
    {
        $objSession = new App_Session_Namespace( 'facebook' );
        $strLikeVarId = 'likes_'.$this->getId();

        if ( isset( $_POST['signed_request'] ) ) {
            $arrRequest = $this->getSignedRequest();
            if ( isset( $arrRequest['page'] ) && isset( $arrRequest['page']['liked'] ) ) {
                    $objSession->$strLikeVarId = $arrRequest['page']['liked'];
                    return $arrRequest['page']['liked'];
            }
        } else {
            if ( isset( $objSession->$strLikeVarId ) && $objSession->$strLikeVarId )
                return true;
        }
        return false;
    }

    /**
     * Whether we are on a facebook Page Tab
     * @return boolean
     */
    public function isFacebookTab()
    {
        if ( !isset( $_POST['signed_request'] ) ) return false;
        $arrRequest = $this->getSignedRequest();
        return ( isset( $arrRequest['page'] ));
    }

    
    protected static function base64UrlDecode($input) {
        return base64_decode(strtr($input, '-_', '+/'));
    }

}
