<?php

namespace app\models\datamodels_local;

use Yii;
use yii\base\Object;
/**
 * This is the model class for table "{{%grab_product}}".
 *
 */
class GrabRedis extends Object
{
    public $keyGrab='grabRedis:id';         //抢单键       前缀
    public $keyGrabLog='grabLogRedis:id';   //抢单记录键    前缀
    private $_errors=[];        //错误
    public $db;

    /**
     * 获取数据redis 数据库
     * @return null|object
     */
    public static function getDb()
    {
        return Yii::$app->get('redis_grab');
    }

    public function setbdb($db){
        return $this->db=$db?:static::getDb();
    }

    public function grab($grabId,$userId){
        $redis_db=static::getDb();

        $config=[$redis_db];
        $redLock = new YiiRedLock($config);
//        $tt=$redis_db->get('tt');
//        while ( $tt=='aa' || !$redis_db->set('tt','aa','PX',5000,'NX')){
//            usleep(150);
//        }


//        $config=[['127.0.0.1',6379,10,'sl788zdfwc8zi',4]];
//        $redLock = new RedLock($config,200,5);
//        $time=1;
////        do{
            $lock=$redLock->lock('red_package',5000);
////            if($time>1){
////                usleep(15000);
////            }
////            $time++;
////        }while(!is_array($lock));
////        $lock=new RedLock([$redis_db->hostname],10);
////        $lock=new CacheLock('red_package',Yii::getAlias('@runtime/lock/'));
////        $lock->lock();
        if($lock==false){
            return ['status'=>false,'msg'=>'抢不上'];
        }

        $redis_db->watch($this->getGrabLogKey($userId,$grabId));
        $redis_db->watch($this->getGrabKey($grabId));

        if( $this->existsGrabLog($userId,$grabId)){
            $redis_db->unwatch();
//            $lock->unlock();
            $redLock->unlock($lock);
//            $redis_db->del('tt');
            return ['status'=>false,'msg'=>'抱歉您已经抢过了！'];
        }

        if( ($this->existsGrab($grabId)) == false ) {
            if (!$grabProduct = $this->findGrabMysql($grabId)) {
                $redis_db->unwatch();
//                $lock->unlock();
                $redLock->unlock($lock);
//                $redis_db->del('tt');
                return ['status'=>'false','msg'=>current($this->getErrors())];
            }
            $redis_db->multi(); //开启事务
            $this->setGrabProduct($grabProduct['id'],$grabProduct);
        }else{
            $grabProduct=$this->findGrab($grabId);
            $redis_db->multi(); //开启事务
        }
//        var_dump($grabProduct);
        if( ($check=static::checkGrab($grabProduct))['status']==false){
            $redis_db->unwatch();
            $redis_db->discard();
//            $lock->unlock();
            $redLock->unlock($lock);
//            $redis_db->del('tt');
            return $check;
        }

        $now =time();
        if($grabProduct['mode'] == 0){//固定模式
            $userGrab=$this->firmMode($grabProduct['num'],$grabProduct['rest'],$grabProduct['partition']);
        }else if($grabProduct['mode'] == 1){
            $userGrab=$this->randomMode($grabProduct['rest'],$grabProduct['partition']);
        }else{
            $this->addError('mode','模式错误');
            $userGrab=false;
        }

        if($userGrab ===false){
            $redis_db->unwatch();

//            $lock->unlock();
            $redLock->unlock($lock);
//            $redis_db->del('tt');
            return ['status'=>false,'msg'=>$this->getError('mode')];
        }

        list($userNum,$grabProduct['rest'],$grabProduct['partition'],$restSub,$partition)=$userGrab;
        $redis_db->hincrby($this->getGrabKey($grabProduct['id']),'rest',-$restSub);
        $redis_db->hincrby($this->getGrabKey($grabProduct['id']),'partition',-$partition);
        $userGrab['user_id']=$userId;
        $userGrab['grab_product_id']=$grabProduct['id'];
        $userGrab['product_id']=$grabProduct['product_id'];
        $userGrab['num']=$userNum;
        $userGrab['total']=$grabProduct['total'];
        $userGrab['add_time']=$now;
        $this->setGrabLog($this->getGrabLogKey($userId,$grabId),$userGrab);
        $redis_db->exec();
//        var_dump($grabProduct);
//        $lock->unlock();
        $redLock->unlock($lock);
//        $redis_db->del('tt');
        return ['status'=>true,'msg'=>'用户'.$userId.'抢到了+'.$userGrab['num'],'剩余：'.$grabProduct['rest'].'分量'.$grabProduct['partition']];
    }

    /**
     * 获取 redis中 抢记录
     * @param $userId
     * @param $grabId
     * @return string
     */
    public function getGrabLogKey($userId,$grabId){
        return $this->keyGrabLog.$userId.'u'.$grabId;
    }

    /**
     * 获取redis中 抢商品记录
     * @param $grabId
     * @return string
     */
    public function getGrabKey($grabId){
        return $this->keyGrab.$grabId;
    }

    /**
     * 固定模式
     *
     * @param $num
     * @param $rest
     * @param $partition
     * @return array|bool
     */
    protected function firmMode($num,$rest,$partition){
        if($partition>0){
            if( ($rest-$num)>=0 ){
                $useGetNum=$num;
                $rest=$rest-$num;
                $partition=$partition-1;
                return [$useGetNum,$rest,$partition,$useGetNum,1];
            }
        }
        $this->addError('mode','手慢了');
        return false;
    }

    /**
     * 随机模式
     *
     * @param $rest
     * @param $partition
     * @return array|bool
     */
    protected function randomMode($rest,$partition){
        if($partition>0){
            if($rest>0){
                if( ($rest-$partition)<0 ){
                    $partition=$rest;
                }
                $useGetNum=rand(1,$rest-($partition-1));
                $rest=$rest-$useGetNum;
                $partition=$partition-1;
                return [$useGetNum,$rest,$partition,$useGetNum,1];
            }
        }
        $this->addError('mode','手慢了');
        return false;
    }

    /**
     * 保存抢单 到redis
     * @param $key
     * @param $value
     * @return bool
     */
    public function setGrabProduct($key,$value){
        $status=$this->setHashRedis($this->getGrabKey($key),$value);
        if($status){
            return true;
        }else{
            $this->addError('redis','秒杀商品放入redis失败');
            return false;
        }
    }

    /**
     * 设置 抢单 某键的值
     * @param $key
     * @param $field
     * @param $value
     * @return mixed
     */
    public function setGrabProductField($key,$field,$value){
        return static::getDb()->hset($this->getGrabKey($key),$field,$value);
    }

    /**
     * 保存数组到 redis
     * @param $key
     * @param $values
     */
    protected function setHashRedis($key,$values){
        $field=[];
        foreach ($values as $k => $v){
            $field[]=$k;
            $field[]=$v;
        }
        array_unshift($field,$key);
        static::getDb()->executeCommand('hmset',$field);
    }

    /**
     * 保存 用户抢单 记录
     * @param $key
     * @param $value
     * @return bool
     */
    public function setGrabLog($key,$value){
        $status=$this->setHashRedis($key,$value);
        if($status){
            return true;
        }else{
            $this->addError('redis','用户记录失败');
            return false;
        }
    }

    /**
     * 抢单记录 是否存在
     * @param $userId
     * @param $grabId
     * @return mixed
     */
    protected function existsGrabLog($userId,$grabId){
        return $this->existKey($this->getGrabLogKey($userId,$grabId));
    }

    /**
     * 抢单是否存在
     * @param $grabId
     * @return mixed
     */
    protected function existsGrab($grabId){
        return $this->existKey($this->getGrabKey($grabId));
    }

    /**
     * redis 中键是否存在
     * @param $key
     * @return mixed
     */
    protected function existKey($key){
        return static::getDb()->exists($key);
    }

    /**
     * 查找 抢单 记录
     * @param $grabId
     * @param $userId
     * @return mixed
     */
    protected function findGrabLog($userId,$grabId){
        return $this->getHashRedis($this->getGrabLogKey($userId,$grabId));
    }

    /**
     * 查找 抢单
     * @param $grabId
     * @return mixed
     */
    protected function findGrab($grabId){
        return $this->getHashRedis($this->getGrabKey($grabId));
    }

    protected function getHashRedis($key){
        $data=static::getDb()->hgetall($key);
        if(is_array($data)){
            $count=count($data);
            $result=[];
            for($i=0;$i<($count/2);$i++){
                $result[$data[2*$i]]=$data[(2*$i+1)];
            }
            return $result;
        }
        return false;
    }

    /**
     * 从mysql中查找
     * @param $grabId
     * @return array|bool
     */
    public function findGrabMysql($grabId){
        $grabProduct=GrabProduct::findBySql("select * from {{%grab_product}} WHERE id={$grabId} FOR UPDATE")->asArray()->one();
        if($grabProduct){
            $status=static::checkGrab($grabProduct);
            if($status['status']==false){
                $this->addError('grab',$status['msg']);
                return false;
            }
            return $grabProduct;
        }else{
            $this->addError('grab','未找到商品');
            return false;
        }
    }

    /**
     * 检查 抢单是否可以开抢
     * @param $product
     * @return array
     */
    public static function checkGrab($product){
        $now=time();
        if( !(
            isset($product['id'])   && isset($product['rest'])&& isset($product['product_id'])&& isset($product['partition'])&&
            isset($product['total'])&& isset($product['mode'])&& isset($product['start_time'])&& isset($product['end_time'])
            )
        ){
            var_dump($product);
            var_dump(isset($product['id']),isset($product['rest']),isset($product['product_id']),isset($product['partition']),
                isset($product['total']),isset($product['mode']),isset($product['start_time']),isset($product['end_time']));

            return ['status'=>false,'msg'=>'商品错误'];
        }
        if( $product['status']!=1){
            return ['status'=>false,'msg'=>'商品未开抢'];
        }
        if( $product['start_time']>$now){
            return ['status'=>false,'msg'=>'活动还未开始'];
        }
        if($product['end_time']<$now){
            return ['status'=>false,'msg'=>'活动已经结束'];
        }
        if($product['rest']<=0){
            return ['status'=>false,'msg'=>'您手慢了，已经抢完'];
        }
        return ['status'=>true];
    }

    /**
     * 添加错误
     * @param $key
     * @param $value
     */
    public function addError($key,$value){
        $this->_errors[$key]=$value;
    }

    /**
     * 获取错误
     * @param $key
     * @return mixed|null
     */
    public function getError($key){
        return isset($this->_errors[$key])?$this->_errors[$key]:null;
    }

    /**
     * 获取所有错误
     * @return array
     */
    public function getErrors(){
        return $this->_errors;
    }
}
