<?php
require 'ProcessHelp.php';
require 'MsgQueue.php';


$list = [];
for($i=0;$i<100;$i++){
    $list[] = 'http://xxx.cn?a='.$i;
}

run($list,1,function ($one){
    sleep(1);
    echo $one.PHP_EOL;
});


/**
 * @param $msgList //队列
 * @param $number //开启进程数量
 * @param $callback //回调函数
 */
function run($msgList,$number,$callback){
    $msg_queue = new MsgQueue();
    $t = new ProcessHelp();
    $t->setMq($msg_queue);
    foreach ($msgList as $v)
    {
        $msg_queue->push($v);
    }
//进程守护
umask(0);
posix_setsid();

$pid = pcntl_fork();
if ($pid>0){
    exit(0);
}
file_put_contents(__DIR__.'/pid.log',getmypid());
$t->setNumber(1);
$t->process(
    function (ProcessHelp $_this) {
        while (  $l = $_this->getMq()->pop(1)) {
            sleep(1);
            file_put_contents(__DIR__.'/test.txt',$l."\n\r",FILE_APPEND);
        }
    }
);

pcntl_async_signals(true);
pcntl_signal(SIGUSR1,function (){
    //重启 逻辑  mac  kill -30 pid.log   linux kill -10 pid.log
    file_put_contents(__DIR__.'/tt.log',1);
});

while (true){
    usleep(1000);
}
