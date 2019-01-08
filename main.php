<?php
require 'ProcessHelp.php';
require 'MsgQueue.php';
require 'Daemon.php';
require 'Task.php';
//监听信号
Daemon::listenSign();
//进程守护
Daemon::run();
//制造测试队列
$list = [];
for($i=0;$i<100;$i++){
    $list[] = 'http://xxx.cn?a='.$i;
}
//进程数量
$number = 5;
//任务主体
$callback = function ($one){
    sleep(1);
    echo $one.PHP_EOL;
    file_put_contents(__DIR__.'/test.log',$one,FILE_APPEND);
    echo getmypid().PHP_EOL;
};


//执行任务
Task::run($list,$number,$callback);

