<?php
namespace common\tool;

use yii\base\Exception;
use yii\helpers\FileHelper;
use yii\web\HttpException;

class ExportCsv {

    /**
     * 记录下用户的导出入口，可以做限制
     * $parameter = [
     *      'query'=>$query,
            'header'=>['trade_sn'=>"交易流水号",'order_sn'=>"业务单号",'pintai'=>"平台",'nick_name'=>"用户",
     *          'trade_log_type'=>"交易类型",'name'=>"业务名称",'money'=>"金额",'user_pay_way'=>"支付方式",'trade_status'=>"交易状态",
     *              'add_time'=>"创建时间",'pay_time'=>"付款时间",'trade_time'=>"交易时间",'end_time'=>"结束时间",'info'=>"备注"],
     *      'name'=>'交易记录_trade_log'.date("Y-m-d_H_i").'.csv',
     *      'statusColum'=>[                           //状态码
     *           'trade_log_type'=>\common\models\money\log\TradeLog::$trade_log_type,
     *           'user_pay_way'=>\yii\helpers\ArrayHelper::getColumn(\common\models\money\pay\PayWay::$user_pay_way,'name',true),
     *           'trade_status'=>\common\models\money\log\TradeLog::$trade_status,
     *           'pintai'=>\yii\helpers\ArrayHelper::getColumn(\common\models\common\SystemConfig::$pintai,'ChineseName',true),
     *     ],
     *    'timeColum'=>['include'=>['add_time'],'except'=>['edit_time']], //时间处理  except排除 include包括
     * ]
     *
     * $success 回调 可以是 和ajax分页 一样
     *  exportQuery($parameter,function($row,$parameter){ //$row 是数据行
     *      return $row;
     * });
     *
     *
     * 也可以是
     *  exportQuery($parameter,['app\tool\Deal','deal']); 使用 app\tool\Deal   中的deal方法处理 注意该方法是静态的
     *  deal($row,$parameter)
     *
     * @param $parameter
     * @param $success  //回调函数
     */
    public static function exportQuery($parameter,$success=null){
        set_time_limit(0);
        ini_set("memory_limit", "512M");
        $status=self::limitExport();
        if( $status['status']){
            if($success==null) $success=['common\tool\ExportCsv','deal'];
            try{
                $key=md5(serialize($parameter).serialize(self::getUserCacheKey()));

                $cache=\yii::$app->getCache()->get(self::getUserCacheKey());
                $filePath='EP'.date("Y_m_d").'/'.md5(serialize(self::getUserCacheKey())).'/'.$key;
                if(!empty($cache[$key]['url'])){
                    if(file_exists(\yii::getAlias('@webroot'.$cache[$key]['url']))){
                        return $status=['status'=>true,'msg'=>'导出成功','url'=>$cache[$key]['url']];
                    }
                }

                if(file_exists(\yii::getAlias('@webroot/assets/'.$filePath.'/'.$parameter['name']))){
                    self::exporPath($parameter['name'],$key,$filePath);
                    return $status=['status'=>true,'msg'=>'导出成功','url'=>'/assets/'.$filePath.'/'.$parameter['name']];
                }

                $parameter['target']=self::exporPath($parameter['name'],$key,$filePath).'/';

                self::export($parameter,$success);
                $status['msg']='导出成功';
                $status['url']=isset($cache[$key]['url'])?$cache[$key]['url']:false;
                if($status['url']==false){
                    $status=['status'=>false,'msg'=>'导出失败','asdf'=>$key,'asdfsad'=>self::getUserCacheKey(),'cache'=>$cache];
                }
            }catch (Exception $e){
                $status['msg']=$e->getMessage();
            }
        }
        return $status;
    }

    public static function exporPath($filename,$codition,$filePath){
        $path=\yii::getAlias('@webroot/assets/'.$filePath);
        FileHelper::createDirectory($path);
        $key=self::getUserCacheKey();
        $userCache=\yii::$app->getCache()->get($key);
        $userCache[$codition]['path']=$path;
        $userCache[$codition]['file']=$filename;
        $userCache[$codition]['url']='/assets/'.$filePath.'/'.$filename;
        \yii::$app->getCache()->set($key,$userCache,3600*24);
        return $path;
    }

    public static function getUserCacheKey(){
        $user=\yii::$app->session->get('admins')?:( \yii::$app->session->get('user')?:'');
        return $key=[\yii::$app->id,'user'=>$user];
    }

    public static function limitExport(){
        $key=self::getUserCacheKey();
        $userCache=\yii::$app->getCache()->get($key);
//        if(empty($userCache)){
//            $userCache=[
//                'export_count'=>1,
//                'export_time'=>time(),
//                'expire'=>strtotime(date("Y-m-d")." +1day"),
//            ];
//            \yii::$app->getCache()->set($key,$userCache,3600*24);
//        }else{
//            if($userCache['expire']>time()){
//                if((time()-$userCache['export_time'])<60){
//                    return ['status'=>false,'msg'=>'您的导出太频繁，请在'.(time()-$userCache['export_time']).'s后导出'];
//                }
//                if($userCache['export_count']>10){
//                    return ['status'=>false,'msg'=>'您的今天导出太频繁，请明天再导出'];
//                }
//                $userCache['export_count']++;
//                $userCache['export_time']=time();
//                \yii::$app->getCache()->set($key,$userCache,3600*24);
//            }else{
//                $userCache=[
//                    'export_count'=>1,
//                    'export_time'=>time(),
//                    'expire'=>strtotime(date("Y-m-d")." +1day"),
//                ];
//                \yii::$app->getCache()->set($key,$userCache,3600*24);
//            }
//        }
        return ['status'=>true];
    }

    /**
     * 获取数据后的回调
     * @param $row
     * @param $parameter
     * @return array
     */
    public static function deal($row,$parameter){
        /**
         * 排序且去除 多余列
         * $row=array_intersect_key($row,$parameter['header']); 无排序功能
         */
        if(!empty($parameter['header'])){
            $data=[];
            foreach ($parameter['header'] as $k=>$v){
                $data[$k]=isset($row[$k])?$row[$k]:'';
            }
            $row=$data;
        }

        /**
         * 处理时间
         */
        $timeInclude=['add_time','edit_time','pay_time','finish_time','end_time','start_time'];
        if(!empty($parameter['timeColum'])){
            if(!empty($parameter['timeColum']['include'])){
                $timeInclude=$parameter['timeColum']['include'];
            }
            if(!empty($parameter['timeColum']['expcect'])){
                $timeInclude=array_diff($timeInclude,$parameter['timeColum']['except']);
            }
        }
        $keys=array_keys($row);
        $timeKeys=array_intersect($keys,$timeInclude);
        foreach ($timeKeys as $timeKey){
            $row[$timeKey]=date('Y-m-d H:i:s',$row[$timeKey]);
        }

        /**
         * 处理状态码
         */
        if(!empty($parameter['statusColum'])){
            foreach ($parameter['statusColum'] as $k=>$v){
                $row[$k]=$row[$k]!==null&&isset($v[$row[$k]])?$v[$row[$k]]:'';
            }
        }
        return $row;
    }

    /**
     * 核心代码底层的调用，没有问题
     * $parameter可以有多种方式，支持多方式导出
     * 1、string $parameter = 'yzb_test'  查询表yzb_test
     *
     * array $parameter
     * 2、 $parameter=[
     *      'table'=>'yzb_test',       //有table字段的情况下
     *      'limit'=>$limit,
     *      'orderby'=>$orderby,
     *      'condition'=>$confition  //where子句
     *      'fields'=>$select      //select
     *      'header'=>$header      //导出表的列名
     *      'name'=>$filename       //导出文件名
     * ],
     * 3、 $parameter =[
     *      'sql'=>$sql         //sql语句
     *      'bind'=>$where      //条件
     *      'name'=>$filename       //导出文件名
     * ]
     * 4、$parameter = [
     *      'query'=>$query     //$query对象
     *      'name'=>$filename       //导出文件名
     * ]
     * 5、$parameter=[
     *      'reader'=>$reader   //
     *      'name'=>$filename       //导出文件名
     * ]
     * 6、$parameter=[
     *      'data'=>$data   //直接是数据     //数据量小于5万 还行，大了就不行了
     *      'name'=>$filename       //导出文件名
     * ]
     * 其他参数
     * $parameter[
     *      'fp'=>$file         //可选参数，写入的文件
     *      'target'=>$path     //文件路径 如果没有，直接变成下载
     *
     *      'timeColum'=>['include'=>['add_time'],'except'=>['edit_time']], //时间处理
     *      'statusColum'=>['status'=>[0=>'未通过'],1=>'通过'],  //状态码
     * ]
     *
     * 二 $sucess 执行成功的回调
     * @param $parameter
     * @param $sucess
     * @return bool
     * @throws \yii\web\HttpException
     */
    public static function export($parameter,$sucess=null){
        if (is_string($parameter)) {
            $parameter = ['table' => $parameter];
            $db= \Yii::$app->getDb();
            $tables = $db->schema->getTableNames();
        }
        if (is_array($parameter)) {
            if ( ! empty($parameter['table'])) {
                if ( ! in_array($parameter['table'], $tables)) {
                    throw new \yii\web\HttpException(500, "table '{$parameter['table']}' not exists!");
                }
                $query = (new \yii\db\Query)->from($parameter['table']);
                if ( ! empty($parameter['limit'])) {
                    $query->limit($parameter['limit']);
                }
                if ( ! empty($parameter['orderby'])) {
                    $query->orderBy($parameter['orderby']);
                }
                if ( ! empty($parameter['condition'])) {
                    $query->where($parameter['condition']);
                }

                if ( ! empty($parameter['fields'])) {
                    $columns = $db->getSchema()->getTableSchema($parameter['table'])->getColumnNames();
                    $parameter['fields'] = array_intersect($parameter['fields'], $columns);
                    if ( ! empty($parameter['exceptFields'])) {
                        $parameter['fields'] = array_diff($columns, $parameter['fields']);
                    }
                    $query->select($parameter['fields']);
                }
                if (empty($parameter['header'])) {
                    $parameter['header'] = [];
                    foreach($db->getSchema()->getTableSchema($parameter['table'])->columns as $item) {
                        if (empty($parameter['fields']) or (! empty($parameter['fields']) && in_array($item->name, $parameter['fields']))) {
                            $parameter['header'][] = (empty($item->comment) ? \yii\helpers\Inflector::camel2words($item->name, true) : $item->comment) . '(' . $item->name . ')';
                        }
                    }
                }
                if (empty($parameter['name'])) {
                    $parameter['name'] = $parameter['table'] . '.csv';
                }
            } elseif ( ! empty($parameter['sql'])) {
                $command = $db->createCommand($parameter['sql']);
                if ( ! empty($parameter['bind'])) {
                    $command->bindValues($parameter['bind']);
                }
                $reader = $command->query();
            } elseif (! empty($parameter['query']) && empty($query)) {
                $query = $parameter['query'];
            } elseif (! empty($parameter['reader']) && empty($reader)) {
                $reader = $parameter['reader'];
            } elseif (empty($parameter['data'])) {
                throw new \yii\web\HttpException(500, "Not a valid parameter!");
            }

            if (empty($parameter['name'])) {
                $parameter['name'] = date('Y-m-d_H-i-s') . '.csv';
            }

            if ( ! empty($parameter['fp']) && is_resource($parameter['fp'])) {
                $fp =& $parameter['fp'];
            } else {
                if ( ! empty($parameter['target'])) {
                    $fp = fopen($parameter['target'] . $parameter['name'], 'w');
                } else {
                    header('Content-Type: text/csv');
                    header("Content-Disposition: attachment;filename={$parameter['name']}");
                    $fp = fopen('php://output', 'w');
                }
            }

            fwrite($fp,chr(0xEF).chr(0xBB).chr(0xBF));
            if ( ! empty($parameter['header']) && is_array($parameter['header'])) {
                fputcsv($fp, $parameter['header']);
            }
            if (isset($query)) {
                foreach ($query->each() as $row) {
                    if($sucess instanceof \Closure ||((is_array($sucess) && is_callable($sucess)))){
                        try{
                            $row = call_user_func_array($sucess,['row'=>$row,'parameter'=>$parameter]);
                        }catch (Exception $e){
                            throw new HttpException(200,$e->getMessage());
                        }
                    }
                    fputcsv($fp, $row);
                }

            } elseif (isset($reader)) {
                foreach ($reader as $row) {
                    if($sucess instanceof \Closure ||((is_array($sucess) && is_callable($sucess)))){
                        try{
                            $row = call_user_func_array($sucess,['row'=>$row,'parameter'=>$parameter]);
                        }catch (Exception $e){
                            throw new HttpException(200,$e->getMessage());
                        }
                    }
                    fputcsv($fp, $row);
                }
            } else if (isset($parameter['data'])) {
                foreach ($parameter['data'] as $row) {
                    if($sucess instanceof \Closure ||((is_array($sucess) && is_callable($sucess)))){
                        try{
                            $row = call_user_func_array($sucess,['row'=>$row,'parameter'=>$parameter]);
                        }catch (Exception $e){
                            throw new HttpException(200,$e->getMessage());
                        }
                    }
                    fputcsv($fp, $row);
                }
            }
            return true;
        }
    }




}