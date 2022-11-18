<?php

namespace Lixinhan\ServiceSdk\Sdk\Wechat;

use Lixinhan\ServiceSdk\Dto\Wechat\Response\MiniprogramJscode2sessionResponseDto;
use Lixinhan\ServiceSdk\Sdk\BaseSdk;

class WechatSdk extends BaseSdk
{
    public function miniprogramJscode2session($jsCode):MiniprogramJscode2sessionResponseDto{
        $params=['js_code'=>$jsCode];
        $params=array_merge($params,$this->baseParams());
        $params['sign']=$this->makeSign($params);
        $responseDataArray=$this->httpClient('get','miniprogram/jscode2session?'.http_build_query($params));
        $responseDto=new MiniprogramJscode2sessionResponseDto();
        $responseDto->wechatUserOpenid=$responseDataArray['wechat_user_openid'];
        $responseDto->wechatUserUnionid=$responseDataArray['wechat_user_unionid'];
        return $responseDto;
    }




}
