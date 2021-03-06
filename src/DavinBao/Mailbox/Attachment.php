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

class Attachment extends Ardent
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'mail_attachments';

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
        'entity'    => array(self::BELONGS_TO, '\DavinBao\Mailbox\Entity')
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

    
    public function getDownloadPath(){
      return \URL::to(str_replace('index.php','',\URL::to('/')).'/'.str_replace(public_path(), '', $this->file_path));
    }

}