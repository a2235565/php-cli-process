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
$msg_queue->push(2);
umask(0);
posix_setsid();
$t->process(
    function (ProcessHelp $_this) {
        while (  $l = $_this->getMq()->pop(1)) {
            sleep(1);
            var_dump($l);
        }
    }
);

exit(0);

