<?php
/**
 * Created by PhpStorm.
 * User: yangzhenyu
 * Date: 19-1-11
 * Time: 下午6:43
 */

class ShmQueue
{

    private $maxSize = 0; // 队列最大长度
    private $blockSize = 256; // 块的大小(byte)
    private $memSize = 25600; // 最大共享内存(byte)
    private $shmId = 0;
    private $semId = 0;

    public function __construct()
    {
        $shmkey = ftok(__FILE__, 't');
        $this->shmId = shmop_open($shmkey, "c", 0644, $this->memSize);
        $this->maxSize = $this->memSize / $this->blockSize - 3;
        $this->semId = sem_get($shmkey, 1);
        //初始写
        shmop_write($this->shmId, 0, ($this->maxSize + 1) * $this->blockSize);
        //初始读
        shmop_write($this->shmId, 0, ($this->maxSize + 2) * $this->blockSize);
        sem_acquire($this->semId);
    }

    private function getWrite()
    {
        return $this->decode(shmop_read($this->shmId, ($this->maxSize + 1) * $this->blockSize, $this->blockSize - 1));
    }

    private function setWrite($val)
    {
        //初始写
        shmop_write($this->shmId, $this->encode($val), ($this->maxSize + 1) * $this->blockSize);
    }

    private function setRead($val)
    {
        //初始读
        shmop_write($this->shmId, $this->encode($val), ($this->maxSize + 2) * $this->blockSize);
    }

    private function getRead()
    {
        return $this->decode(shmop_read($this->shmId, ($this->maxSize + 2) * $this->blockSize, $this->blockSize - 1));
    }

    public function getLength()
    {
        return $this->getWrite() - $this->getRead() >= 0
            ? $this->getWrite() - $this->getRead()
            : $this->getWrite() + $this->maxSize - $this->getRead() + 1;
    }

    public function push($value)
    {
        if ($this->checkInc()) { // 队满
            while (1) {
                sleep(0.1);
                if (!$this->checkInc()) {
                    break;
                }
            }
        }
        $data = $this->encode($value);
        shmop_write($this->shmId, $data, $this->getWrite() * $this->blockSize);
        $this->setWrite($this->getWrite() + 1);
        if ($this->getWrite() > $this->maxSize) {
            $this->setWrite(0);
        }
        return true;
    }

    public function pop()
    {
        if (!$this->getLength()) { // 队空
            return false;
        }
        $value = shmop_read($this->shmId, $this->getRead() * $this->blockSize, $this->blockSize - 1);
        $this->setRead($this->getRead() + 1);
        if ($this->getRead() > $this->maxSize) {
            $this->setRead(0);
        }
        return $this->decode($value);
    }

    private function checkInc()
    {
        return $this->getLength() + 1 >= $this->maxSize;
    }

    private function encode($value)
    {
        $data = serialize($value) . "__eof";
        if (strlen($data) > $this->blockSize - 1) {
            throw new Exception(strlen($data) . " is overload block size!");
        }
        return $data;
    }

    private function decode($value)
    {
        $data = explode("__eof", $value);
        return (substr($data[0], 0, 1) == "0")
            ? false
            : unserialize($data[0]);
    }


    public function __destruct()
    {
        sem_release($this->semId); // 出临界区, 释放信号量
    }

    public function close()
    {
        shmop_delete($this->shmId);
        shmop_close($this->shmId);
    }
}