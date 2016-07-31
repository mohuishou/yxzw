<?php
date_default_timezone_set("PRC");
require_once "vendor/autoload.php";
include_once 'lib/bmob/BmobObject.class.php';
//自动签到
$user_db=new BmobObject("user");
$res=$user_db->get("");

$yxzw_db=new BmobObject("yxzw");
$yxzw_data=[
    "month"=>(int)date("m"),
    "all_sign"=>0,
    "sign_info"=>"",
    "auto_sign"=>0,
];
foreach ($res->results as $k=>$v){
    //已关闭自动签到
    if(!$v->auto_status){
        continue;
    }

    //剩余自动签到数目已经不足以自动签到，自动退出
    if($v->sign_num<1){

    }



    $yxzw_sign=new \Mohuishou\Lib\YxzwSign();
    $auto_sign=$yxzw_sign->index($v->phone,$v->password);
    $yxzw_data["all_sign"]=$auto_sign["all_sign"];
    $yxzw_data["sign_info"]=$auto_sign["sign_info"];
    updateYxzw($yxzw_db,$v->objectId,$yxzw_data);
    $auto_status=$auto_sign['status'];
    switch ($auto_status){
        case 1:
            $user_db->increment($v->objectId,"sign_num",array(-1)); //签到总数自动-1


    }

}

function updateYxzw($yxzw_db,$uid,$data){
    $res=$yxzw_db->get("",array('where={"month":'.(int)date("m").',"user":{"__type":"Pointer","className":"user","objectId":"'.$uid.'"}}'));
    print_r($res);
//    echo 'where={"user":{"__type":"Pointer","className":"user","objectId":"'.$uid.'"}}';
    if(empty($res->results)){
        $res1=$yxzw_db->addRelPointer(array(array("user","user",$uid)));
        $res2=$yxzw_db->create($data);
        print_r($res1);
    }
    print_r($res);

}

