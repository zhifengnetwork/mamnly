<?php
/**
 * Token  API
 */
namespace app\api\controller;
use think\Db;
use think\Controller;

class Token extends Controller
{
    /**
     * 获取 access_token
     */
    public function access_token(){
        exit(access_token());
    }

    /**
     * 测试
     */
    public function test(){
         

        $logic = new \app\common\logic\TemplateMessage();
        $openid = 'okGVu1VH0v-JofMu_4XFMD4WNZ9M';
        $goods_name = '一坨屎';
        $yongjin = '2.50';
        $order_sn = '12345678910';
        $res = $logic->yongjin($openid,$goods_name,$yongjin,$order_sn);
        dump($res);

    }
}
