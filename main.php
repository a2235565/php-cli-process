<?php
require 'ProcessHelp.php';
require 'MsgQueue.php';
require 'Daemon.php';
require 'Task.php';
require 'Curl.php';
//监听信号
Daemon::listenSign();
//进程守护
Daemon::run();
//设置接到重启信号执行内容
Daemon::setSigUser1Callback(function (){

});

//制造测试队列
$list = [];
for($i=0;$i<200;$i++){
    $list[] = 'http://www.php20.cn/article/'.$i;
}
//进程数量
$number = 1;
//任务主体
$callback = function ($one,MsgQueue $MsgQueue){
    sleep(1);
    $fileName = explode('/',$one);
    if($rs = Curl::run($one)){
        file_put_contents(__DIR__.'/A/'.$fileName[count($fileName)-1].'.html',$rs);
    }else{
        $MsgQueue->push($one,1);
    }
};
//任务完成回调
$success = function (){
    echo 'task is success';
};

//执行任务
Task::run($list,$number,$callback,$success);

