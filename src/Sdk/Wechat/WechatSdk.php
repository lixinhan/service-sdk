<?php

namespace Lixinhan\ServiceSdk\Sdk\Wechat;

use Lixinhan\ServiceSdk\Dto\Wechat\Request\MiniprogramGetUnlimitedQRCodeRequestDto;
use Lixinhan\ServiceSdk\Dto\Wechat\Response\MiniprogramAnalysisSceneResponseDto;
use Lixinhan\ServiceSdk\Dto\Wechat\Response\MiniprogramGetPhoneNumberByEncryptedDataRequestDto;
use Lixinhan\ServiceSdk\Dto\Wechat\Response\MiniprogramGetPhoneNumberResponseDto;
use Lixinhan\ServiceSdk\Dto\Wechat\Response\MiniprogramGetUnlimitedQRCodeResponseDto;
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
        $this->copy($responseDataArray,$responseDto);
        return $responseDto;
    }
    public function miniprogramGetUnlimitedQRCode(MiniprogramGetUnlimitedQRCodeRequestDto  $requestDto):MiniprogramGetUnlimitedQRCodeResponseDto{
        $params=$this->toArrayWithCamelKeytoUnderLine($requestDto);
        $params=array_merge($params,$this->baseParams());
        $params['sign']=$this->makeSign($params);
        $responseDataArray=$this->httpClient('get','miniprogram/jscode2session?'.http_build_query($params));
        $responseDto=new MiniprogramGetUnlimitedQRCodeResponseDto();
        $this->copy($responseDataArray,$responseDto);
        return $responseDto;
    }


    public function miniprogramGetPhoneNumber($code):?MiniprogramGetPhoneNumberResponseDto{
        $params=['code'=>$code];
        $params=array_merge($params,$this->baseParams());
        $params['sign']=$this->makeSign($params);
        $responseDataArray=$this->httpClient('get','miniprogram/getPhoneNumber?'.http_build_query($params));
        $responseDto=new MiniprogramGetPhoneNumberResponseDto();
        $this->copy($responseDataArray,$responseDto);
        return $responseDto;

    }

    public function miniprogramGetPhoneNumberByEncryptedData(MiniprogramGetPhoneNumberByEncryptedDataRequestDto $requestDto):MiniprogramGetPhoneNumberResponseDto{
        $params=$this->toArrayWithCamelKeytoUnderLine($requestDto);
        $params=array_merge($params,$this->baseParams());
        $params['sign']=$this->makeSign($params);
        $responseDataArray=$this->httpClient('get','miniprogram/getPhoneNumberByEncryptedData?'.http_build_query($params));
        $responseDto=new MiniprogramGetPhoneNumberResponseDto();
        $this->copy($responseDataArray,$responseDto);
        return $responseDto;

    }

    public function miniprogramAnalysisScene($miniprogramSceneId):MiniprogramAnalysisSceneResponseDto{
        $params['miniprogram_scene_id']=$miniprogramSceneId;
        $params=array_merge($params,$this->baseParams());
        $params['sign']=$this->makeSign($params);
        $responseDataArray=$this->httpClient('get','miniprogram/analysisScene?'.http_build_query($params));
        $responseDto=new MiniprogramAnalysisSceneResponseDto();
        $this->copy($responseDataArray,$responseDto);
        return $responseDto;

    }


}
