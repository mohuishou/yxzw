<?php
/**
 * Created by mohuishou<1@lailin.xyz>.
 * User: mohuishou<1@lailin.xyz>
 * Date: 2016/7/31 0031
 * Time: 19:33
 */

function success($info,$url="",$data=""){
    $data=[
        'status'=>1,//1成功,0错误
        "info"=>$info,
        "url"=>$url,
        "data"=>$data
    ];

    echo json_encode($data);
    exit();
}

function error($info,$url=""){
    $data=[
        'status'=>0,//1成功,0错误
        "info"=>$info,
        "url"=>$url
    ];

    echo json_encode($data);
    exit();
}