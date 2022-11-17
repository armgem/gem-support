@TODO more proper type

About CoreGemServiceProvider

We are offering 3 ways for easy make our configs and publish
1) mergeAndPublishConfig
2) mergeAndPublishPackageConfig
3) mergeAndPublishPackageConfigs

**mergeAndPublishConfig** method need pass __DIR__, and it will be automatically register you config path like project_name with snake_case
and will publish it, also will provide tag like project-name-config.
This method benefits is by default using **array_replace_recursive** method against laravel **array_merge**, and you will be sure all sub nested array data will be keep,
but you can pass in 4-th parameter as **single_level** for merge config using **array_merge**

Let say your package name is **gem-support**, then it will register **gem_support** config and also will publish your config with tag **gem-support-config**
```
class GemSupportServiceProvider extends CoreGemServiceProvider
{
    /**
     *
     */
    public function boot()
    {
        $this->mergeConfig(__DIR__);
    }
}
```
