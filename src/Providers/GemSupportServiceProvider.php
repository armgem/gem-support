<?php

namespace GemSupport\Providers;

use GemSupport\CoreGemServiceProvider;
use Illuminate\Contracts\Container\BindingResolutionException;
use GemSupport\GemDB;

class GemSupportServiceProvider extends CoreGemServiceProvider
{
    /**
     * @throws BindingResolutionException
     */
    public function boot()
    {
        $this->mergeAndPublishPackageConfig();
    }

    /**
     *
     */
    public function register()
    {
        $this->registerSingletons([
            'gem-db' => GemDB::class,
        ]);
    }
}
