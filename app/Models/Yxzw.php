<?php
/**
 * Created by PhpStorm.
 * User: lxl
 * Date: 16-11-9
 * Time: 下午8:06
 */

namespace App\Models;

class Yxzw extends BaseModel{

    protected $table="yxzw";

    protected $guarded = [
        'id'
    ];

    public function user(){
        return $this->belongsTo("App\\Models\\User","uid");
    }


}