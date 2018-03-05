<?php
/**
 * Created by PhpStorm.
 * User: aa
 * Date: 2018/1/17
 * Time: 14:35
 */

namespace app\controllers;


use app\models\datamodels_local\GrabProduct;
use app\models\datamodels_local\GrabRedis;
use yii\caching\TagDependency;
use yii\data\Pagination;
use yii\db\Query;
use yii\web\Controller;
use yii\web\Response;

class GrabProductController extends Controller
{

    public function actionIndex(){
        $db=\yii::$app->db_local;

        $request=\yii::$app->request;
        $tag='test.s.count';
        $query=(new Query())->from('{{%grab_product}}')
            ->andFilterWhere([
                'user_id'=>$request->get('user_id'),
                'app_id'=>$request->get('app_id'),
                'interface_id'=>$request->get('interface_id'),
            ])
            ->andFilterWhere(['>=','start_time',isset($request->get('start_time')[0])&&strtotime($request->get('start_time')[0])?strtotime($request->get('start_time')[0]):'',])
            ->andFilterWhere(['<=','end_time',isset($request->get('end_time')[1])&&strtotime($request->get('end_time')[1])?strtotime($request->get('end_time')[1]):'',]);
        $cache=\yii::$app->cache;
        $get=$request->get();
        unset($get['page']);
        unset($get['per-page']);
        $key=[__METHOD__,$tag,$get];
        $value=$cache->get($key);
        if($value){
            $count=$value;
        }else{
            $count=$query->count('*',$db);
            $cache->set($key,$count,100,new TagDependency(['tags'=>$tag,]));
        }

        $page=new Pagination(['totalCount'=>$count]);
        $data=$query->offset($page->offset)->limit($page->limit)->all($db);

        return $this->renderPartial('index',['page'=>$page,'data'=>$data]);
    }

    public function actionAddKill(){
        $model = new GrabProduct();
        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'id' => $model->id]);
        } else {
            return $this->render('add-kill', [
                'model' => $model,
            ]);
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    protected function findModel($id)
    {
        if (($model = GrabProduct::findOne($id)) !== null) {
            return $model;
        } else {
        }
    }

    public function actionGrab(){
        \yii::$app->response->format=Response::FORMAT_JSON;

//        $redis_grab=\yii::$app->redis_grab;

        $grabId=\yii::$app->request->get('grab_id');
        $userId=\yii::$app->request->get('user_id');

        $grab=new GrabRedis();
        $status=$grab->grab($grabId,$userId);
        return $status;
    }

    protected function setHashRedis($key,$values,$db){
        $field=[];
        foreach ($values as $k => $v){
            $field[]=$k;
            $field[]=$v;
        }
        array_unshift($field,$key);
        return $db->executeCommand('HMSET',$field);
    }


}