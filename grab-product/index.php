<?php
/**
 * Created by PhpStorm.
 * User: aa
 * Date: 2018/3/2
 * Time: 10:35
 */
\yii\bootstrap\BootstrapAsset::register($this);
$mode=[0=>'固定',1=>'随机'];
$status=[0=>'不可用',1=>'可用',2=>'到期'];
?>
<?=$this->beginPage()?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>测试</title>
        <script type="text/javascript" src="/public/open/js/sea.js"></script>
        <?=$this->head()?>
    </head>
    <body>
    <?=$this->beginBody()?>
    <div class="container">

        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">数据</h3>
            </div>
            <div class="panel-body">
                <?=\yii\helpers\Html::beginForm([''])?>
                <div class="row">
                    <div class="col-lg-2">
                        <div class="form-group">
                            <span class="">id</span>
                            <input type="text" name="id" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <span class="">user_id</span>
                            <input type="text" name="user_id" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <span class="">app_id</span>
                            <input type="text" name="app_id" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <span class="">interface_id</span>
                            <input type="text" name="interface_id" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-2">
                        <div class="form-group">
                            <span class="">add_time[0]</span>
                            <input type="text" name="add_time[0]" class="form-control">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <span class="">add_time[1]</span>
                            <input type="text" name="add_time[1]" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group">
                        <?=\yii\helpers\Html::submitButton('查询',['class'=>'btn btn-primary'])?>
                    </div>
                </div>
                <?=\yii\helpers\Html::endForm()?>
                <div class="form-group">
                    <a class="btn btn-primary" href='<?=\yii\helpers\Url::to(['add-kill'])?>' target="_blank">添加</a>
                </div>
                <table class="table table-hover table-striped">
                    <thead>
                    <tr>
                        <th>id</th>
                        <th>名称</th>
                        <th>商品id</th>
                        <th>额外参数</th>
                        <th>份数</th>
                        <th>总数</th>
                        <th>模式</th>
                        <th>固定数量</th>
                        <th>剩余</th>
                        <th>状态</th>
                        <th>start_time</th>
                        <th>end_time</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data as $v):?>
                        <tr>
                            <td><?=$v['id']?></td>
                            <td><?=$v['name']?></td>
                            <td><?=$v['product_id']?></td>
                            <td><?=$v['add_params']?></td>
                            <td><?=$v['partition']?></td>
                            <td><?=$v['total']?></td>
                            <td><?=$mode[$v['mode']?:0]?></td>
                            <td><?=$v['num']?></td>
                            <td><?=$v['rest']?></td>
                            <td><?=$status[$v['status']?:0]?></td>
                            <td><?=date('Y-m-d H:i:s',$v['start_time'])?></td>
                            <td><?=date('Y-m-d H:i:s',$v['end_time'])?></td>
                            <td>
                                <a class="btn btn-primary" href="<?=\yii\helpers\Url::to(['update','id'=>$v['id']])?>">修改</a>
                                <a class="btn btn-primary" href="<?=\yii\helpers\Url::to(['grab','grab_id'=>$v['id'],'user_id'=>round(microtime(true),3)*1000]).rand(100,999)?>">抢</a>
                            </td>
                        </tr>
                    <?php endforeach;?>
                    </tbody>
                </table>
               <?=\yii\widgets\LinkPager::widget([
                       'pagination'=>$page,
               ])?>
            </div>
        </div>
    </div>
    <?=$this->endBody()?>
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.11.3.min.js"></script>
    <script>
        $('form').submit(function () {
            alert('提交');
            $.ajax('<?=\yii\helpers\Url::to([''])?>',{
                type:'get',
                data:{a:11},
                success:function (res) {
                    if(res.status==true){
                        alert('成功');
                    }
                },
                dataType:'json'
            });
            return false;
        });
    </script>
    </body>
    </html>
<?=$this->endPage()?>