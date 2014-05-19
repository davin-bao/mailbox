<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-5-19
 * Time: 下午7:40
 */

namespace DavinBao\Mailbox;

use Illuminate\Console\Command;
use SebastianBergmann\Exporter\Exception;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MailQueueCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'mailbox:work';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run mailbox works.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $app = app();
    }


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {

        $this->line('');
        $this->info( "Run work" );
        $message = "  ";

        $this->comment( $message );
        $this->line('');

        if ( $this->confirm("Proceed with run mailbox work? [Yes|no]") )
        {
            $this->line('');

            $this->info( "Running ..." );
            try{
                //Receive mails worker
                \Queue::push( '\DavinBao\Mailbox\MailWorker@receiveRecentMail', array(), 'low');



                $this->info( "Work successfully ran!" );
            }catch(\Exception $ex){
                $this->info( "Work run failed!" );
            }
            $this->line('');

        }
    }

}