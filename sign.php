<?php
date_default_timezone_set("PRC");
require_once "vendor/autoload.php";
include_once 'lib/bmob/BmobObject.class.php';

function sign($uid){
    $user_db=new BmobObject("user");
    $user=$user_db->get($uid);
    //连接优选在沃表
    $yxzw_db=new BmobObject("yxzw");
    //优选在沃数据初始化
    $yxzw_data=[
        "month"=>(int)date("m"),
    ];


    //签到
    $yxzw_sign=new \Mohuishou\Lib\YxzwSign();
    $auto_sign=$yxzw_sign->index($user->phone,$user->password);

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