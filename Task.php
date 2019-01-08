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

        foreach ($msgList as $v) {
            $msg_queue->push($v);
        }

        while (true) {
            sleep(1);
            $status = msg_stat_queue($msg_queue->queue);
            if ($status['msg_qnum'] == 0) {
                $t->killAll();
                if(is_callable($success)){
                    $success();
                }
                die(0);
            }
        }
    }
}