<?php

namespace GemSupport;

use Illuminate\Contracts\Foundation\CachesConfiguration;
use Illuminate\Contracts\Foundation\CachesRoutes;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Contracts\Container\BindingResolutionException;

class CoreGemServiceProvider extends ServiceProvider
{
    /**
     * Available config merge options
     */
    const CONFIG_MERGE = [
        'SINGLE_LEVEL' => 'single_level',
        'MULTI_LEVEL' => 'multi_level',
    ];

    /**
     * Merge config files under config path and publish it with tag [config-name]-config
     *
     * @param string $callPath
     * @throws BindingResolutionException
     */
    protected function mergeAndPublishPackageConfigs(string $callPath = '')
    {
        if ($this->isConfigurationCached()) {
            return;
        }

        $packagePath = $this->detectPackageRootPath($callPath);
        $configPath = $this->getPackageConfigPath($packagePath);
        $configPaths = glob($configPath . '*.php');

        foreach ($configPaths as $configPath) {
            $configName = Str::afterLast($configPath, DIRECTORY_SEPARATOR);
            $configName = Str::replaceLast('.php', '', $configName);
            $this->mergeAndPublishConfig($configPath, $configName);
        }
    }

    /**
     * Merge config file by default with snake_case of package name like [package_name] and publish it with tag [project-name]-config
     *
     * @param string $callPath
     * @param string $configName
     * @param bool $isPublish
     * @param string $mergeOption
     * @throws BindingResolutionException
     */
    protected function mergeAndPublishPackageConfig(string $callPath = '', string $configName = '', bool $isPublish = true, string $mergeOption = self::CONFIG_MERGE['MULTI_LEVEL'])
    {
        if ($this->isConfigurationCached()) {
            return;
        }

        $packagePath = $this->detectPackageRootPath($callPath);
        $configName = $configName ?: $this->detectPackageName($packagePath);
        $configPath = $this->getPackageConfigPath($packagePath) . $configName . '.php';
        $this->mergeAndPublishConfig($configPath, $configName, $isPublish, $mergeOption);
    }

    /**
     * Merge config file by given name and publish it with tag [name]-config
     *
     * @param string $configPath
     * @param string $configName
     * @param bool $isPublish
     * @param string $mergeOption
     * @throws BindingResolutionException
     */
    protected function mergeAndPublishConfig(string $configPath, string $configName, bool $isPublish = true, string $mergeOption = self::CONFIG_MERGE['MULTI_LEVEL'])
    {
        if ($this->isConfigurationCached()) {
            return;
        }

        if ($isPublish) {
            $tagName = $this->getTagName($configName, 'config');
            $this->publishes([$configPath => config_path($configName . '.php')], $tagName);
        }

        $config = $this->app->make('config');
        if (!in_array($mergeOption, self::CONFIG_MERGE)) {
            throw new \Exception(sprintf('Invalid mergeOption provided, you can use one of this "%s". Check CoreGemServiceProvider::CONFIG_MERGE const values', implode('", "', self::CONFIG_MERGE)));
        }

        $configValues = self::CONFIG_MERGE['MULTI_LEVEL'] === $mergeOption
            ? array_replace_recursive(require $configPath, $config->get($configName, []))
            : array_merge(require $configPath, $config->get($configName, []));

        $config->set($configName, $configValues);
    }

    /**
     * To register package views under resources/views folder with tag [package-name]-views
     *
     * @param string $callPath
     * @param string $namespace
     * @param bool $isPublish
     */
    protected function loadPackageViews(string $callPath = '', string $namespace = '', bool $isPublish = true)
    {
        $packagePath = $this->detectPackageRootPath($callPath);
        $viewPath = $this->getPackageViewPath($packagePath);
        $namespace = $namespace ?: $this->detectPackageName($packagePath);
        $this->loadViewsFrom($viewPath, $namespace);

        if ($isPublish) {
            $viewVendorPath = $this->getViewVendorPath($namespace);
            $tagName = $this->getTagName($namespace, 'views');
            $this->publishes([$viewPath => $viewVendorPath], $tagName);
        }
    }

    /**
     * To register package routes under routes folder
     *
     * @param string $callPath
     */
    protected function loadPackageRoutes(string $callPath = '')
    {
        if ($this->app instanceof CachesRoutes && $this->app->routesAreCached()) {
            return;
        }

        $packagePath = $this->detectPackageRootPath($callPath);
        $routePath = $this->getPackageRoutePath($packagePath);
        $routePaths = glob($routePath . '*.php');

        foreach ($routePaths as $routePath) {
            $this->loadRoutesFrom($routePath);
        }
    }

    /**
     * To register package commands under console/commands folder
     *
     * @param array|string $commands
     */
    protected function packageCommands(array | string $commands = [])
    {
        if (!$this->app->runningInConsole()) {
            return;
        }
        if (empty($commands)) {
            // TODO detect commands
            $commands = [];
        } elseif (is_string($commands)) {
            $commands = [$commands];
        }

        $this->commands($commands);
    }

    /**
     * Register multiple singletons
     *
     * @param array $singletons
     */
    protected function registerSingletons(array $singletons)
    {
        foreach ($singletons as $singleton => $class) {
            $this->registerSingleton($singleton, $class);
        }
    }

    /**
     * Register single singletons
     *
     * @param string $singleton
     * @param string $class
     */
    protected function registerSingleton(string $singleton, string $class)
    {
        $this->app->singleton($singleton, function ($app) use ($class) {
            return App::make($class);
        });
    }

    /**
     * @param string $callPath
     * @return string
     */
    private function detectPackageRootPath(string $callPath = ''): string
    {
        if (empty($callPath)) {
            $callPath = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'];
        }

        return Str::before($callPath, 'src');
    }

    /**
     * @param string $packagePath
     * @return string
     */
    private function detectPackageName(string $packagePath): string
    {
        $packagePath = trim($packagePath, DIRECTORY_SEPARATOR);
        $packageName = Str::afterLast($packagePath, DIRECTORY_SEPARATOR);
        return str_replace('-', '_', $packageName);
    }

    /**
     * @param string $packagePath
     * @return string
     */
    private function getPackageConfigPath(string $packagePath): string
    {
        return $this->getPackageSubPath($packagePath, 'config');
    }

    /**
     * @param string $packagePath
     * @return string
     */
    private function getPackageViewPath(string $packagePath): string
    {
        return $this->getPackageSubPath($packagePath, 'resources' . DIRECTORY_SEPARATOR . 'views');
    }

    /**
     * @param string $packagePath
     * @return string
     */
    private function getPackageRoutePath(string $packagePath): string
    {
        return $this->getPackageSubPath($packagePath,  'routes');
    }

    /**
     * @param string $packagePath
     * @param string $subPath
     * @return string
     */
    private function getPackageSubPath(string $packagePath, string $subPath): string
    {
        return $packagePath . $subPath . DIRECTORY_SEPARATOR;
    }

    /**
     * @param string $resource
     * @param string $tagSuffix
     * @return string
     */
    private function getTagName(string $resource, string $tagSuffix): string
    {
        return Str::slug($resource) . '-' . $tagSuffix;
    }

    /**
     * @return bool
     */
    private function isConfigurationCached(): bool
    {
        return $this->app instanceof CachesConfiguration && $this->app->configurationIsCached();
    }

    /**
     * @param string $namespace
     * @return string
     */
    private function getViewVendorPath(string $namespace): string
    {
        return resource_path('views' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . $namespace);
    }
}
