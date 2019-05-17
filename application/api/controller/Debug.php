<?php
/**
 * debug
 */
namespace app\api\controller;
use app\common\model\Users;
use think\Db;
use think\Controller;

class Debug extends Controller
{

    /**
     * 数据错误
     */
    public function ss(){

        $list = M('oauth_users')->alias('o')->join('users u', 'o.user_id = u.user_id')
        ->field('o.openid as oooooo_openid,u.openid as uuuuuu_openid,o.user_id,o.nickname as ooooo_nickname,u.nickname as uuuuu_nickname,u.old_openid')
        ->where('o.openid','exp','!= u.openid')
      
        ->select();
        foreach($list as $k=>$v)
        {
            $list[$k]['orders'] = M('order')->where('user_id='.$v['user_id'])->select();
        }

        dump($list);

    }


    /**
     * 模板消息测试
     */
    public function msg(){

        $order_sn = I('order_sn');
        if(!$order_sn){
            exit('order_sn不存在');
        }

        $user_id = I('user_id');
        if(!$user_id){
            exit('user_id不存在');
        }

        $yongjin = I('yongjin');
        if(!$yongjin){
            exit('yongjin不存在');
        }

        $logic = new \app\common\logic\TemplateMessage();
        $openid = M('users')->where(['user_id'=>$user_id])->value('openid');
        $goods_name = '测试';
        
        $res = $logic->yongjin($openid,$goods_name,$yongjin,$order_sn);

        dump($res);
    }

    /**
     * 查用户信息
     */
    public function info(){
        $user_id = I('user_id');
        if(!$user_id){
            exit('参数user_id不存在');
        }

        $openid = M('users')->where(['user_id'=>$user_id])->value('openid');
        $access_token = access_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        $resp = httpRequest($url, "GET");
        $res = json_decode($resp, true);

        dump($res);

    }




    /**
     * 查找重复值
     */
    public function aa(){
        $list = Db::query("select openid,count(*) as count from tp_users  WHERE openid!='' group by openid having count > 1 limit 1") ;
        $openid = $list[0]['openid'];
        $users = M('users')->where(['openid'=>$openid])->field('user_id,level,openid,old_openid,user_money,first_leader,nickname,head_pic')->select();
        foreach($users as $k => $v){
            $user_id = $v['user_id'];
            $v['order_num'] = M('order')->where(['user_id'=>$v['user_id']])->count();
            $v['uuuuuu_id'] = M('oauth_users')->where(['user_id'=>$user_id])->value('user_id');
            dump($v);
            echo "<img src=".$v['head_pic'].">";
            echo "<a href=/api/debug/del?user_id=".$user_id."&re=aa>删除这个</a>";
        }
    }

    /**
     * 查找重复值
     */
    public function bb(){
        $list = Db::query("select openid,user_id,count(*) as count from tp_users  WHERE openid!='' group by openid having count > 1 order by user_id DESC  limit 1 ") ;
        $openid = $list[0]['openid'];
        $users = M('users')->where(['openid'=>$openid])->field('user_id,level,openid,old_openid,user_money,first_leader,nickname,head_pic')->select();
        foreach($users as $k => $v){
            $user_id = $v['user_id'];
            $v['order_num'] = M('order')->where(['user_id'=>$v['user_id']])->count();
            $v['uuuuuu_id'] = M('oauth_users')->where(['user_id'=>$user_id])->value('user_id');
            dump($v);
            echo "<img src=".$v['head_pic'].">";
            echo "<a href=/api/debug/del?user_id=".$user_id."&re=bb>删除这个</a>";
        }
    }


    public function del(){
        ecit('禁止删除');
        $user_id = I('user_id');
        if($user_id){
            M('users')->where(['user_id'=>$user_id])->delete();
            M('oauth_users')->where(['user_id'=>$user_id])->delete();
        }
        $re = I('re');
        $this->redirect($re);
    }

    public function index()
    {
        $user_id = 17951598;
        $user = M('users')->where(['user_id'=>$user_id])->find();
        dump($user);
        $use = M('oauth_users')->where(['user_id'=>$user_id])->find();
        dump($use);
        $user = M('users')->where(['user_id'=>$user_id])->delete();
        $use = M('oauth_users')->where(['user_id'=>$user_id])->delete();
    }

    /**
     * 给空的 openid 赋值  user_id
     */
    public function set(){
        exit('ok');
        $limit = I('limit');
        $list = Db::query("select user_id,openid,old_openid from tp_users  WHERE openid = '' limit ".$limit) ;
        dump($list);
        foreach($list as $k => $v){
            if($v){
                M('users')->where(['user_id'=>$v['user_id']])->update(['openid'=>$v['old_openid']]);
            }
        }
    }
}
