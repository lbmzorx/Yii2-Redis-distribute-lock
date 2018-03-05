<?php

namespace app\models\datamodels_local;

use Yii;

/**
 * This is the model class for table "{{%grab_product}}".
 *
 * @property string $id
 * @property string $name
 * @property integer $num
 * @property integer $partition
 * @property integer $rest
 * @property integer $total
 * @property integer $mode
 * @property integer $product_id
 * @property integer $status
 * @property string $add_params
 * @property integer $start_time
 * @property integer $end_time
 */
class GrabProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%grab_product}}';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_local');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'product_id'], 'required'],
            [['num', 'partition', 'rest', 'total', 'mode', 'product_id', 'status', 'start_time', 'end_time'], 'integer'],
            [['name', 'add_params'], 'string', 'max' => 255],
            [['num'],'integer','when'=>function($model){
                return $model->mode==1;
            },'message'=>'固定模式必须要']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'num' => '单人抢固定数量',
            'partition' => '份数',
            'rest' => 'Rest',
            'total' => '总数',
            'mode' => '模式 1固定，2随机',
            'product_id' => 'Product ID',
            'status' => '状态 0不可用，1 可用',
            'add_params' => '额外参数',
            'start_time' => '活动时间',
            'end_time' => '结束时间',
        ];
    }

    /**
     * @return array
     */
    public function checkGrab(){
        $now=time();
        if($this->start_time>$now){
            return ['status'=>false,'msg'=>'活动还未开始'];
        }
        if($this->end_time<$now){
            return ['status'=>false,'msg'=>'活动已经结束'];
        }
        if($this->rest<=0){
            return ['status'=>false,'msg'=>'您手慢了，已经抢完'];
        }
        return ['status'=>true];
    }

}
