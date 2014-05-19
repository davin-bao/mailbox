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
        //$job->delete();
        \Log::info('receiving...');
        $remoteBox = new RemoteMailbox(
          'imap.exmail.qq.com',
          'bwj@zhiyee.com',
          'A!b2c3d4',
          'imap',
          '143',
          false,
          'utf-8',
          storage_path()
        );
        $mailIds = $remoteBox->searchMailbox('ALL');
        foreach ($mailIds as $mailId) {
          $mail = $remoteBox->getMail($mailId);
          \Log::info('received mail id :'.$mailId);
        }
        $job->release(5);
    }

    public function fire($job, $data){
        //sleep(10);
        $date = \Carbon::now();
        $text = "Completed at ".$date;
        $file = storage_path()."/myfirstqueue.txt";

        \File::put($file, $text);

        \Log::info('This is was written via the MailWorker class at '.time().'.'.json_encode($data));

        $job->delete();
    }
}