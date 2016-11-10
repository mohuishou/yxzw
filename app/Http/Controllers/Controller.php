<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function error($msg,$code=10000){
        if(is_array($msg)){
            return response()->json($msg,400);
        }
        return response()->json([
            "error"=>$msg,
            "code"=>$code
        ],400);
    }

    /**
     * @author mohuishou<1@lailin.xyz>
     * @param $msg
     * @param array $data
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function success($msg,$data=[]){
        $success=[
            'status'=>1,
            'msg'=>$msg,
            'data'=>$data
        ];
        return response()->json($success,200);
    }
}
