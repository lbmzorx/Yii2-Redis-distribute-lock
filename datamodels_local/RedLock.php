<?php
/**
 * author https://github.com/ronnylt/redlock-php
  * redlock-php - Redis distributed locks in PHP
  * Based on Redlock-rb by Salvatore Sanfilippo
  * This library implements the Redis-based distributed lock manager algorithm described in this blog post.
  * To create a lock manager:
  *  *
  * $servers = [
  * ['127.0.0.1', 6379, 0.01],
  * ['127.0.0.1', 6389, 0.01],
  * ['127.0.0.1', 6399, 0.01],
  * ];
  * 
  * $redLock = new RedLock($servers);
  * 
  * To acquire a lock:
  * 
  * 
  * $lock = $redLock->lock('my_resource_name', 1000);
  * 
  * Where the resource name is an unique identifier of what you are trying to lock and 1000 is the number of milliseconds for the validity time.
  * The returned value is false if the lock was not acquired (you may try again), otherwise an array representing the lock is returned, having three keys:
  * 
  * Array
  * (
  * [validity] => 9897.3020019531
  * [resource] => my_resource_name
  * [token] => 53771bfa1e775
  * )
  * 
  * validity, an integer representing the number of milliseconds the lock will be valid.
  * resource, the name of the locked resource as specified by the user.
  * token, a random token value which is used to safe reclaim the lock.
  * To release a lock:
  * $redLock->unlock($lock)
  * It is possible to setup the number of retries (by default 3) and the retry delay (by default 200 milliseconds) used to acquire the lock.
  * The retry delay is actually chosen at random between $retryDelay / 2 milliseconds and the specified $retryDelay value.
  * Disclaimer: As stated in the original antirez's version, this code implements an algorithm which is currently a proposal, it was not formally analyzed. Make sure to understand how it works before using it in your production environments.
 *
 *
 * you can use password, and indexdb as :
 *
 * $servers = [
 * ['127.0.0.1', 6379, 0.01, 'passowrd', 4],
 * ['127.0.0.1', 6389, 0.01, 'passowrd', 4],
 * ['127.0.0.1', 6399, 0.01, 'passowrd', 4],
 * ];
 *
 */

namespace app\models\datamodels_local;


class RedLock
{
    protected $retryDelay;
    protected $retryCount;
    protected $clockDriftFactor = 0.01;
    protected $quorum;
    protected $servers = array();
    protected $instances = array();
    function __construct(array $servers, $retryDelay = 200, $retryCount = 3)
    {
        $this->servers = $servers;
        $this->retryDelay = $retryDelay;
        $this->retryCount = $retryCount;
        $this->quorum  = min(count($servers), (count($servers) / 2 + 1));
    }

    /**
     * @param $resource
     * @param $ttl
     * @return array|bool
     */
    public function lock($resource, $ttl)
    {
        $this->initInstances();
        $token = uniqid();
        $retry = $this->retryCount;
        do {
            $n = 0;
            $startTime = microtime(true) * 1000;
            foreach ($this->instances as $instance) {
                if ($this->lockInstance($instance, $resource, $token, $ttl)) {
                    $n++;
                }
            }
            # Add 2 milliseconds to the drift to account for Redis expires
            # precision, which is 1 millisecond, plus 1 millisecond min drift
            # for small TTLs.
            $drift = ($ttl * $this->clockDriftFactor) + 2;
            $validityTime = $ttl - (microtime(true) * 1000 - $startTime) - $drift;
            if ($n >= $this->quorum && $validityTime > 0) {
                return [
                    'validity' => $validityTime,
                    'resource' => $resource,
                    'token'    => $token,
                ];
            } else {
                foreach ($this->instances as $instance) {
                    $this->unlockInstance($instance, $resource, $token);
                }
            }
            // Wait a random delay before to retry
            $delay = mt_rand(floor($this->retryDelay / 2), $this->retryDelay);
            usleep($delay * 1000);
            $retry--;
        } while ($retry > 0);
        return false;
    }
    public function unlock(array $lock)
    {
        $this->initInstances();
        $resource = $lock['resource'];
        $token    = $lock['token'];
        foreach ($this->instances as $instance) {
            $this->unlockInstance($instance, $resource, $token);
        }
    }
    protected function initInstances()
    {
        if (empty($this->instances)) {
            foreach ($this->servers as $server) {
                list($host, $port, $timeout,$password,$index) = $server;
                $redis = new \Redis();
                $redis->connect($host, $port, $timeout);
                if($password) $redis->auth($password);      //you can use password
                if($index)  $redis->select($index);         //you can select db
                $this->instances[] = $redis;
            }
        }
    }
    protected function lockInstance($instance, $resource, $token, $ttl)
    {
        return $instance->set($resource, $token, ['NX', 'PX' => $ttl]);
    }
    protected function unlockInstance($instance, $resource, $token)
    {
        $script = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("DEL", KEYS[1])
            else
                return 0
            end
        ';
        return $instance->eval($script, [$resource, $token], 1);
    }
}