<?php

namespace OlesKashchenko\LarOAuth;

use Illuminate\Support\ServiceProvider;

class LarOAuthServiceProvider extends ServiceProvider {

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
		$this->package('oles-kashchenko/lar-oauth');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['lar_oauth'] = $this->app->share(function($app) {
			return new LarOAuth();
		});
	} // end register

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
