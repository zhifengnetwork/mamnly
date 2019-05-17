<?php
/**
 * debug
 */
namespace app\api\controller;
use app\common\model\Users;
use think\Db;
use think\Controller;

class Updata extends Controller
{

    public function delectyeji()
    {
            $where_goods = [
           // 'og.is_send'    => 1,
            'og.prom_type' =>0,//只有普通订单才算业绩
            //'u.first_leader'=>$v['user_id'],
            //"og.goods_num" =>'>1',
            //'od.pay_status'=>1,

            'gs.sign_free_receive'=>['IN','1,2'],
            
          ];
    $order_goods = Db::name('order_goods')->alias('og')
             ->field('og.order_id,o.order_sn,og.goods_price,og.prom_type,og.goods_name,og.goods_id,gs.sign_free_receive,o.user_id')
             ->where($where_goods)
             ->join('goods gs','gs.goods_id=og.goods_id','LEFT')
             ->join('order o','og.order_id=o.order_id','LEFT')
             ->order('og.order_id desc')
             ->limit(3000,100)
             ->select();
      //  echo count($order_goods);exit;
    foreach($order_goods as $k=>$v)
    {
        $order_amount = $v['goods_price'];
    //加个人业绩(下单人)
        $order_id=$v["order_id"];
        $where="note='订单{$order_id}业绩'";
        $cunzai = M('agent_performance_log')->where($where)->select();
        //存在
        if(!empty($cunzai))
        {
            foreach ($cunzai as $ke=>$ye)
            {
                //删除个人业绩
                if($ye['user_id']==$v['user_id'])
                {
               // $data['ind_per'] = $ye['ind_per'] - $order_amount;
                 $ind_per = M('agent_performance')->where(['user_id'=>$ye['user_id']])->value('ind_per');
                 if($ind_per>=$order_amount)
                 {
                      M('agent_performance')->where(['user_id'=>$ye['user_id']])->setDec('ind_per',$order_amount);

                    //agent_performance_log($user_id,$order_amount,$order_id);
                     echo '成功删除ID:'.$ye["user_id"].'个人业绩<br/>';

                     }else
                     {
                         echo '业绩不够删除';
                     }
               

                }
                //删除团队业绩
                else
                {
                  $agent_per = M('agent_performance')->where(['user_id'=>$ye['user_id']])->value('team_per');
                 if($agent_per>=$order_amount)
                 {
                     M('agent_performance')->where(['user_id'=>$ye['user_id']])->setDec('team_per',$order_amount);
                    echo '成功删除ID:'.$ye["user_id"].'团队业绩<br/>';
                  }else
                  {
                    echo '团队业绩不够删除';
                  }

                }
                 Db::name('agent_performance_log')->where('performance_id', $ye['performance_id'])->delete();
                 echo '成功删除ID:'.$ye["performance_id"].'业绩记录<br/>';
                
             }
        }else
        {
            echo '已删除或找不到业绩记录偏号sn：'.$v["order_sn"].'--'. $where.'<br/>';
        }
    }
    }


    public function jiayeji()
    {
        $ps =I('ps');
        $pe =!empty($ps)?$ps:0;
            $where_goods = [
           // 'og.is_send'    => 1,
            'og.prom_type' =>0,//只有普通订单才算业绩
            //'u.first_leader'=>$v['user_id'],
            //"og.goods_num" =>'>1',
            'o.pay_status'=>1,

            'gs.sign_free_receive'=>0,
            
          ];
    $order_goods = Db::name('order_goods')->alias('og')
             ->field('og.order_id,o.order_sn,og.goods_price,og.prom_type,og.goods_name,og.goods_id as ogoods_id,gs.sign_free_receive,o.user_id,og.goods_num,o.add_time as oaddtime,pay_time,og.rec_id')
             ->where($where_goods)
             ->join('goods gs','gs.goods_id=og.goods_id','LEFT')
             ->join('order o','og.order_id=o.order_id','LEFT')
             ->order('og.order_id desc')
             ->limit($pe,50)
             ->select();
         //print_R(Db::name('order_goods')->getlastsql());exit;
    foreach($order_goods as $ke=>$ve)
    {
        $order_amount = $ve['goods_price'] * $ve['goods_num'];
        $order_id=$ve["order_id"];
        $user_id = $ve["user_id"];

        //$where="note='订单{$order_id}业绩'";
        $where = "ogoods_id=".$ve['rec_id'];

        $agent_performance = M('agent_performance_log_new')->where($where)->find();
        if(empty($agent_performance))
        {
         //加个人业绩(下单人)
        $cunzai = M('agent_performance_new')->where(['user_id'=>$user_id])->find();
        //存在
        if($cunzai){
            $data_new['ind_per'] = $cunzai['ind_per'] + $order_amount;
            $data_new['update_time'] = date('Y-m-d H:i:s');
            $res = M('agent_performance_new')->where(['user_id'=>$user_id])->save($data_new);

            agent_performance_log_new($user_id,$order_amount,$order_id,$ve['rec_id'],$ve['pay_time']);
              echo '成功加上存在ID:'.$v["user_id"].'个人业绩<br/>';
        }else{

            $data['user_id'] =  $user_id;
            $data['ind_per'] =  $order_amount;
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['update_time'] = date('Y-m-d H:i:s');
            $res = M('agent_performance_new')->add($data);

            agent_performance_log_new($user_id,$order_amount,$order_id,$ve['rec_id'],$ve['pay_time']);
            echo '成功加上不存在ID:'.$v["user_id"].'个人业绩<br/>';
        }




          $first_leader = M('users')->where(['user_id'=>$user_id])->value('first_leader');
           $arr = get_uper_user($first_leader);
  


        //加 团队业绩
         foreach($arr['recUser'] as $k => $v){
       
            $cunzais = M('agent_performance_new')->where(['user_id'=>$v['user_id']])->find();
            //存在
            if($cunzais && !empty($cunzais)){
                $data11['team_per'] = $cunzais['team_per'] + $order_amount;
                $data11['update_time'] = date('Y-m-d H:i:s');
                $res = M('agent_performance_new')->where(['user_id'=>$v['user_id']])->update($data11);
                echo '成功加上存在ID:'.$v["user_id"].'团队业绩<br/>';

            }else{

                $data1['user_id'] =  $v['user_id'];
                $data1['team_per'] =  $order_amount;
                $data1['create_time'] = date('Y-m-d H:i:s');
                $data1['update_time'] = date('Y-m-d H:i:s');
                $res = M('agent_performance_new')->add($data1);
                  echo '成功加上不存在ID:'.$v["user_id"].'团队业绩<br/>';
            }

            
            agent_performance_log_new($v['user_id'],$order_amount,$order_id,$ve['rec_id'],$ve['pay_time']);
            
        }

        }else
        {
            echo '已加上或找不到业绩记录'. $where.'<br/>';
        }

       
        echo $i++;
    }
    }


     public function jiayeji_new()
    {
        
        $ps =I('ps');
        $pe =!empty($ps)?$ps:0;
            $where_goods = [
           // 'og.is_send'    => 1,
            'og.prom_type' =>0,//只有普通订单才算业绩
            //'u.first_leader'=>$v['user_id'],
            //"og.goods_num" =>'>1',
            'o.pay_status'=>1,

            'gs.sign_free_receive'=>0,
            
          ];
    $order_goods = Db::name('order_goods')->alias('og')
             ->field('og.order_id,o.order_sn,og.goods_price,og.prom_type,og.goods_name,og.goods_id as ogoods_id,gs.sign_free_receive,o.user_id,og.goods_num,o.add_time as oaddtime,pay_time,og.rec_id,gs.cat_id')
             ->where($where_goods)
             ->join('goods gs','gs.goods_id=og.goods_id','LEFT')
             ->join('order o','og.order_id=o.order_id','LEFT')
             ->order('og.order_id desc')
             ->limit($pe,50)
             ->select();
         //print_R(Db::name('order_goods')->getlastsql());exit;
    foreach($order_goods as $ke=>$ve)
    {
        $order_amount = $ve['goods_price'] * $ve['goods_num'];
        $order_id=$ve["order_id"];
        $user_id = $ve["user_id"];

        //$where="note='订单{$order_id}业绩'";
        $where = "ogoods_id=".$ve['rec_id'];

        $agent_performance = M('agent_performance_log_new')->where($where)->find();
        if(empty($agent_performance))
        {
         //加个人业绩(下单人)
        $cunzai = M('agent_performance_new')->where(['user_id'=>$user_id])->find();
        //存在
        if($cunzai){
            $data_new['ind_per'] = $cunzai['ind_per'] + $order_amount;
            $data_new['update_time'] = date('Y-m-d H:i:s');
            $res = M('agent_performance_new')->where(['user_id'=>$user_id])->save($data_new);

            agent_performance_log_new($user_id,$order_amount,$order_id,$ve['rec_id'],$ve['pay_time']);
              echo '成功加上存在ID:'.$v["user_id"].'个人业绩<br/>';
        }else{

            $data['user_id'] =  $user_id;
            $data['ind_per'] =  $order_amount;
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['update_time'] = date('Y-m-d H:i:s');
            $res = M('agent_performance_new')->add($data);

            agent_performance_log_new($user_id,$order_amount,$order_id,$ve['rec_id'],$ve['pay_time']);
            echo '成功加上不存在ID:'.$v["user_id"].'个人业绩<br/>';
        }


          $first_leader = M('users')->where(['user_id'=>$user_id])->value('first_leader');
           $arr = get_uper_user($first_leader);
  

        if($ve['cat_id']==8)
        {
            //加 团队业绩
             foreach($arr['recUser'] as $k => $v){
           
                $cunzais = M('agent_performance_new')->where(['user_id'=>$v['user_id']])->find();
                //存在
                if($cunzais && !empty($cunzais)){
                    $data11['team_per'] = $cunzais['team_per'] + $order_amount;
                    $data11['update_time'] = date('Y-m-d H:i:s');
                    $res = M('agent_performance_new')->where(['user_id'=>$v['user_id']])->update($data11);
                    echo '成功加上存在ID:'.$v["user_id"].'团队业绩<br/>';

                }else{

                    $data1['user_id'] =  $v['user_id'];
                    $data1['team_per'] =  $order_amount;
                    $data1['create_time'] = date('Y-m-d H:i:s');
                    $data1['update_time'] = date('Y-m-d H:i:s');
                    $res = M('agent_performance_new')->add($data1);
                      echo '成功加上不存在ID:'.$v["user_id"].'团队业绩<br/>';
                }

                
                agent_performance_log_new($v['user_id'],$order_amount,$order_id,$ve['rec_id'],$ve['pay_time']);
                
            }

        }else
        {
            $agent_end=0;
            //加 团队业绩
             foreach($arr['recUser'] as $k => $v){
               if($agent_end==0) //判断是否已加业绩，加了就不往下加了
              {
                    if($v['level']==4 || $v['level']==5)
                    {
               
                    $cunzais_s = M('agent_performance_new')->where(['user_id'=>$v['user_id']])->find();
                    //存在
                    if($cunzais_s && !empty($cunzais_s)){
                        $data22['team_per'] = $cunzais_s['team_per'] + $order_amount;
                        $data22['update_time'] = date('Y-m-d H:i:s');
                        $res = M('agent_performance_new')->where(['user_id'=>$v['user_id']])->update($data22);
                        echo '成功加上存在ID:'.$v["user_id"].'单品团队业绩<br/>';

                    }else{

                        $data2['user_id'] =  $v['user_id'];
                        $data2['team_per'] =  $order_amount;
                        $data2['create_time'] = date('Y-m-d H:i:s');
                        $data2['update_time'] = date('Y-m-d H:i:s');
                        $res = M('agent_performance_new')->add($data2);
                          echo '成功加上不存在ID:'.$v["user_id"].'单品团队业绩<br/>';
                    }

                    
                    agent_performance_log_new($v['user_id'],$order_amount,$order_id,$ve['rec_id'],$ve['pay_time']);
                    $agent_end =1; 

                   }
               }
            

            }
        }

            echo $i++;
        }else
        {
            echo '已加上或找不到业绩记录'. $where.'<br/>';
        }
     }
   }
   public function apiadd_add_agent_performance()
   {
     $order_id =I('order_id');
      add_agent_performance($order_id);
       echo '已加上或找不到业绩记录'. $where.'<br/>';
   }

    
}
    function agent_performance_log_new($user_id,$order_amount,$order_id,$ogoods_id,$time){

    $log = array(
        'user_id'=>$user_id,
        'money'=>$order_amount,
        'create_time'=>date('Y-m-d H:i:s',$time),
        'note'=>'订单'.$order_id.'业绩',
        'order_id'=>$order_id,
        'ogoods_id'=>$ogoods_id,
    );
    M('agent_performance_log_new')->add($log);
    }
