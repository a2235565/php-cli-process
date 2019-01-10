<?php
require 'ProcessHelp.php';
require 'Daemon.php';
require 'Curl.php';
require 'Register.php';
//监听信号
Daemon::listenSign();
//进程守护
Daemon::run();
//设置接到重启信号执行内容
Daemon::setSigUser1Callback(function (){

});

$redis = new Redis;
$redis->connect('','');
Register::set('redis',$redis);
for($i=0;$i<200;$i++){
    $redis->lPush('task','http://www.php20.cn/article/'.$i);
}

$process = new ProcessHelp();
//启动进程数
$number=3;
//业务
$callback = function ($oneTask){
    echo $oneTask;
};

$process->setNumber($number);
$process->process(
    function () use($callback) {
       while ($oneTask = Register::get('redis')->lPop('task')){
           $callback($oneTask);
       }
    }
);







