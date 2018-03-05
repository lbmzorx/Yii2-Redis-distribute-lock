<?php

namespace app\models\datamodels_local;

use Yii;

/**
 * This is the model class for table "{{%grab_log}}".
 *
 * @property string $id
 * @property integer $user_id
 * @property integer $num
 * @property integer $product_id
 * @property integer $grab_product_id
 * @property integer $total
 * @property integer $add_time
 */
class GrabLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%grab_log}}';
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
            [['user_id', 'num', 'product_id', 'grab_product_id', 'total', 'add_time'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户id',
            'num' => '抢的数量',
            'product_id' => '产品id',
            'grab_product_id' => '秒杀id',
            'total' => '秒杀总数',
            'add_time' => '添加时间',
        ];
    }
}
