<?php
//---------------------------------------------------------
//---------------------------------------------------------
use Postcard\Model\WxPara;

include_once("WxPay.config.php");

define('ACCESS_TOKEN_KEY', 'accessToken');

class CommonUtil
{
    /**
     * 
     * 
     * @param toURL
     * @param paras
     * @return
     */
    protected $wxParaTable;
    protected $serviceLocator;
    public function setServiceLocator($sm) {
        $this->serviceLocator = $sm;
    }

    private function getWxParaTable()
    {
        if (!$this->serviceLocator) {
            return FALSE;
        }

        if (!$this->wxParaTable) {
            $this->wxParaTable = $this->serviceLocator->get('Postcard\Model\WxParaTable');
        }
        return $this->wxParaTable;
    }

    public function getAccessToken() {
        $wxpara = $this->getWxParaTable()->getWxPara(ACCESS_TOKEN_KEY);
        if (!$wxpara) {
            $token = $this->refreshAccessToken();
        } else {
            $token = $wxpara->value;
        }
        return $token;
    }

    private function asyn_request($args)
    {
        $host = $args["host"] ?  $args["host"] : "localhost";//主机
        $method = $args["method"] == "POST" ? "POST" : "GET";//方法   
        $url = $args["url"] ? $args["url"] : "http://".$host ;//地址
        $data = is_array($args["data"]) ? $args["data"] : array();//请求参数   
        $fp = @fsockopen($host, 80, $errno, $errstr, 30);
        //错误
        if (!$fp) {echo "$errstr ($errno)<br/>\n"; exit;}

        $qstr = $method == "GET" ? urlencode($args["data"]) : $args["data"];
        $params = '';
        $params.= $method == "GET" ? "GET {$url}?{$qstr} HTTP/1.1\r\n" :  "POST {$url} HTTP/1.1\r\n";
        $params.= "Host: ".$host."\r\n";
        $params.= "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.1.5) Gecko/20091102 Firefox/3.5.5\r\n";
        $params.= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
        $params.= "Accept-Language: zh-cn,zh;q=0.5\r\n";
        $params.= "Accept-Encoding: gzip,deflate\r\n";
        $params.= "Accept-Charset: GB2312,utf-8;q=0.7,*;q=0.7\r\n";
        $params.= "Keep-Alive: 300\r\n";
        $params.= "Connection: keep-alive\r\n";
        $params.= "Content-Type: application/x-www-form-urlencoded; charset=UTF-8;encoding=utf-8;\r\n";
        // $params.= "Content-Type: application/json; encoding=utf-8\r\n";
        $params.= "Content-Length: ".strlen($qstr)."\r\n\r\n";
        $params.= $method == "GET" ? null :$qstr;

        //file_put_contents("C:\\http.txt",$params);

        fwrite($fp, $params);

        // echo '<br>params:' . $params . '<br>';
        //取得回應的內容
        // $line = fgets($fp, 1024);
        // echo "result:<br>";
        // echo $line;
        // if (!preg_match('/^HTTP/1.. 200/i', $line)) return;

        $results = "";
        $inheader = true;
        while (!feof($fp)) {
          $line = fgets($fp, 2048);
          if ($inheader && ($line == "\n" || $line == "\r\n")) {
            $inheader = false;
          } else if (!$inheader) {
            $results .= $line;
          }
        }
        // echo '<br>results:' . $results . '<br>';
        fclose($fp);
        return $results;
    }
    
    function saveAccessToken($token) {
        $para = new WxPara();
        $para->paraName = ACCESS_TOKEN_KEY;
        $para->value = $token;
        $this->getWxParaTable()->savePara($para);
    }

    function refreshAccessToken() {
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.APPID.'&secret='.APPSERCERT;
        $obj = json_decode(file_get_contents($url));
        $access_token = $obj->access_token; // another para is "expires_in"
        $this->saveAccessToken($access_token);
        return $access_token;
    }

    function genAllUrl($toURL, $paras) {
        $allUrl = null;
        if(null == $toURL){
            die("toURL is null");
        }
        if (strripos($toURL,"?") =="") {
            $allUrl = $toURL . "?" . $paras;
        } else {
            $allUrl = $toURL . "&" . $paras;
        }

        return $allUrl;
    }

    function create_noncestr( $length = 16 ) {  
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ( $i = 0; $i < $length; $i++ )  {  
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
            //$str .= $chars[ mt_rand(0, strlen($chars) - 1) ];  
        }  
        return $str;  
    }
    /**
     * 
     * 
     * @param src
     * @param token
     * @return
     */
    function splitParaStr($src, $token) {
        $resMap = array();
        $items = explode($token,$src);
        foreach ($items as $item){
            $paraAndValue = explode("=",$item);
            if ($paraAndValue != "") {
                $resMap[$paraAndValue[0]] = $parameterValue[1];
            }
        }
        return $resMap;
    }
    
    /**
     * trim 
     * 
     * @param value
     * @return
     */
    static function trimString($value){
        $ret = null;
        if (null != $value) {
            $ret = $value;
            if (strlen($ret) == 0) {
                $ret = null;
            }
        }
        return $ret;
    }
    
    function formatQueryParaMap($paraMap, $urlencode){
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v){
            if (null != $v && "null" != $v && "sign" != $k) {
                if($urlencode){
                   $v = urlencode($v);
                }
                $buff .= $k . "=" . $v . "&";
            }
        }
        $reqPar;
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }
    function formatBizQueryParaMap($paraMap, $urlencode){
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v){
        //  if (null != $v && "null" != $v && "sign" != $k) {
                if($urlencode){
                   $v = urlencode($v);
                }
                $buff .= strtolower($k) . "=" . $v . "&";
            //}
        }
        $reqPar;
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }
    function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
             if (is_numeric($val))
             {
                $xml.="<".$key.">".$val."</".$key.">"; 

             }
             else
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";  
        }
        $xml.="</xml>";
        return $xml; 
    }
    function httpPost($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $res = curl_exec($ch);
        curl_close($ch);
        if ($res)
            return $res;
        else  
            return false;
    }

}

?>