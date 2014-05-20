<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-5-18
 * Time: 下午5:57
 */

namespace DavinBao\Mailbox;

class MailWorker {

    public function receiveRecentMail($job, $data){
        $job->delete();
        \Log::info('receiving...');
        \Log::info(json_encode($data));
        $userId = $data["user_id"];

        $localeBox =  new LocaleMailbox();
        $account = $localeBox->getAccount($userId);

        if($account){
            $remoteBox = new RemoteMailbox(
                $account->host_name,
                $account->email,
                $account->password,
                $account->host_protocol,
                $account->host_port,
                false,
                'utf-8',
                storage_path()."\\attachments"
            );

            $updated_at = $localeBox->getUpdatedAt();
            $mailIds = $remoteBox->searchMailbox('SINCE "'.$updated_at.'"');


            \Log::info(json_encode($mailIds));
        }

//        $remoteBox = new RemoteMailbox(
//          'imap.exmail.qq.com',
//          'bwj@zhiyee.com',
//          'A!b2c3d4',
//          'imap',
//          '143',
//          false,
//          'utf-8',
//          storage_path()
//        );
//        $mailIds = $remoteBox->searchMailbox('ALL');
//        foreach ($mailIds as $mailId) {
//          $mail = $remoteBox->getMail($mailId);
//          \Log::info('received mail id :'.$mailId);
//        }
//        $job->release(5);
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

    public static function sendMail($incomingMail, $view, $delay){
      Mail::later($delay, $view,array('incomingMail' => $incomingMail), function($m) use ($incomingMail)
      {
        $m->from($incomingMail->fromAddress, $incomingMail->fromName);
        foreach ($incomingMail->to as $key=>$value) {
          $m = $m->to($key, $value);
        }
        foreach ($incomingMail->cc as $key=>$value) {
          $m = $m->cc($key, $value);
        }
        foreach ($incomingMail->replyTo as $key=>$value) {
          $m = $m->replyTo($key, $value);
        }

        $m->subject($incomingMail->subject);
        foreach($incomingMail->attachments as $attachment){
          $m->attach($attachment->filePath, array('as' => $attachment->name));
        }
      }, 'low');
    }
}