<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-5-18
 * Time: 下午5:57
 */

namespace DavinBao\Mailbox;

class MailWorker {

    public function receiveMail($job, $data){
        \Log::info('This is was written via the MailWorker class at '.time().'.'.json_encode($data));
        //$job->delete();
    }

    public function fire($job, $data){
        sleep(10);
        $date = \Carbon::now();
        $text = "Completed at ".$date;
        $file = storage_path()."/myfirstqueue.txt";

        \File::put($file, $text);

        $job->delete();
    }
}