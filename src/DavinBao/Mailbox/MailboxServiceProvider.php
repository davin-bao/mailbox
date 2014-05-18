<?php namespace DavinBao\Mailbox;

use Illuminate\Support\ServiceProvider;

class MailboxServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('davin-bao/mailbox');
    }
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//
        $this->registerMailbox();
	}


    /**
     * Register the application bindings.
     *
     * @return void
     */
    private function registerMailbox()
    {
        $this->app->bind('mailbox', function($app)
        {
            return new Mailbox($app);
        });
    }
	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
