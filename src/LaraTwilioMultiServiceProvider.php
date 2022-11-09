<?php

namespace Armenium\LaraTwilioMulti;

use App\Models\Recipient;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Route;

class LaraTwilioMultiServiceProvider extends ServiceProvider{

	private $configs;

	public function register(){
		$this->mergeConfigFrom(__DIR__.'/../config/laratwiliomulti.php', 'laratwiliomulti');

		$this->app->bind('laratwiliomulti', function(){
			$this->ensureConfigValuesAreSet();

			$configs = config('laratwiliomulti');
			if(is_null($use_account)){
				$use_account = $config['active_account'];
			}

			$client = new Client(config('laratwiliomulti.account_sid'), config('laratwiliomulti.auth_token'));

			return new LaraTwilioMulti($client);
		});
	}

	public function boot(){
		if($this->app->runningInConsole()){
			$this->publishConfig();
			$this->registerMigrations();
			#$this->registerRoutes();
		}
		$this->registerViews();
		$this->installRoutes();
	}

	protected function ensureConfigValuesAreSet(){
		$configs = config('laratwiliomulti');
		#$r = Recipient::find(288);
		#dd($r);
		foreach($configs as $k => $elements){
			foreach($elements['accounts'] as $account_key => $mandatoryAttributes){
				foreach($mandatoryAttributes as $key => $value){
					if(empty($value)){
						throw new Exception("Please provide a value for ${key}");
					}
				}
			}
		}
	}

	protected function publishConfig(){
		$this->publishes([__DIR__.'/../config/laratwiliomulti.php' => config_path('laratwiliomulti.php')], 'laratwiliomulti-config');
	}

	public function registerViews(){
			$this->loadViewsFrom(__DIR__.'/views', 'LaraTwilioMultiViews');
			$this->publishes([__DIR__.'/views' => resource_path('views/vendor/LaraTwilioMultiViews')]);
	}

	public function registerMigrations(){
		$this->loadMigrationsFrom(realpath(__DIR__.'/../database/migrations'));
		Artisan::call('migrate');
	}

	public function registerRoutes(){
		$this->loadRoutesFrom(realpath(__DIR__.'/../routes/web.php'));
	}

	protected function installRoutes(){
		$config = [
			'prefix' => '',
			'middleware' => ['auth'],
			'namespace' => 'Armenium\LaraTwilioMulti',
		];

		#Route::resource('laratwiliomultisettings', 'Armenium\LaraTwilioMulti\Controllers\LaraTwilioMultiSettingsController');
		Route::group($config, function(){
			Route::get('laratwiliomultisettings', 'Controllers\LaraTwilioMultiSettingsController@index')->name('laratwiliomultisettings.index');
		});
	}

}
