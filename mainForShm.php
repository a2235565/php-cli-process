<?php
require 'ProcessHelp.php';
require 'Daemon.php';
require 'Curl.php';
require 'Register.php';
require 'ShmQueue.php';
//监听信号
Daemon::listenSign();
//进程守护
Daemon::run();
//设置接到重启信号执行内容
Daemon::setSigUser1Callback(function (){

});

$shmq = new ShmQueue();
Register::set('shmq',$shmq);

$process = new ProcessHelp();
$pid = getmygid();
//开个进程注册数据
$process->processOne(function () use($process,$pid){
    for($i=0;$i<20;$i++){
        Register::get('shmq')->push('http://www.php20.cn/article/'.$i);
    }
    //结束杀死所有进程
    while (Register::get('shmq')->getLength()){
        sleep(1);
    }
    $process->killAll();
    posix_kill($pid, SIGKILL);
    exit(0);
});

//启动进程数
$number=2;
//业务
$callback = function ($oneTask){
    sleep(1);
    echo $oneTask.PHP_EOL;
};

$process->setNumber($number);
$process->process(
    function () use($callback) {
       while (1){
           $oneTask = Register::get('shmq')->pop();
           $callback($oneTask);
       }
    }
);










