<?php
/**
 * Created by PhpStorm.
 * User: yangzhenyu
 * Date: 2019/1/8
 * Time: 10:02
 */

class Daemon
{
    protected static $sigUser1=null;
    static function setSigUser1Callback(callable $func){
        Daemon::$sigUser1 = $func;
    }

    static function getSUC(){
        return Daemon::$sigUser1;
    }

    static function run(){
        //进程守护
        umask(0);
        posix_setsid();
        $pid = pcntl_fork();
        if ($pid>0){
            exit(0);
        }
    }


    /**
     * 监听信号
     */
    static function listenSign()
    {
        pcntl_async_signals(true);//异步信号
        $sign_array = array(
            SIGUSR1,
            SIGUSR2,
            SIGCHLD,
        );
        foreach ($sign_array as $sign) {
            pcntl_signal($sign, array(new Daemon(), 'signalHandler'));//信号调度工作
        }
    }
    /**
     * 信号处理
     * @param $signo
     */
    public function signalHandler($signo)
    {
        switch ($signo) {
            case SIGUSR1:
                echo '触发信号(处理数据)'.PHP_EOL;
                if (is_callable(Daemon::$sigUser1))
                {
                   (Daemon::$sigUser1)();
                }
                break;
            case SIGUSR2:
//                echo "进程回收处理\n";

                break;
            case SIGCHLD:
                //子进程退出
                echo "监控到子进程退出\n";
                break;
            default:
                echo "unknow";
                break;
        }
        return;
    }

    /**
     * processRecycling
     * @throws Exception
     * @author yangzhenyu
     * Time: 13:37
     */
    protected function processRecycling()
    {
        while (($pid = pcntl_waitpid(-1, $status, WUNTRACED)) != 0) {
            // 退出的子进程pid
            if ($pid > 0) {
                echo "fork成功\n";
            } else {
                // 出错了
                throw new Exception('监控子进程退出发生错误');
            }
            usleep(1);
        }
    }

}