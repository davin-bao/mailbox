<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-5-18
 * Time: PM 12:36
 */


namespace DavinBao\Mailbox;

use LaravelBook\Ardent\Ardent;
use Config;

class Entity extends Ardent
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'mail_entities';

    protected $results = array();

    protected $guarded = array();

    /**
     * Laravel application
     *
     * @var Illuminate\Foundation\Application
     */
    public static $app;

    /**
     * Ardent validation rules
     *
     * @var array
     */
    public static $rules = array(
    );

    public static $relationsData = array(
        'account'    => array(self::BELONGS_TO, '\DavinBao\Mailbox\Account'),
        'attachments'    => array(self::HAS_MANY, '\DavinBao\Mailbox\Attachment'),
        'toAddresses' => array(self::BELONGS_TO_MANY,'\DavinBao\Mailbox\Address',  'table'=>'to_addresses', 'foreignKey'=>'mail_entity_id','otherKey'=>'mail_address_id'),
        'ccAddresses' => array(self::BELONGS_TO_MANY, '\DavinBao\Mailbox\Address', 'table'=> 'cc_addresses','foreignKey'=> 'mail_entity_id', 'otherKey'=>'mail_address_id'),
        'replyAddresses' => array(self::BELONGS_TO_MANY, '\DavinBao\Mailbox\Address',  'table'=>'reply_addresses', 'foreignKey'=>'mail_entity_id','otherKey'=>'mail_address_id')
    );
    /**
     * Creates a new instance of the model
     */
    public function __construct(array $attributes = array())
    {
        parent::__construct($attributes);

        if ( ! static::$app )
            static::$app = app();
    }

}