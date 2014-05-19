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

class Account extends Ardent
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'mail_accounts';

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
        'host_name' => 'required',
        'host_port' => 'required|numeric',
        'host_protocol' => 'min:4',
        'email' => 'required|email|unique:mail_accounts',
        'password' => 'required|min:2',
    );



    public static $relationsData = array(
        'Entities'    => array(self::HAS_MANY, 'Entity')
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