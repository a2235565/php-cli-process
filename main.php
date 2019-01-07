<?php
require 'ProcessHelp.php';
require 'MsgQueue.php';
$msg_queue = new MsgQueue();
$t = new ProcessHelp();
$t->setMq($msg_queue);
$msg_queue->push(1);
$msg_queue->push(3);
$msg_queue->push(2);
$msg_queue->push(2);
$msg_queue->push(2);
$msg_queue->push(2);
$msg_queue->push(2);
$msg_queue->push(2123);
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
