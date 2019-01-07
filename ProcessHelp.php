<?php
class ProcessHelp
{
    protected $threadNumber = [];
    protected $number = 3;
    protected $mq = null;
    public function setMq($mq){
        $this->mq = $mq;
    }
    /**
     * getMq
     * @return MsgQueue
     * @author yangzhenyu
     * Time: 16:49
     */
    public function getMq(){
        return $this->mq;
    }
    public function setNumber($number)
    {
        $this->number = $number;
    }
    function process(callable $func)
    {
        $my_pid = getmypid();
        for ($i = 0; $i < $this->number; $i++) {
            if ($my_pid == getmypid()) {
                $pid = pcntl_fork();
                if ($pid == 0) {
                    $func($this);
                } else {
                    $this->threadNumber[$pid] = $pid;
                }
            }
        }
    }
    function kill($pid = null)
    {
        if (!empty($pid) && in_array($pid, $this->threadNumber)) {
            posix_kill($pid, SIGKILL);
            unset($this->threadNumber[$pid]);
        }
    }
    function killAll()
    {
        foreach ($this->threadNumber as $v) {
            posix_kill($v, SIGKILL);
        }
        $this->threadNumber = [];
    }
}
