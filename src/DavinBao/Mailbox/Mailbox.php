<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-5-18
 * Time: 下午12:07
 */

namespace DavinBao\Mailbox;

class Mailbox {

    private $isRegister = false;
    private $remoteBox = false;
    private $localeBox = false;

    const SORT_DATE = 'date';

    const SORT_FROM = 'from_name';

    const SORT_SUBJECT = 'subject';

    const BOX_UNSEEN = 'UNSEEN';
    const BOX_IN = 'ALL';
    const BOX_FLAGED = 'FLAGED';
    const BOX_SENT = 'SENT';
    const BOX_DELETED = 'DELETED';


    /**
     * Laravel application
     *
     * @var Illuminate\Foundation\Application
     */
    public $_app;

    public function __construct($app)
    {
        set_time_limit(0);
        $this->_app = $app;
    }

    public function getLocaleBox(){
        if(!$this->localeBox){
            $this->localeBox = new LocaleMailbox();
        }
        return $this->localeBox;
    }

    public function signIn($userId){
        $account = $this->getLocaleBox()->getAccount($userId);
        if($account){
            $this->remoteBox = new RemoteMailbox(
                $account->host_name,
                $account->email,
                $account->password,
                $account->host_protocol,
                $account->host_port,
                false,
                'utf-8',
                storage_path()."\\attachments"
            );
            $this->isRegister = true;
        }
    }

    public function isRegister(){
        return $this->isRegister;
    }

    /**
     * Register
     * @param array $accountData {$user_id, $host_name, $host_port, $host_protocol, $email, $password}
     */
    public function register(array $accountData){
        return $this->getLocaleBox()->register($accountData);
    }

    public function isSaved($mailId){
        $mailUid = $this->remoteBox->getUid($mailId);
        if($this->localeBox->isExistInLocale($mailUid)){
            return true;
        }
        return false;
    }

    public function receive(){
        $updated_at = $this->getLocaleBox()->getUpdatedAt();
        $mailIds = $this->remoteBox->searchMailbox('SINCE "'.$updated_at.'"');
        foreach($mailIds as $mailId){
            $this->saveToLocale($mailId);
        }
    }

    public function search($flag= self::BOX_UNSEEN, $since=null, $order = self::SORT_DATE, $reverse = false){
        $mails = array();
        switch($flag){
            case 'SENT':
                break;
            case 'DELETED':
                break;
            case self::BOX_IN:
                return $this->getLocaleBox()->searchInbox($order, $reverse);
        }
    }

    public function saveToLocale($mailId){
        if($this->isSaved($mailId)){
            return;
        }
        $incomingMail = $this->remoteBox->getMail($mailId);
        var_dump($incomingMail);
       //$this->localeBox->saveMail($incomingMail);
    }

    public function refreshLocaleStatus($mailsIds){
        $mailStatus = $this->remoteBox->getMailsInfo($mailsIds);
    }

    public function getMail($mailId){}

    public function markMailAsRead($mailId){}

    public function markMailAsUnRead($mailId){}

    public function markMailAsImportant($mailId) {}

    public function markMailAsNoImportant($mailId) {}

    public function markMailDeleted($mailId){}

    public function markMailUnDeleted($mailId){}

    public function send(IncomingMail $mail, $isSaveSent=true, $delay = 0){}
}