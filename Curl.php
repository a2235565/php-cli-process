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
// 2. 设置选项
        curl_setopt($ch, CURLOPT_URL, $url);  // 设置要抓取的页面地址
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);              // 抓取结果直接返回（如果为0，则直接输出内容到页面）
        curl_setopt($ch, CURLOPT_HEADER, 0);                      // 不需要页面的HTTP头
// 3. 执行并获取HTML文档内容，可用echo输出内容
        $output = curl_exec($ch);
// 4. 释放curl句柄
        curl_close($ch);
        return $output;
    }
}