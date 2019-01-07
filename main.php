<?php
require 'ProcessHelp.php';
require 'MsgQueue.php';
//制造数据
$list = [];
for($i=0;$i<10;$i++){
    $list[] = 'http://xxx.cn?a='.$i;
}
//执行任务
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
    $t->setNumber($number);
    $t->process(
        function (ProcessHelp $_this) use ($callback) {
            while (  $l = $_this->getMq()->pop(1)) {
                if(is_callable($callback)){
                    $callback($l);
                }
            }
        }
    );
    pcntl_async_signals(true);
    pcntl_signal(SIGUSR1,function () use($t,$number,$callback){
        //重启 逻辑  mac  kill -30 pid.log   linux kill -10 pid.log
        $t->killAll();
        $t->setNumber($number);
        $t->process(
            function (ProcessHelp $_this) use ($callback) {
                while (  $l = $_this->getMq()->pop(1)) {
                    if(is_callable($callback)){
                        $callback($l);
                    }
                }
            }
        );
    });
    while (true){
        usleep(1000);
    }
}