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

    private $userId = 1;

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
        $this->userId = $userId;
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

    public function search($flag= self::BOX_UNSEEN, $order = self::SORT_DATE, $reverse = false){

      switch($flag){
        case self::BOX_DELETED:
          return $this->getLocaleBox()->searchDeletedbox($order, $reverse);
        case self::BOX_SENT:
          return $this->getLocaleBox()->searchSentbox($order, $reverse);
        case self::BOX_FLAGED:
          return $this->getLocaleBox()->searchFlagedbox($order, $reverse);
        case self::BOX_UNSEEN:
          return $this->getLocaleBox()->searchUnseenbox($order, $reverse);
        case self::BOX_UNSEEN:
        default:
          return $this->getLocaleBox()->searchInbox($order, $reverse);
      }
    }

    /**
     * add to receive queue
     */
    public function receive(){
        \Queue::push('\DavinBao\Mailbox\MailWorker@receiveRecentMail', array('user_id' => $this->userId), 'low');
    }

    private function saveToLocale($mailId){
        if($this->isSaved($mailId)){
            return;
        }
        $incomingMail = $this->remoteBox->getMail($mailId);
        $this->getLocaleBox()->saveMail($incomingMail);
    }

    private function isSaved($mailId){
        $mailUid = $this->remoteBox->getUid($mailId);
        if($this->getLocaleBox()->isExistInLocale($mailUid)){
            return true;
        }
        return false;
    }

    private function syncMailStatus($mailsIds){
        $mailStatus = $this->remoteBox->getMailsInfo($mailsIds);
        $this->getLocaleBox()->syncMailStatus($mailStatus);
    }

    public function getMail($id){
        return $this->getLocaleBox()->getMail($id);
    }

    public function markMailAsRead($mailId){
      $this->remoteBox->setFlag(array($mailId), '\\Seen');
      $mailUid = $this->remoteBox->getUid($mailId);
      $this->getLocaleBox()->setFlag(array($mailUid), '\\Seen');
    }

    public function markMailAsUnRead($mailId){
      $this->remoteBox->clearFlag(array($mailId), '\\Seen');
      $mailUid = $this->remoteBox->getUid($mailId);
      $this->getLocaleBox()->clearFlag(array($mailUid), '\\Seen');
    }

    public function markMailAsImportant($mailId) {
      $this->remoteBox->setFlag(array($mailId), '\\Flagged');
      $mailUid = $this->remoteBox->getUid($mailId);
      $this->getLocaleBox()->setFlag(array($mailUid), '\\Flagged');
    }

    public function markMailAsNoImportant($mailId) {
      $this->remoteBox->clearFlag(array($mailId), '\\Flagged');
      $mailUid = $this->remoteBox->getUid($mailId);
      $this->getLocaleBox()->clearFlag(array($mailUid), '\\Flagged');
    }

    public function markMailDeleted($mailId){
      $mailUid = $this->remoteBox->getUid($mailId);
      $this->getLocaleBox()->setFlag(array($mailUid), '\\Deleted');
    }

    public function markMailUnDeleted($mailId){
      $mailUid = $this->remoteBox->getUid($mailId);
      $this->getLocaleBox()->clearFlag(array($mailUid), '\\Deleted');
    }

    public function send(IncomingMail $incomingMail, $isSaveSent=true, $delay = 0){
      if($isSaveSent){
        $this->getLocaleBox()->saveMail($incomingMail);
        $this->getLocaleBox()->setFlag(array($incomingMail->uid), '\\Sent');
      }


        $view = $this->_app['config']->get('mailbox::emails_default_form');
      MailWorker::sendMail($incomingMail, $view, $delay);
    }
}