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
            //get recent receive mail date
            $updated_at = $localeBox->getUpdatedAt();
            $mailIds = $remoteBox->searchMailbox('SINCE "'.$updated_at.'"');

            foreach($mailIds as $mailId){
                $mailUid = $remoteBox->getUid($mailId);
                if($localeBox->isExistInLocale($mailUid)){
                    return;
                }
                $incomingMail = $remoteBox->getMail($mailId);
                $localeBox->saveMail($incomingMail);
            }
            $mailStatus = $remoteBox->getMailsInfo($mailIds);
            $localeBox->syncMailStatus($mailStatus);

            //get message count and unseen count , update receive mail date
            $boxStatus = $remoteBox->statusMailbox();
            $localeBox->syncMailBoxStatus($boxStatus);
        }
        \Log::info('Recent mails received.');
    }

    public static function sendMail($incomingMail, $view, $delay){
      \Mail::later($delay, $view,array('incomingMail' => $incomingMail), function($m) use ($incomingMail)
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

        \Log::info('The mail "'.$incomingMail->subject.'" was sent.');
      }, 'low');
    }
}