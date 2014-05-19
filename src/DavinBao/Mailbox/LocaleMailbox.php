<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-5-18
 * Time: PM 12:20
 */

namespace DavinBao\Mailbox;

class LocaleMailbox
{
    private $account = false;

    public function __construct() {
    }

    public function getAccount($userId){
        if(!$this->account){
            $this->account = Account::where('user_id', '=', $userId)->first();
        }
        return $this->account;
    }
    /**
     * Register
     * @param array $accountData {$user_id, $host_name, $host_port, $host_protocol, $email, $password}
     */
    public function register(array $accountData){
        $this->account = Account::firstOrCreate($accountData);
        if($this->account->save()){
            return $this->account;
        }else{
            return $this->account->errors()->all();
        }
    }

    public function getUpdatedAt($format='d M Y'){
        return $this->account ? $this->account->updated_at->format($format) : date($format);
    }

    public function isExistInLocale($mailUid){
        if(Entity::where('uid', '=', $mailUid)->count()>0){
            return true;
        }
        return false;
    }

    public function saveMail($incomingMail){
        $entity = new Entity();
        $entity->date = $incomingMail->date;
        $entity->subject = $incomingMail->subject;
        $entity->from_name = $incomingMail->fromName;
        $entity->from_address = $incomingMail->fromAddress;
        $entity->text_plain = $incomingMail->fromAddress;
        $entity->text_html = $incomingMail->textHtml;
        $entity->uid = $incomingMail->uid;
        $this->account->Entities()->save($entity);
        foreach($incomingMail->attachments as $attach){
            $attachment = new Attachment();
            $attachment->name = $attach->name;
            $attachment->file_path = $attach->filePath;
            $attachment->ext_name = $attach->extName;
            $entity->Attachments()->save($attachment);
        }
        foreach($incomingMail->to as $key=>$value){
            $address = new Address();
            
        }
    }

    public function getInbox($orders, $reverse = false){
        return $this->account->entities()->where('sent', '=', false)->orderBy($orders, $reverse?"DESC":"ASC")->get();
    }
}