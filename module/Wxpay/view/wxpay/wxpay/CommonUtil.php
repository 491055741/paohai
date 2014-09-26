<?php
//---------------------------------------------------------
//---------------------------------------------------------
use Postcard\Model\WxPara;

include_once("WxPay.config.php");
Require("class_qrcode.php");

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
    //证书文件
    var $certFile;
    //证书密码
    var $certPasswd;
    //证书类型PEM
    var $certType;
    //CA文件
    var $caFile;

    public function qrcode($str, $filename = false)
    {
        QRcode::png($str, $filename); //,$file,"Q",6,2
    }

    public function setServiceLocator($sm)
    {
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

    // must call '$util->setServiceLocator($this->getServiceLocator())' before call this function
    public function getAccessToken()
    {
        $wxpara = $this->getWxParaTable()->getWxPara(ACCESS_TOKEN_KEY);
        if (!$wxpara) {
            $token = $this->refreshAccessToken();
        } else {
            $token = $wxpara->value;
        }
        return $token;
    }

    function saveAccessToken($token)
    {
        $para = new WxPara();
        $para->paraName = ACCESS_TOKEN_KEY;
        $para->value = $token;
        $this->getWxParaTable()->savePara($para);
    }

    function refreshAccessToken()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.APPID.'&secret='.APPSERCERT;
        $obj = json_decode(file_get_contents($url));
        $access_token = $obj->access_token; // another para is "expires_in"
        $this->saveAccessToken($access_token);
        return $access_token;
    }

    function setCertInfo($certFile, $certPasswd, $certType="PEM") {
        $this->certFile = $certFile;
        $this->certPasswd = $certPasswd;
        $this->certType = $certType;
    }

    function setCaInfo($caFile) {
        $this->caFile = $caFile;
    }

    function genAllUrl($toURL, $paras)
    {
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

    function create_noncestr( $length = 16 )
    {
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
    function splitParaStr($src, $token)
    {
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
    static function trimString($value)
    {
        $ret = null;
        if (null != $value) {
            $ret = $value;
            if (strlen($ret) == 0) {
                $ret = null;
            }
        }
        return $ret;
    }
    
    function formatQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v){
            if (null != $v && "null" != $v && "sign" != $k) {
                if ($urlencode) {
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
    
    function formatBizQueryParaMap($paraMap, $urlencode)
    {
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
    
    function httpPost($url, $data)
    {
        $this->logger("httpPost:$url");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        //设置证书信息
        if($this->certFile != "") {

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

            curl_setopt($ch, CURLOPT_SSLCERT, $this->certFile);
            curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $this->certPasswd);
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, $this->certType);
        }

        //设置CA
        if($this->caFile != "") {
            // 对认证证书来源的检查，0表示阻止对证书的合法性的检查。1需要设置CURLOPT_CAINFO
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_CAINFO, $this->caFile);
            // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
        } else {
            // 对认证证书来源的检查，0表示阻止对证书的合法性的检查。1需要设置CURLOPT_CAINFO
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        }

        $res = curl_exec($ch);
        curl_close($ch);
        if ($res)
            return $res;
        else  
            return false;
    }

    function httpGet($url)
    {
        $this->logger("httpGet:$url");
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        if ($res)
            return $res;
        else  
            return false;
    }

    private function logger($content)
    {
        file_put_contents($this->logFileName(), date('m/d H:i:s').' '.$content."\n", FILE_APPEND); // notice: use "\n", not '\n'
    }

    private function logFileName()
    {
        return '/tmp/paohai_error.log';
    }
}

?>