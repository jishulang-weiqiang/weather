<?php
namespace Weiqiang;

use Weiqiang\Lib\Curl;

/**
 * 天气
 */
class Weather
{
   /**
     * Notes：根据城市获取天气
     * @Author: weiqiang
     * Date: 2020/2/22
     * Time: 14:50
     * @return 
     */
    public static function getWeatherByCity($timeType, $city = '', $cityCode = '', $location = '')
    {
        $logTitle = '根据城市获取天气';
        $logData = array(
            'city' => $city,
            'cityCode' => $cityCode,
            'location' => $location,
        );
        $weatherData = [];
        try {
            $logData['api'] = 'aliYun';
            $weatherData = self::aliYun($timeType, $city, $cityCode, $location);//阿里云
            if(empty($weatherData) && $city) {
                $logData['api'] = 'etouchCn';
                $weatherData = self::etouchCn($timeType, $city, $cityCode);//中华万年历
            }
            
            //if(empty($weatherData)) Log::error($logTitle.'-失败', $logData);
        } catch (\Exception $ex) {
            //Log::exception($logTitle, $logData, $ex);
        }
        return $weatherData;
    }
    
    
    //根据经纬度获取城市-中华万年历
    public static function etouchCn($timeType, $city = '', $cityCode = '')
    {
        $logTitle = '根据经纬度获取城市-中华万年历';
        $logData = [];
        $weatherData = [];
        try {
            if(empty($city) && empty($cityCode)) return $weatherData;
            
            $url = 'http://wthrcdn.etouch.cn/weather_mini?city='.$city;//根据城市名
            if($cityCode) $url = 'http://wthrcdn.etouch.cn/weather_mini?citykey='.$cityCode;//根据城市码
            $res = Curl::get($url);
            $json = gzdecode($res);
            $data = json_decode($json, true);
            $data = isset($data['data']) ? $data['data'] : [];
            if(empty($data)) return;
            
            $centigrade = '℃';
            $weatherData['city'] = $data['city'];
            $weatherData['type'] = isset($data['yesterday']['type']) ? $data['yesterday']['type'] : '';
            $weatherData['type_id'] = 0;
            $weatherData['temperature'] = $data['wendu'].$centigrade;
            $weatherData['tips'] = isset($data['ganmao']) ? $data['ganmao'] : '';
         
            foreach ($data['forecast'] as $arr){
                $dateArr = explode('日', $arr['date']);
                $date = $dateArr[0];
                if($date < 10) $date = '0'.$date;
                $date2 = date('Y-m-').$date;
                $sameDay = date('Y-m-d');
                if($date2 < $sameDay) {//小于当天的日期是下个月的
                    $nextMonth = date('Y-m-',strtotime('next month'));
                    $date2 = $nextMonth.$date;
                }
                
                $highArr = explode(' ', $arr['high']);
                $high = isset($highArr[1]) ? $highArr[1] : 0;
                $high = str_replace($centigrade, '', $high);
                $lowArr = explode(' ', $arr['low']);
                $low = isset($lowArr[1]) ? $lowArr[1] : 0;
                $low = str_replace($centigrade, '', $low);
                
                $temperature = $weatherData['temperature'];
                if($sameDay != $date2){
                    $temperature = $high + $low;
                    if($temperature > 0) {
                        $temperature = $temperature / 2;
                        $temperature = number_format($temperature, 1).$centigrade;
                    }
                }

                $weatherData['list'][] =  array(
                    'type' => $arr['type'],
                    'type_id' => 0,
                    'week' => $dateArr[1],
                    'date' => $date2,
                    'temperature' => $temperature,
                );
            }
            
        } catch (\Exception $ex) {
            //Log::exception($logTitle, $logData, $ex);
        }
        return $weatherData;
    }
    
    
    //根据经纬度获取城市-中国天气网
    //https://www.cnblogs.com/laosan/p/how-to-write-a-weather-api.html
    //https://m.weather.com.cn/d/town/index?lat=22.53332&lon=113.93041&areaid=101280601
    public static function weatherComCn($cityCode)
    {
        $logTitle = '根据经纬度获取城市-中国天气网';
        $logData = [];
        $data = [];
        try {
            if(empty($cityCode)) return [];
            $url = 'http://m.weather.com.cn/data/'.$cityCode.'.html';
            $res = Curl::get($url);
            $data = json_decode($res, true);
        } catch (\Exception $ex) {
            //echo $ex->getMessage();exit;
            //Log::exception($logTitle, $logData, $ex);
        }
        return $data;
    }
    
    
    //根据经纬度获取城市-阿里云
    //https://market.aliyun.com/products/57126001/cmapi014302.html#sku=yuncode830200000  购买页
    //https://market.console.aliyun.com/imageconsole/index.htm   管理页
    //$location = '22.547,114.085947';//经度lat,纬度lng
    public static function aliYun($timeType, $city = '', $cityCode = '', $location = '')
    {
        $logTitle = '根据经纬度获取城市-阿里云';
        $logData = array(
            'timeType' => $timeType,
            'city' => $city,
            'cityCode' => $cityCode,
            'location' => $location,
        );
        $weatherData = [];
        try {
            //$appCode = "89aaa6c9db4443aeadf4682f00fac8ff";//我自己的
            $appCode = "946f5bfa97894da5bd0d684ab2e1a757";//公司
            
            $headers = array();
            array_push($headers, "Authorization:APPCODE ".$appCode);
            //根据API的要求，定义相对应的Content-Type
            array_push($headers, "Content-Type".":"."application/json; charset=UTF-8");
            $url = "https://jisutqybmf.market.alicloudapi.com/weather/query?";//"city=city&citycode=citycode&cityid=cityid&ip=ip&location=location"
            if($city) $url.= "city=".$city;
            elseif($location) $url.= "location=".$location;
            else return [];
            //if($cityCode) $url.= "citycode=".$cityCode;
            
            $res = Curl::get($url, $headers);
            $data = json_decode($res, true);  
            $data = isset($data['result']) ? $data['result'] : [];
            if(empty($data)) return $weatherData;
            
            $centigrade = '℃';
            $weatherData['city'] = $data['city'];
            $weatherData['type'] = isset($data['weather']) ? $data['weather'] : '';
            $weatherData['type_id'] = 0;
            $weatherData['temperature'] = $data['temp'].$centigrade;
            $weatherData['tips'] = '';
         
            foreach ($data['daily'] as $arr){
                if($timeType == 1){//白天
                    $dayData = isset($arr['day']) ? $arr['day'] : [];
                }else{//黑夜
                    $dayData = isset($arr['night']) ? $arr['night'] : [];
                }
               
                $temperature = isset($dayData['templow']) ? $dayData['templow'] : '';
                if($temperature) $temperature.=$centigrade;
                $weatherData['list'][] =  array(
                    'type' => isset($dayData['weather']) ? $dayData['weather'] : '',
                    'type_id' => 0,
                    'week' => $arr['week'],
                    'date' => $arr['date'],
                    'temperature' => $temperature,
                );
            }
        } catch (\Exception $ex) {
            //echo $ex->getMessage();exit;
            //Log::exception($logTitle, $logData, $ex);
        }
        
        return $weatherData;
    }
    
    
}