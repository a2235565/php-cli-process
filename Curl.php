<?php
/**
 * Created by PhpStorm.
 * User: yangzhenyu
 * Date: 2019/1/8
 * Time: 15:56
 */

class Curl
{
    static function run($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);  // 设置要抓取的页面地址
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);              // 抓取结果直接返回（如果为0，则直接输出内容到页面）
        curl_setopt($ch, CURLOPT_HEADER, 0);                      // 不需要页面的HTTP头
        $output = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code==200){
            return $output;
        }else{
            return false;
        }

    }
}