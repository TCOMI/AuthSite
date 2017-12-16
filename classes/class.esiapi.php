<?php
use Swagger\Client\ApiClient;
use Swagger\Client\Configuration;
use Swagger\Client\ApiException;

require_once(realpath(dirname(__FILE__))."/esi/autoload.php");

class ESIAPI extends ApiClient 
{
    public function __construct(\Swagger\Client\Configuration $esiConfig = null) 
    {   
        global $config;
        if($esiConfig == null)
        {
            $esiConfig = Configuration::getDefaultConfiguration();
            $esiConfig->setCurlTimeout(60);
            $esiConfig->setUserAgent($config['snitch_authevesso_esi_ua']);
            // disable the expect header, because the ESI server reacts with HTTP 502
            $esiConfig->addDefaultHeader('Expect', '');
        }
        
        parent::__construct($esiConfig);     
    }
    
    public function setAccessToken($accessToken)
    {
        $this->config->setAccessToken($accessToken);
    }
}
?>
