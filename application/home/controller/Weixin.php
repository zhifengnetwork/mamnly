<?php


namespace app\home\controller;

use app\common\logic\WechatLogic;
use think\Db;

class Weixin
{
    /**
     * 处理接收推送消息
     */
    public function index()
    {
        write_log('微信公众号-----1');
        $data = file_get_contents("php://input");
    	if ($data) {
            write_log('微信公众号-----2'.json_encode($data));
            $re = $this->xmlToArray($data);
            
	    	$url = SITE_URL.'/mobile/message/index?eventkey='.$re['EventKey'].'&openid='.$re['FromUserName'].'&event='.$re['Event'];
	    	httpRequest($url);
        }

        $config = Db::name('wx_user')->find();
        if ($config['wait_access'] == 0) {
            ob_clean();
            exit($_GET["echostr"]);
        }
         write_log('微信公众号-----3');
        $logic = new WechatLogic($config);
        $str = serialize($logic);
        $a = $logic->handleMessage();
        
    }


    public function xmlToArray($xml)
    {
    	$obj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		$json = json_encode($obj);
		$arr = json_decode($json, true);  
		return $arr;
    }
    
}