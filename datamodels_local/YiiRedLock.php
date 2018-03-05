<?php
/**
 * author lbmzorx@163.com
 *
 * config file
 *
 *  'component'=>[
 *  ...
      'redis_db'=>[
           'class' => 'yii\redis\Connection',
            'hostname' => '127.0.0.1',
            'port' => 6379,
            'password'=>'123456',   //or null
            'database' => 4,
      ],
   ...
 ],
 *
 *
 * $config=[\yii::$app->get('redis_db')];
 * $redLock = new YiiRedLock($config);
 *
 * $lock=$redLock->lock('red_package',5000);
 * ...
 * $redLock->unlock($lock);
 */

namespace app\models\datamodels_local;


class YiiRedLock extends Redlock
{
    protected function initInstances()
    {
        if (empty($this->instances)) {
            foreach ($this->servers as $server) {
                $this->instances[] = $server;
            }
        }
    }

    protected function lockInstance($instance, $resource, $token, $ttl)
    {
        return $instance->set($resource, $token,'PX',$ttl,'NX');
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
        $instance->eval($script,1,$resource,$token);
    }

}