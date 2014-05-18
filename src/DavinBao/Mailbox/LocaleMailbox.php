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
        if($this->account){
            return true;
        }else{
            return $this->account->errors()->all();
        }
    }

    public function getUpdatedAt($format='d M Y'){
        return $this->account ? $this->account->updated_at->format($format) : date($format);
    }

    public function isExistInLocale($mailUid){

    }

    public function getInbox($orders, $reverse = false){
        return $this->account->entities()->where('sent', '=', false)->orderBy($orders, $reverse?"DESC":"ASC")->get();
    }
}