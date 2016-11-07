<?php
date_default_timezone_set("PRC");
require_once "vendor/autoload.php";
include_once 'lib/bmob/BmobObject.class.php';
include_once 'class/Mail.php';
set_time_limit(0);//关闭时间限制
//自动签到
$user_db=new BmobObject("user");

try {
//获取所有用户信息（默认100条）
    $res = $user_db->get("");
}catch (Exception $e){
    echo $e;
}


//连接优选在沃表
$yxzw_db=new BmobObject("yxzw");

//优选在沃数据初始化
$yxzw_data=[
    "month"=>(int)date("m"),
];


foreach ($res->results as $k=>$v){
    //已关闭自动签到
    if(!$v->auto_status){
        continue;
    }

    //剩余自动签到数目已经不足以自动签到，自动退出
    if($v->sign_num<1){
        continue;
    }

    //签到
    $yxzw_sign=new \Mohuishou\Lib\YxzwSign();
    $auto_sign=$yxzw_sign->index($v->phone,$v->password);
    $msg_sign_num=$v->sign_num;

    //构造签到数据
    $yxzw_data["all_sign"]=$auto_sign["all_sign"];
    $yxzw_data["sign_info"]=$auto_sign["sign_info"];

    //获取签到状态，并做出相应操作
    $auto_status=$auto_sign['status'];
    
    $status="成功";
    try{
        switch ($auto_status){
            case 1:
                $msg_status="签到成功！";
                update($yxzw_db,$user->objectId,$yxzw_data,$user_db,$auto_sign["wb"],$auto_status);
                break;
            case 2:
                $msg_status="今日已签到，不会扣除自动签到次数";
                $status="已签到";
                update($yxzw_db,$user->objectId,$yxzw_data,$user_db,$auto_sign["wb"],$auto_status);
                break;
            case 0:
                $status="失败";
                $msg_status="签到失败，请手动尝试！";
                break;
            case -1:
                $status="失败";
                $msg_status="登录失败，请检查您的手机号与密码！";
                break;
        }
        $ret['status']=$auto_status;
        $ret['info']=$msg_status;
        return $ret;

    }catch (Exception $e){
        echo $e;
    }


    //发送邮件
    $msg="感谢您使用优选在沃在线签到系统，您的使用情况如下：<br />";
    $msg .="【手机号】：".$v->phone."<br />";
    $msg .="【签到情况】：".$msg_status."<br />";
    $msg .="【本月签到次数】：".$auto_sign["all_sign"]."<br />";
    $msg .="【本月自动签到次数】：".$auto_sign["all_sign"]."<br />";
    $msg .="【剩余自动签到次数】：".$msg_sign_num."<br />";
    $msg .="【签到时间】：".date("Y-m-d H:i:s")."<br />";
    $msg .="详情请点击：http://y.lxl520.com 登录查看，感谢您的使用";
//    echo $msg;
    if(isset($v->email)){
        $re=sendMail($msg,$v->email,$status);
        print_r($re);
    }


}

/**
 * 更新数据库
 * @author mohuishou<1@lailin.xyz>
 * @param $yxzw_db
 * @param $uid
 * @param $data
 * @param $user_db
 * @param $wb
 * @param $auto_status
 */
function update($yxzw_db,$uid,$data,$user_db,$wb,$auto_status){
    $res=$yxzw_db->get("",array('where={"month":'.(int)date("m").',"user":{"__type":"Pointer","className":"user","objectId":"'.$uid.'"}}'));

    if(empty($res->results)){
        $res=$yxzw_db->addRelPointer(array(array("user","user",$uid)));
        $res2=$yxzw_db->update($res->objectId,$data);
    }else{
        $res2=$yxzw_db->update($res->results[0]->objectId,$data);

    }

    if($auto_status==1){
        $yxzw_db->increment($res->results[0]->objectId,"auto_sign",array(1)); //自动签到自动+1
    }


    $user_data=["wb"=>$wb];
    $user_db->update($uid,$user_data);

}

