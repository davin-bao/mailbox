<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-5-18
 * Time: ÏÂÎç5:57
 */

namespace DavinBao\Mailbox;

use Illuminate\Support\SerializableClosure;

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

        $callback = MailWorker::buildQueueCallable(function($m) use ($incomingMail)
        {
            $m->from($incomingMail->fromAddress, $incomingMail->fromName);
            foreach ($incomingMail->to as $key=>$value) {
                $m = $m->to($key, $value);
            }
            foreach ($incomingMail->cc as $key=>$value) {
                $m = $m->cc($key);
            }
            foreach ($incomingMail->replyTo as $key=>$value) {
                $m = $m->replyTo($key);
            }

            $m->subject($incomingMail->subject);
            foreach($incomingMail->attachments as $attachment){
                $m->attach($attachment->filePath, array('as' => $attachment->name));
            }
            \Log::info('The mail "'.$incomingMail->subject.'" was sent.');
        });

        $data = array('incomingMail' => $incomingMail);

        \Queue::later($delay, '\DavinBao\Mailbox\MailWorker@handleQueuedMessage', compact('view', 'data', 'callback'), 'low');
        var_dump('sendMail');

//      \Mail::later($delay, $view,array('incomingMail' => $incomingMail), function($m) use ($incomingMail)
//      {
//        $m->from($incomingMail->fromAddress, $incomingMail->fromName);
//        foreach ($incomingMail->to as $key=>$value) {
//          $m = $m->to($key, $value);
//        }
//        foreach ($incomingMail->cc as $key=>$value) {
//          $m = $m->cc($key);
//        }
//        foreach ($incomingMail->replyTo as $key=>$value) {
//          $m = $m->replyTo($key);
//        }
//
//        $m->subject($incomingMail->subject);
//        foreach($incomingMail->attachments as $attachment){
//          $m->attach($attachment->filePath, array('as' => $attachment->name));
//        }
//
//
//        \Log::info('The mail "'.$incomingMail->subject.'" was sent.');
//      }, 'low');
    }

    public function handleQueuedMessage($job, $data){
        $job->delete();
        \Mail::send($data['view'], $data['data'], MailWorker::getQueuedCallable($data));
    }

    /**
     * Build the callable for a queued e-mail job.
     *
     * @param  mixed  $callback
     * @return mixed
     */
    protected static function buildQueueCallable($callback)
    {
        if ( ! $callback instanceof \Closure) return $callback;

        return serialize(new SerializableClosure($callback));
    }


    /**
     * Get the true callable for a queued e-mail message.
     *
     * @param  array  $data
     * @return mixed
     */
    protected static function getQueuedCallable(array $data)
    {
        if (str_contains($data['callback'], 'SerializableClosure'))
        {
            return with(unserialize($data['callback']))->getClosure();
        }

        return $data['callback'];
    }
}