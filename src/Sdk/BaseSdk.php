<?php
namespace Lixinhan\ServiceSdk\Sdk;
use GuzzleHttp\Client;
use Lixinhan\ServiceSdk\Config;
use Lixinhan\ServiceSdk\Exception\SdkException;

class BaseSdk
{
    protected $url;
    protected $appId;
    protected $appSecret;
    protected $os;
    protected $version;
    protected $requestOriginContent;
    /**
     * @param $appId
     * @param $appSecret
     * @param $os
     * @param $version
     */
    public function __construct(Config $config)
    {
        $this->url=$config->getUrl();
        $this->appId = $config->getAppId();
        $this->appSecret = $config->getAppSecret();
        $this->os = $config->getOs();
        $this->version = $config->getVersion();
    }

    /**
     * 基础参数
     * @return array
     */
    protected function baseParams(){
        $params=[];
        $params['app_id']=$this->appId;
        $params['os']=$this->os;
        $params['time']=time();
        $params['version']=$this->version;
        return $params;
    }

    /**
     * 生成签名
     * @param $array
     * @return string
     */
    protected function makeSign($array){
        ksort($array);
        $string=http_build_query($array);
        return strtolower(md5($this->appSecret.$string.$this->appSecret));
    }

    /**
     * http请求
     * @param $method
     * @param $uri
     * @param $option
     * @return mixed
     * @throws SdkException
     */
    protected function httpClient($method, $uri, $option=[]){
        $client=new Client([
            'base_uri'=>$this->url
        ]);
        try{
            $response=$client->request($method,$uri,$option);
        }catch (\Exception $exception){
            throw new SdkException($exception->getMessage(),$exception->getCode());

        }
        if($response->getStatusCode()!=200){
            throw new SdkException('request error httpCode:'.$response->getStatusCode(),$response->getStatusCode());
        }
        $reponseContent=$response->getBody()->getContents();
        $this->setRequestOriginContent($reponseContent);
        $reponseArray=json_decode($reponseContent,true);
        $reponseStatus=$reponseArray['status']??-1;
        if($reponseStatus==-1){
            throw new SdkException($reponseArray['msg']??'',$reponseArray['error_code']??'');
        }
        return $reponseArray['data']??[];
    }

    /**
     * @return mixed
     */
    public function getRequestOriginContent()
    {
        return $this->requestOriginContent;
    }

    /**
     * @param mixed $requestOriginContent
     */
    protected function setRequestOriginContent($requestOriginContent)
    {
        $this->requestOriginContent = $requestOriginContent;
    }

}
