<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-5-19
 * Time: 下午7:53
 */
namespace DavinBao\Mailbox;

class MailQueues {

    public  function receiveRecentMail($user_id){
        \Queue::push( '\DavinBao\Mailbox\MailWorker@receiveRecentMail', array('user_id', $user_id), 'low');
    }
}