<?php

namespace App\Http\Controllers;
use App\Model\User;
use App\Jobs\SignJob;
class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function test(){
      $user=User::where("phone",15680698256)->first();
      dispatch(new SignJob($user));
    }

    //
}
