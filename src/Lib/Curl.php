<?php
namespace Weiqiang\Lib;

/**
 * curl
 */
class Curl
{
    public static function get($url, $headers = [])
    {
        $tmpInfo = '';
        try {
            //$headers[] = "Content-type: application/x-www-form-urlencoded";
            //$headers[] = "Zoomkey-Auth-Token: 9CD0F0F60AFDF00";
            $curl = curl_init(); // 启动一个CURL会话
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            $tmpInfo = curl_exec($curl);     
            //关闭URL请求
            curl_close($curl);
        } catch (\Exception $ex) {
            //echo $ex->getMessage();exit;
        }
        
        return $tmpInfo;   
    }


    public static function post($url, $headers, $data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $sResult = curl_exec($ch);
        if($sError=curl_error($ch)){
            die($sError);
        }
        curl_close($ch);
        return $sResult;
    }
    
    
    
}