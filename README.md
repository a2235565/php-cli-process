# php-cli-process
- 多进程 cli模式下执行程序
- 入口为 main
- 重启 逻辑 mac kill -30 pid.log linux kill -10 pid.log
- os 10.14.x 无法使用  
执行任务

```php
<?php
require 'ProcessHelp.php';
require 'MsgQueue.php';
require 'Daemon.php';
require 'Task.php';
require 'Curl.php';
require 'Register.php';
//监听信号
Daemon::listenSign();
//进程守护
Daemon::run();
//设置接到重启信号执行内容
Daemon::setSigUser1Callback(function (){
    Task::getProcess()->killAll();
    Task::getProcess()->setMq(Task::getMsgQueue());
    //$number  重新开启几个进程
    $number = 3;
    //你的业务逻辑
    $callback = function(){};
    
    Task::getProcess()->setNumber($number);
    Task::getProcess()->process(
        function (ProcessHelp $_this) use ($callback) {
            while (true) {
                $msg = $_this->getMq()->pop(1);
                if (is_callable($callback)) {
                    $callback($msg,$_this->getMq());
                }
            }
        }
    );
});

//制造测试队列
$list = [];
for($i=0;$i<100;$i++){
    $list[] = 'http://xxx.cn?a='.$i;
}
//进程数量
$number = 5;
//任务主体
$callback = function ($one,MsgQueue $MsgQueue){
    sleep(1);
    echo $one.PHP_EOL;
    file_put_contents(__DIR__.'/test.log',$one,FILE_APPEND);
    echo getmypid().PHP_EOL;
    //假设业务逻辑出错塞回队列
    if(false){
        $MsgQueue->push($one,1);
    }
};
//任务完成回调
$success = function (){
    echo 'task is success';
};

//执行任务
Task::run($list,$number,$callback,$success);


```
