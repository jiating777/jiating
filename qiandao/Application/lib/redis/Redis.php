<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/3 0003
 * Time: 下午 3:32
 */
namespace app\lib\redis;

use think\Exception;

class Redis{
    private static $redisInstance;

    private function __construct(){}

    public static function getRedisConn(){
        if(!self::$redisInstance instanceof self){
            self::$redisInstance = new self;
        }
        // 获取当前单例
        $temp = self::$redisInstance;
        // 调用私有化方法
        return $temp->connRedis();
    }

    /**
     * 连接ocean 上的redis的私有化方法
     * @return Redis
     */
    private static function connRedis()
    {
        try {
            $redis_ocean = new \Redis();
            $redis_ocean->connect(config('redis.host'), config('redis.port'));
            $redis_ocean->auth(config('redis.password'));
            if(0 != config('redis.select')){
                $redis_ocean->select(config('redis.select'));
            }

        }catch (Exception $e){
            echo $e->getMessage().'<br/>';
        }

        return $redis_ocean;
    }

    /**
     * 添空当前数据库
     *
     * @return boolean
     */
    public function clear(){
        return self::getRedisConn()->flushDB();
    }

    /**
     * 写入缓存
     * @param string $key 键名
     * @param string $value 键值
     * @param int $exprie 过期时间 0:永不过期
     * @return bool
     */
    public function set($key, $value, $exprie = 0)
    {
        if ($exprie == 0) {
            $set = self::getRedisConn()->set($key, $value);
        } else {
            $set = self::getRedisConn()->setex($key, $exprie, $value);
        }
        return $set;
    }
    /**
     * 读取缓存
     * @param string $key 键值
     * @return mixed
     */
    public function get($key)
    {
        $fun = is_array($key) ? 'Mget' : 'get';
        return self::getRedisConn()->{$fun}($key);
    }

    /**
     * 获取值长度
     * @param string $key
     * @return int
     */
    public function lLen($key)
    {
        return self::getRedisConn()->lLen($key);
    }

    /**
     * 将一个或多个值插入到列表头部
     * @param $key
     * @param $value
     * @return int
     */
    public function LPush($key, $value, $value2 = null, $valueN = null)
    {
        return self::getRedisConn()->lPush($key, $value, $value2, $valueN);
    }

    /**
     * 移出并获取列表的第一个元素
     * @param string $key
     * @return string
     */
    public function lPop($key)
    {
        return self::getRedisConn()->lPop($key);
    }

    /**
     * 从list 的尾部删除元素，并返回删除元素
     * @param $key
     * @return string
     */
    public function rPop($key){
        return self::getRedisConn()->rPop($key);
    }

    /**
     * 从第一个list 的尾部移除元素并添加到第二个 list的头部,最后返回被移除的元素值，整个操
     * 作是原子的.如果第一个list 是空或者不存在返回 nil
     * @param $source
     * @param $destination
     * @return string
     */
    public function rpoplpush($source,$destination){
        return self::getRedisConn()->rpoplpush($source,$destination);
    }

    /**
     * 值加加操作,类似 ++$i ,如果 key 不存在时自动设置为 0 后进行加加操作
     *
     * @param string $key 缓存KEY
     * @param int $default 操作时的默认值
     * @return int　操作后的值
     */
    public function incr($key,$default=1){
        if($default == 1){
            return self::getRedisConn()->incr($key);
        }else{
            return self::getRedisConn()->incrBy($key, $default);
        }
    }

    /**
     * 值减减操作,类似 --$i ,如果 key 不存在时自动设置为 0 后进行减减操作
     *
     * @param string $key 缓存KEY
     * @param int $default 操作时的默认值
     * @return int　操作后的值
     */
    public function decr($key,$default=1){
        if($default == 1){
            return self::getRedisConn()->decr($key);
        }else{
            return self::getRedisConn()->decrBy($key, $default);
        }
    }
}