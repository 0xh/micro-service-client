<?php

/**
 * @author simon <crcms@crcms.cn>
 * @datetime 2018/07/02 19:15
 * @link http://crcms.cn/
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Foundation\MicroService\Client;

use CrCms\Foundation\MicroService\Client\Contracts\Selector;
use CrCms\Foundation\MicroService\Client\Contracts\ServiceContract;
use CrCms\Foundation\MicroService\Client\Drivers\Restful;
use CrCms\Foundation\MicroService\Client\Selectors\RandSelector;
use CrCms\Foundation\MicroService\Client\ServiceDiscover;
use CrCms\Foundation\MicroService\Server\Commands\ServiceRegisterCommand;
use CrCms\Foundation\MicroService\Client\Contracts\ServiceDiscoverContract;
use CrCms\Foundation\MicroService\Client\ServiceFactory;
use Illuminate\Support\ServiceProvider;

/**
 * Class MicroServiceProvider
 * @package CrCms\Foundation\Rpc
 */
class MicroServiceClientProvider extends ServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = false;

    /**
     * @var string
     */
    protected $namespaceName = 'micro-service-client';

    /**
     * @var string
     */
    protected $packagePath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;

    /**
     * @return void
     */
    public function boot()
    {
        //move config path
        $this->publishes([
            $this->packagePath . 'config' => config_path(),
        ]);
    }

    /**
     * @return void
     */
    public function register(): void
    {
        //merge config
        $configFile = $this->packagePath . "config/config.php";
        if (file_exists($configFile)) $this->mergeConfigFrom($configFile, $this->namespaceName);

        $this->registerAlias();
        $this->registerServices();
        $this->registerCommands();
    }

    /**
     * @return void
     */
    protected function registerServices(): void
    {
        $this->app->singleton(ServiceFactory::class, function ($app) {
            return new ServiceFactory($app);
        });

        $this->app->singleton('micro-service-client.discovery.selector', function () {
            return new RandSelector;
        });

        $this->app->singleton('micro-service-client.discovery', function ($app) {
            return new ServiceDiscover($app, $app->make('micro-service-client.discovery.selector'), $app->make('client.manager'));
        });
    }

    /**
     * @return void
     */
    protected function registerCommands(): void
    {
        $this->commands(ServiceRegisterCommand::class);
    }

    /**
     * @return void
     */
    protected function registerAlias(): void
    {
        $this->app->alias('micro-service-client.discovery', ServiceDiscoverContract::class);
        $this->app->alias('micro-service-client.discovery.selector', Selector::class);
    }

    /**
     * @return array
     */
    public function provides(): array
    {
        return [
            ServiceDiscoverContract::class,
            Selector::class,
            ServiceRegisterCommand::class,
        ];
    }
}