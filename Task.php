<?php
/**
 * Created by PhpStorm.
 * User: yangzhenyu
 * Date: 2019/1/8
 * Time: 13:05
 */

class Task
{
    /**
     * getMsgQueue
     * @return MsgQueue|null
     * @author yangzhenyu
     * Time: 09:51
     */
    static function getMsgQueue(){
        return Register::get('msgQueue');
    }

    /**
     * getProcess
     * @return ProcessHelp|null
     * @author yangzhenyu
     * Time: 10:03
     */
    static function getProcess(){
        return Register::get('Process');
    }

    /**
     * run
     * @param $msgList
     * @param $number
     * @param $callback
     * @param callable $success
     * @author yangzhenyu
     * Time: 13:40
     */
    static function run($msgList, $number, $callback,callable $success=null)
    {
        $msg_queue = new MsgQueue();
        $t = new ProcessHelp();
        Register::set('msgQueue',$msg_queue);
        Register::set('Process',$t);
        $t->setMq($msg_queue);
        file_put_contents(__DIR__ . '/pid.log', getmypid());
        $t->setNumber($number);
        $t->process(
            function (ProcessHelp $_this) use ($callback) {
                while (true) {
                    $l = $_this->getMq()->pop(1);
                    if (is_callable($callback)) {
                        $callback($l,$_this->getMq());
                    }
                }
            }
        );

        $pid = pcntl_fork();
        if($pid==0){
            $pid = getmypid();
            file_put_contents(__DIR__.'/dataPid.log',$pid);
            foreach ($msgList as $v) {
                $msg_queue->push($v,1);
            }
            die();
        }else{
            sleep(1);
            while (true) {
                sleep(1);
                $status = msg_stat_queue($msg_queue->queue);
                if ($status['msg_qnum'] == 0) {
                    $t->killAll();
                    $pid = file_get_contents(__DIR__.'/dataPid.log');
                    posix_kill($pid, SIGKILL);
                    if(is_callable($success)){
                        $success();
                    }
                    die(0);
                }
            }
        }



    }
}