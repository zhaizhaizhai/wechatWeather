<?php
/**
  * wechat php test
  */

//define your token
define("TOKEN", "libingzhai");
$wechatObj = new wechatCallbackapiTest();
$wechatObj->responseMsg();
//$wechatObj->valid();

class wechatCallbackapiTest
{
    /*public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }*/

    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

          //extract post data
        if (!empty($postStr)){
                
                  $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $RX_TYPE = trim($postObj->MsgType);

                switch($RX_TYPE)
                {
                    case "text":
                        $resultStr = $this->handleText($postObj);
                        break;
                    case "event":
                        $resultStr = $this->handleEvent($postObj);
                        break;
                    default:
                        $resultStr = "Unknow msg type: ".$RX_TYPE;
                        break;
                }
                echo $resultStr;
        }else {
            echo "";
            exit;
        }
    }

    public function handleText($postObj)
    {
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $keyword = trim($postObj->Content);
        $time = time();
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>0</FuncFlag>
                    </xml>";             
        if(!empty( $keyword ))
        {
            $msgType = "text";

            //天气
            $str = mb_substr($keyword,-2,2,"UTF-8");
            $str_key = mb_substr($keyword,0,-2,"UTF-8");
            if($str == '天气' && !empty($str_key)){
                $data = $this->weather($str_key);
                if(empty($data->cityInfo)){
                    $contentStr = "抱歉，没有查到\"".$str_key."\"的天气信息！";
                } else {
                    $contentStr = "【".$data->cityInfo->city."天气预报】\n".$data->time." "."发布"."\n\n实时天气\n".$data->data->forecast[0]->type." ".$data->data->wendu."℃  ".$data->data->forecast[0]->fx.$data->data->forecast[0]->fl."\npm25: ".$data->data->pm25." 湿度: ".$data->data->shidu."\n\n温馨提示：".$data->data->ganmao.
                                    "\n\n明天\n".$data->data->forecast[1]->week."\n".$data->data->forecast[1]->type." ".$data->data->forecast[1]->low." ".$data->data->forecast[1]->high."\n".$data->data->forecast[1]->fx." ".$data->data->forecast[1]->fl.
                                    "\n\n后天\n".$data->data->forecast[2]->week."\n".$data->data->forecast[2]->type." ".$data->data->forecast[2]->low." ".$data->data->forecast[2]->high."\n".$data->data->forecast[2]->fx." ".$data->data->forecast[2]->fl;
                }
            } else {
                $contentStr = "感谢您的关注"."\n"."目前平台功能如下："."\n"."查天气，如输入：北京天气"."\n"."更多内容，敬请期待...";
            }
            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            echo $resultStr;
        }else{
            echo "Input something...";
        }
    }

    public function handleEvent($object)
    {
        $contentStr = "";
        switch ($object->Event)
        {
            case "subscribe":
                $contentStr = "感谢您的关注"."\n"."目前平台功能如下："."\n"."查天气，如输入：北京天气"."\n"."更多内容，敬请期待...";
                break;
            default :
                $contentStr = "Unknow Event: ".$object->Event;
                break;
        }
        $resultStr = $this->responseText($object, $contentStr);
        return $resultStr;
    }
    
    public function responseText($object, $content, $flag=0)
    {
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>%d</FuncFlag>
                    </xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content, $flag);
        return $resultStr;
    }

    private function weather($n){
        include("weather_cityId.php");
        $c_name=$weather_cityId[$n];
        if(!empty($c_name)){
            $json=file_get_contents("http://t.weather.sojson.com/api/weather/city/".$c_name);
            return json_decode($json);
        } else {
            return null;
        }
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];    
                
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
}

?>
        
        
      