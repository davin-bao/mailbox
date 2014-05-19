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
        'Account'    => array(self::BELONGS_TO, 'Account'),
        'Attachments'    => array(self::HAS_MANY, 'Attachment'),
        'ToAddresses' => array(self::BELONGS_TO_MANY, 'Address', 'to_addresses', 'mail_entity_id', 'mail_address_id'),
        'CcAddresses' => array(self::BELONGS_TO_MANY, 'Address', 'cc_addresses', 'mail_entity_id', 'mail_address_id'),
        'ReplyAddresses' => array(self::BELONGS_TO_MANY, 'Address', 'reply_addresses', 'mail_entity_id', 'mail_address_id')
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