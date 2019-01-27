<?php
/**
 * Created by PhpStorm.
 * User: yangzhenyu
 * Date: 2018/12/10
 * Time: 上午9:23
 */
class ShmQueue
{
    protected $shkList = [];
    protected $semIdList = [];
    protected $memSize = 150000;
    protected $blockSize = 1500;
    protected $tempPath = './';
    protected $maxSize = 0;
    protected $nowQueue = null;
    function __construct()
    {
        $this->showTitle();
        $this->maxSize = $this->memSize / $this->blockSize - 3;
    }
    function setNowQueue($key)
    {
        $this->nowQueue = $key;
    }
    function addQueue($file = __FILE__, $proj = 'a')
    {
        if (!file_exists($this->tempPath . $file.$proj)) {
            file_put_contents($this->tempPath . $file.$proj, '');
        }
        $shmkey = ftok($this->tempPath . $file.$proj, $proj);
        $shmId = shmop_open($shmkey, "c", 0644, $this->memSize);
        $key = $file . $proj;
        $this->shkList[$key] = $shmId;
//        $semId = sem_get($shmkey, 1);
//        $this->semIdList[$key] = $semId;
        $this->setNowQueue($key);
        //初始化队列长度
        $this->setRead($key,0);
        $this->setWrite($key,0);
    }
    function bindQueue($file = __FILE__, $proj = 'a')
    {
        if (!file_exists($this->tempPath . $file.$proj)) {
            return false;
        }
        $shmkey = ftok($this->tempPath . $file.$proj, $proj);
        $shmId = shmop_open($shmkey, "c", 0644, $this->memSize);
        $key = $file . $proj;
        $this->shkList[$key] = $shmId;
        $this->setNowQueue($key);
    }
    function removeQueue($file = __FILE__, $proj = 'a')
    {
        if (file_exists($this->tempPath . $file.$proj)) {
            unlink($this->tempPath . $file.$proj);
        }
        $key = $file . $proj;
        if ($this->nowQueue == $key) {
            $this->nowQueue = '';
        }
        sem_release($this->shkList[$key]);
        shmop_delete($this->shkList[$key]);
        shmop_close($this->semIdList[$key]);
        unset($this->shkList[$key]);
        unset($this->semIdList[$key]);
    }
    /**
     * push
     * @param      $data
     * @param null $file
     * @param null $proj
     * @throws Exception
     * @author yangzhenyu
     * @return bool
     * Time: 15:04
     */
    function push($data, $file = null, $proj = null)
    {
        $key = $file . $proj;
        if (empty($file) || empty($proj)) {
            if ($this->nowQueue == '') {
                throw new Exception('请设置默认队列，或者传入,$file,$proj');
            } else {
                $key = $this->nowQueue;
            }
        }
        if (empty($this->shkList[$key])) {
            throw new Exception('您的队列不存在');
        }
        //队列已满
        if(!$this->incCheck($key)){
            return false;
        }
        $data = $this->encode($data);
        shmop_write($this->shkList[$key], $data, $this->getWrite($key) * $this->blockSize);
        $this->setWrite($key,$this->getWrite($key) + 1);
        if ($this->getWrite($key) > $this->maxSize) {
            $this->setWrite($key,0);
        }
        return true;
    }
    /**
     * pop
     * @param null $file
     * @param null $proj
     * @return bool
     * @throws Exception
     * @author yangzhenyu
     * Time: 15:21
     */
    public function pop($file = null, $proj = null)
    {
        $key = $file . $proj;
        if (empty($file) || empty($proj)) {
            if ($this->nowQueue == '') {
                throw new Exception('请设置默认队列，或者传入,$file,$proj');
            } else {
                $key = $this->nowQueue;
            }
        }
        if (!$this->getLen($key)) { // 队空
            return false;
        }
        $value = shmop_read($this->shkList[$key], $this->getRead($key) * $this->blockSize, $this->blockSize - 1);
        $this->setRead($key,$this->getRead($key) + 1);
        if ($this->getRead($key) > $this->maxSize) {
            $this->setRead($key,0);
        }
        return $this->decode($value);
    }
    private function incCheck($key){
        return $this->maxSize > $this->getLen($key);
    }
    private function getWrite($key)
    {
        $w = shmop_read($this->shkList[$key], ($this->maxSize + 1) * $this->blockSize, $this->blockSize - 1);
        return intval($this->decode($w));
    }
    private function setWrite($key,$val)
    {
        shmop_write($this->shkList[$key], $this->encode($val), ($this->maxSize + 1) * $this->blockSize);
    }
    private function getRead($key)
    {
        $r = shmop_read($this->shkList[$key], ($this->maxSize + 2) * $this->blockSize, $this->blockSize - 1);
        return intval($this->decode($r));
    }
    private function setRead($key,$val)
    {
        shmop_write($this->shkList[$key], $this->encode($val), ($this->maxSize + 2) * $this->blockSize);
    }
    private function getLen($key){
        $w = $this->getWrite($key);
        $r = $this->getRead($key);
        return $w - $r  >= 0
            ? $w - $r
            : $w + $this->maxSize - $r + 1;
    }
    private function encode($data){
        return $data.'__EOL';
    }
    private function decode($data){
        $list = explode('__EOL',$data);
        return $list[0];
    }
    function __destruct()
    {
//        sem_release($semId);
//        shmop_delete($shmId);
//        shmop_close($shmId);
//        foreach ($this->semIdList as $v){
//            var_dump($v);
//            sem_release($v);
//        }
        unset($this->semIdList);
        foreach ($this->shkList as $v){
            shmop_delete($v);
            shmop_close($v);
            if(file_exists($this->tempPath .$v)){
                unlink($this->tempPath .$v);
            }
        }
        unset($this->shkList);
    }
    function showTitle()
    {
        echo '--------------------------------------------' . PHP_EOL;
        echo '--------------当前共享内存信息--------------' . PHP_EOL;
        echo '--------------------------------------------' . PHP_EOL;
        echo `ipcs -m`;
        echo '--------------------------------------------' . PHP_EOL;
        echo '--------若不需要请 ipcrm -m id 删除---------' . PHP_EOL;
        echo '--------------------------------------------' . PHP_EOL;
    }
}
