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
        $this->_app = $app;
    }

    public function getLocaleBox(){
        if(!$this->localeBox){
            $this->localeBox = new LocaleMailbox();
        }
        return $this->localeBox;
    }

    public function signIn($userId){


      \Queue::push(function($job)
      {
        $nowUtc = new \DateTime( 'now',  new \DateTimeZone( 'UTC' ) );

        \Log::info('10This is was written via the MailWorker class at '.$nowUtc->format('Y-m-d h:i:s').' id is '.$job->getJobId());
        $job->release(10);
      },array('message' => 'aa'), 'high');

        //$date = \Carbon::now()->addMinutes(1);
        \Queue::push( '\DavinBao\Mailbox\MailWorker@receiveMail', array('message' => 'aa'), 'low');

        var_dump(1);
        exit;
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
                $this->_app['config']->get('mailbox::attachments_dir')
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

    public function receive(){
        $updated_at = $this->getLocaleBox()->getUpdatedAt();
        return $this->remoteBox->searchMailbox('SINCE "'.$updated_at.'"');
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


    public function getMail($mailId){}

    public function markMailAsRead($mailId){}

    public function markMailAsUnRead($mailId){}

    public function markMailAsImportant($mailId) {}

    public function markMailAsNoImportant($mailId) {}

    public function markMailDeleted($mailId){}

    public function markMailUnDeleted($mailId){}

    public function send(IncomingMail $mail, $isSaveSent=true, $delay = 0){}
}