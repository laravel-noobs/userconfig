<?php namespace KouTsuneka\UserConfig;

use Illuminate\Support\ServiceProvider;

class UserConfigServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerFlashBuilder();

        $this->app->alias('flash', 'App\Providers\UserConfigRepository');
    }

    /**
     * Register the UserConfig builder instance.
     *
     * @return void
     */
    protected function registerFlashBuilder()
    {
        $this->app->singleton('uconfig', function($app)
        {
            $user_config = new UserConfigRepository();
            return $user_config->set_session_store($app['session.store']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['uconfig', 'App\Providers\UserConfigRepository'];
    }
}
