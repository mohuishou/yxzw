<?php

namespace App\Console;

use App\Jobs\SignJob;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function(){
            $users=User::all();
            foreach ($users as $user){
                //今天是否已更新
                $yxzw=$user->yxzw->where("month",(int)date("m"))->first();
                if(!isset($yxzw["updated_at"])){
                    dispatch(new SignJob($user));
                }else{
                    $update_time= strtotime($yxzw["updated_at"]);
                    $update_day=date("d",$update_time);
                    $now=date("d");
                    if(($now-$update_day)>0){
                        dispatch(new SignJob($user));
                    }
                }
            }
        })->everyMinute();
    }
}
