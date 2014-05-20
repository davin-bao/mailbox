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
        $entity->mail_id = $incomingMail->id;
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
          $address->address = $key;
          $address->name = $value;
          $entity->ToAddresses()->save($address);
        }

        foreach($incomingMail->cc as $key=>$value){
          $address = new Address();
          $address->address = $key;
          $address->name = $value;
          $entity->CcAddresses()->save($address);
        }

        foreach($incomingMail->replyTo as $key=>$value){
          $address = new Address();
          $address->address = $key;
          $address->name = $value;
          $entity->ReplyAddresses()->save($address);
        }

        return $entity->id;
    }

    public function getMail($id) {
        return Entity::find($id);
    }

    public function syncMailStatus($mailsStatus){
      foreach ($mailsStatus as $mailStatus) {
        $mail = Entity::where('uid','=', $mailStatus->uid);
        if(!$mail){
          continue;
        }
        $mail->seen = $mailsStatus->seen;
        $mail->flagged = $mailsStatus->flagged;
        $mail->save();
      }
    }

  public function searchDeletedbox($orders, $reverse = false){
    return $this->account->entities()->where('deleted', '=', true)->andWhere('sent', '=', false)->orderBy($orders, $reverse?"DESC":"ASC")->get();
  }

  public function searchSentbox($orders, $reverse = false){
    return $this->account->entities()->where('send', '=', true)->orderBy($orders, $reverse?"DESC":"ASC")->get();
  }

  public function searchFlagedbox($orders, $reverse = false){
    return $this->account->entities()->where('flaged', '=', true)->andWhere('sent', '=', false)->orderBy($orders, $reverse?"DESC":"ASC")->get();
  }

  public function searchUnseenbox($orders, $reverse = false){
    return $this->account->entities()->where('seen', '=', false)->andWhere('sent', '=', false)->orderBy($orders, $reverse?"DESC":"ASC")->get();
  }

  public function searchInbox($orders, $reverse = false){
    return $this->account->entities()->where('sent', '=', false)->orderBy($orders, $reverse?"DESC":"ASC")->get();
  }

  public function setFlag(array $mailUids, $flag) {
    $this->setMailFlag($mailUids, $flag, true);
  }

  public function clearFlag(array $mailUids, $flag) {
    $this->setMailFlag($mailUids, $flag, false);
  }

  private function setMailFlag($mailUids, $flag, $value = false){
    foreach($mailUids as $mailUid){
      $mailEntity = Entity::where('uid', '=', $mailUid)->first();
      if($mailEntity){
        switch($flag){
          case "\\Seen":
            $mailEntity->seen = $value;
            break;
          case "\\Sent":
            $mailEntity->sent = $value;
            break;
          case "\\Flagged":
            $mailEntity->flagged = $value;
            break;
          case "\\Deleted":
            $mailEntity->deleted = $value;
            break;
        }
        $mailEntity->save();
      }
    }
  }

}