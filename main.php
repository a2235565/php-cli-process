<?php
require 'ProcessHelp.php';
require 'MsgQueue.php';
require 'Daemon.php';
Daemon::listenSign();
Daemon::run();
//end
//制造数据
$list = [];
for($i=0;$i<100;$i++){
    $list[] = 'http://xxx.cn?a='.$i;
}

//执行任务
run($list,3,function ($one){
    sleep(1);
    echo $one.PHP_EOL;
    file_put_contents(__DIR__.'/test.log',$one,FILE_APPEND);
    echo getmypid().PHP_EOL;
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

    file_put_contents(__DIR__.'/pid.log',getmypid());
    $t->setNumber($number);
    $t->process(
        function ( $_this) use ($callback)  {
            while ( true ) {
                $l = $_this->getMq()->pop(1);
                if(is_callable($callback)){
                    $callback($l);
                }
            }
        }
    );

    foreach ($msgList as $v)
    {
        $msg_queue->push($v);
    }

    while (true){
        sleep(1);
        $status = msg_stat_queue($msg_queue->queue);
        if($status['msg_qnum']==0){
            echo 'task is success';
            $t->killAll();
            die(0);
        }


    }
}