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
        return $this->toCamelArray($reponseArray['data'])??[];
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



    /**
     * object  to array.
     * @param $object
     * @return mixed
     */
    protected static function toArray($object)
    {
        return json_decode(json_encode($object,JSON_FORCE_OBJECT), true);
    }

    /** object to array with camel key to underline.
     * @param $object
     * @return null|array|bool|string
     */
    protected function toArrayWithCamelKeytoUnderLine($object)
    {
        $array = $this->toArray($object);
        return $this->arrayCamelKeytoUnderLine($array);
    }

    /**
     * arrayCamelKeytoUnderLine
     * @param $array
     * @return array|bool|string|null
     */
    protected function arrayCamelKeytoUnderLine($array)
    {
        if (is_numeric($array)) {
            // 数字时，防止字符太长被转换
            return strval($array);
        }
        if (is_bool($array) || is_string($array) || is_null($array)) {
            return $array;
        }
        $return = [];
        foreach ($array as $key => $value) {
            $return[$this->camelStringtoUnderLineString($key)] = $this->arrayCamelKeytoUnderLine($value);
        }
        return $return;
    }

    /**
     * camelStringtoUnderLineString
     * @param $string
     * @return string
     */
    protected function camelStringtoUnderLineString($string)
    {
        $callback = preg_replace_callback('/([A-Z]+)/', function ($matchs) {
            return '_' . strtolower($matchs[0]);
        }, $string);
        $string = trim(preg_replace('/_{2,}/', '_', $callback), '_');
        $callback = preg_replace_callback('/([0-9]+)/', function ($matchs) {
            return '_' . strtolower($matchs[0]);
        }, $string);
        return trim(preg_replace('/_{2,}/', '_', $callback), '_');
    }

    protected function toCamelArray($array)
    {
        if (! is_array($array)) {
            return $array;
        }
        $return = [];
        foreach ($array as $key => $value) {
            if (strpos(strval($key), '_') !== false) {
                $array = explode('_', $key);
                $key = $array[0];
                $len = count($array);
                if ($len > 1) {
                    for ($i = 1; $i < $len; ++$i) {
                        $key .= ucfirst($array[$i]);
                    }
                }
            }
            $return[$key] = $this->toCamelArray($value);
        }
        return $return;
    }
    protected function copy($from, $to)
    {
        //把数据转换成数组
        if(is_scalar($from)||is_scalar($to)){
            return false;
        }elseif (is_array($from)) {
            $fromArray = $from;
        } else {
            $fromArray = $this->toArray($from);
        }

        $toReflection = new \ReflectionClass($to);
        $toProperties = $toReflection->getProperties();
        foreach ($toProperties as $property) {
            if($property->isProtected()||$property->isPrivate()){
                //如果是保护数据不复制
                continue;
            }
            $itemValue=$fromArray[$property->name] ?? null;
            $propertyType = $property->getType();
            if (isset($propertyType)) {
                if(!isset($fromArray[$property->name])){
                    if($property->getType()->allowsNull()){
                        $to->{$property->name} =null;
                    }
                    //但是没有设置类型，但是没有值，跳过
                    continue;
                }
            }
            $propertyTypeName=($propertyType==null?null:$propertyType->getName());
            switch ($propertyTypeName){
                case 'bool':
                    $to->{$property->name}=boolval($itemValue);
                    break;
                case 'int':
                    $to->{$property->name}=intval($itemValue);
                    break;
                case 'float':
                    $to->{$property->name}=floatval($itemValue);
                    break;
                case 'string':
                    $to->{$property->name}=strval($itemValue);
                    break;
                case 'array':
                    $to->{$property->name}=$itemValue;
                case null:
                    $to->{$property->name}=$itemValue;
                    break;
                default:
                    $className=($property->getType()->getName());
                    $class=new $className();
                    $this->copy($itemValue,$class);
                    $to->{$property->name}=$class;
            }
        }
        return $to;
    }
}
