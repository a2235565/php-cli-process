<?php
class MsgQueue
{
    public $queue;
    public function __construct($file = __FILE__,$proj = 'p')
    {
        $message_queue_key = ftok($file, $proj);
        if(msg_queue_exists($message_queue_key)){//如果有该消息队列,则删除,用于清空之前队列的无用数据
            msg_remove_queue(msg_get_queue($message_queue_key, 0666));
        }
        $message_queue= msg_get_queue($message_queue_key, 0666);
        $this->queue = $message_queue;
    }
    public function push($data, $type = 1)
    {
        //若 纠结报错 开大 msg_queue 队列长度即可
        @ $result = msg_send($this->queue, $type, $data);
        return $result;
    }
    public function pop($type = 0,$flags = MSG_IPC_NOWAIT)
    {
        msg_receive($this->queue, $type, $message_type, 1024, $message,true,$flags);
        return $message;
    }
    public function close()
    {
        return msg_remove_queue($this->queue);
    }
    /**
     * 创建一个队列（TODO:疑问待解决）
     * @param string $path_name
     * @param string $prop
     * @param string $perms
     * @return array
     */
    public static function getQueue($path_name, $prop = '1', $perms = '0666')
    {
        $data              = array();
        $data['queue＿key'] = ftok($path_name, $prop);
        $data['queue']     = msg_get_queue($data['queue＿key'], $perms);
        return $data;
    }
}