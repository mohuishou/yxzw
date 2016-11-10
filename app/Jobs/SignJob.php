<?php
/**
 * Created by PhpStorm.
 * User: lxl
 * Date: 16-11-10
 * Time: 上午11:45
 */

namespace App\Jobs;


use App\Http\Controllers\Sign\SignController;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SignJob extends Job
{

    protected $_user;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->_user=$user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SignController $signController)
    {
        $res=$signController->sign($this->_user,true);
        Log::debug(json_encode($res));
        print_r($res);
    }
}