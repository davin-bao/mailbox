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

        $time = $this->account->freshTimestamp()->subDays(15);
        $this->account->setUpdatedAt($time);
        $this->account->timestamps = false;
        $this->account->forceSave();
        //$this->account->validationErrors = new \MessageBag();

        return $this->account;
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
        $entity->text_plain = $incomingMail->textPlain;
        $entity->text_html = $incomingMail->textHtml;
        $entity->uid = $incomingMail->uid;
        $this->account->entities()->save($entity);
        foreach($incomingMail->getAttachments() as $attach){
            $attachment = new Attachment();
            $attachment->name = $attach->name;
            $attachment->file_path = $attach->filePath;
            $attachment->ext_name = $attach->extName;
            $entity->attachments()->save($attachment);
        }
        foreach($incomingMail->to as $key=>$value){
          $address = Address::getNewAddress($key, $value);
          $entity->toAddresses()->save($address);
        }

        foreach($incomingMail->cc as $key=>$value){
            $address = Address::getNewAddress($key, $value);
          $entity->ccAddresses()->save($address);
        }

        foreach($incomingMail->replyTo as $key=>$value){
            $address = Address::getNewAddress($key, $value);
          $entity->replyAddresses()->save($address);
        }
        return $entity->id;
    }

    public function getMail($id) {
        return Entity::find($id);
    }

    public function syncMailBoxStatus($boxStatus){
        if($this->account){
            $this->account->message_count = $boxStatus->messages;
            $this->account->unseen_count = $boxStatus->unseen;
            $this->account->forceSave();
        }
    }

    public function syncMailStatus($mailsStatus){
      foreach ($mailsStatus as $mailStatus) {
        $mail = Entity::where('uid','=', $mailStatus->uid)->first();
        if(!$mail){
          continue;
        }
        $mail->seen = $mailStatus->seen;
        $mail->flagged = $mailStatus->flagged;
        $mail->save();
      }
    }

  public function searchDeletedbox($orders, $reverse = false){
    return $this->account->entities()->where('deleted', '=', true)->where('sent', '=', false)->orderBy($orders, $reverse?"DESC":"ASC")->get();
  }

  public function searchSentbox($orders, $reverse = false){
    return $this->account->entities()->where('sent', '=', true)->orderBy($orders, $reverse?"DESC":"ASC")->get();
  }

  public function searchFlaggedbox($orders, $reverse = false){
    return $this->account->entities()->where('flagged', '=', true)->where('sent', '=', false)->orderBy($orders, $reverse?"DESC":"ASC")->get();
  }

  public function searchUnseenbox($orders, $reverse = false){
    return $this->account->entities()->where('seen', '=', false)->where('sent', '=', false)->orderBy($orders, $reverse?"DESC":"ASC")->get();
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