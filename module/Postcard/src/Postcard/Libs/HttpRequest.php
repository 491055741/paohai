<?php
namespace Postcard\Libs;

/**
 * Usage: $res = WrapCurl::get('http://www.163.com')
 */
class HttpRequest {
    static public function get($url, $query_data, $cookie='', $referer=null) {
        return self::request($url, $query_data, array(), $cookie, $referer);
    }


    static public function post($url, $form_data, $cookie='', $referer=null) {
        return self::request($url, array(), $form_data, $cookie, $referer);
    }


    /**
     * Send request to url
     *
     * @param string $url
     * @param array $query_data
     * @param array $form_data if this param is not empty, 
     *          the request method will be set to POST, else GET
     * @param array $cookie
     *
     * @return string|false If error happened, return false, else return raw response
     */
    static public function request(
        $url, 
        $query_data=array(), 
        $form_data=array(), 
        $cookie='', 
        $referer=null
    ) {
        $curl_resource = curl_init();

        // Set opt
        $headers = array();
        $opts = array(
            CURLOPT_TIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
        );

        if ( ! empty($query_data)) {
            $url .= '?' . http_build_query($query_data);
        }
        $opts[CURLOPT_URL] = $url;

        if ( ! empty($form_data)) {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = $form_data;
            $headers['Content-type'] = "application/x-www-form-urlencoded";
        }

        if ($referer) {
            $opts[CURLOPT_REFERER] = $referer;
        }

        if ($cookie) {
            $opts[CURLOPT_COOKIE] = $cookie;
        }

        $opts[CURLOPT_HTTPHEADER] = $headers;

        curl_setopt_array($curl_resource, $opts);

        $res = curl_exec($curl_resource);

        if (curl_errno($curl_resource)) {
            var_dump(curl_error($curl_resource));
        }

        curl_close($curl_resource);

        return $res;
    }
}


/* End of file */
