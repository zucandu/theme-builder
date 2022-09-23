<?php

namespace App\Modules\Deepplusplus\Plugin;

class Plugin {

    public static $plugin;

    /**
     * Store a newly created resource in storage.
     *
     * @param  plugin name  $plugin
     * @return \Illuminate\Http\Response
     */
    public static function init($plugin)
    {
        $plugin = ucfirst($plugin);
        return self::$plugin = new $plugin;
    }

    /**
     * Check plugin if support hook event
     * @param String $listener where the hook code actions
     * @param Object $data data which send to hook to process
     * @return Array $processedData
     */
    public static function availableHook($data, $listener)
    {
        $processedData = [];
        $path = app_path() . '/Modules/Deepplusplus/Plugin/*.php';
        foreach (glob($path) as $module) {
            $className = basename($module, '.php');
            if($className !== 'Plugin') {
                $initPlugin = self::init($className);
                if(method_exists($initPlugin, 'hook') === true && (isset($initPlugin->availableHooks) && in_array($listener, $initPlugin->availableHooks) === true)) {
                    $processedData[$listener][strtolower($className)] = $initPlugin->hook($data, $listener);
                }
            }
        }
        return $processedData;
    }

    /**
     * Handle the response data from external websites
     * @param String $listener where the hook code actions
     * @param Object $data data which send to hook to process
     * @return Any
     */
    public static function responseHandler($data, $listener)
    {
        $processedData = [];
        $path = app_path() . '/Modules/Deepplusplus/Plugin/*.php';
        foreach (glob($path) as $module) {
            $className = basename($module, '.php');
            if($className !== 'Plugin') {
                $initPlugin = self::init($className);
                if(method_exists($initPlugin, 'responsehandler') === true && (isset($initPlugin->availableHooks) && in_array($listener, $initPlugin->availableHooks) === true)) {

                    /**
                     * In children class (plugin class), never use exit or die to debug code
                     * because it will shows http blank page
                     */
                    $processedData[$listener][strtolower($className)] = $initPlugin->responsehandler($data, $listener);
                }
            }
        }
        return $processedData;
    }

}