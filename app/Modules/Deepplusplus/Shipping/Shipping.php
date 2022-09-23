<?php

namespace App\Modules\Deepplusplus\Shipping;

//use App\Helpers\Deepplusplus\Setting\Helpers;

/**
 * Class Shipping.
 *
 */
class Shipping {

    public static $courier;

    /**
     * Store a newly created resource in storage.
     *
     * @param  courier name  $courier
     * @return \Illuminate\Http\Response
     */
    public static function init($courier)
    {
        $courier = ucfirst($courier);
        return self::$courier = new $courier;
    }

    /**
     * Load all of shipping modules in admin
     */
    public static function showModules()
    {
        $shippingModules = [];

        $path = app_path() . '/Modules/Deepplusplus/Shipping/*.php';
        foreach (glob($path) as $module) {
            $className = basename($module, '.php');
            if($className !== 'Shipping') {
                $courier = new $className;
                $shippingModules[] = [
                    'code' => $courier->code,
                    'name' => $courier->name,
                    'status' => $courier->status(true),
                    'description' => $courier->description,
                    'fields' => $courier->fields()
                ];
            }
        }
        return $shippingModules;
    }

    /**
     * Show enabled shipping modules on the checkout
     */
    public static function enabledModules() {
        
        $enabledModules = [];

        $path = app_path() . '/Modules/Deepplusplus/Shipping/*.php';
        foreach (glob($path) as $module) {
            $className = basename($module, '.php');
            if($className !== 'Shipping') {
                $courier = new $className;
                if($courier->status() === true) {
                    $enabledModules[] = $courier->quotes();
                }
            }
        }

        return $enabledModules;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  plugin name  $plugin
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function store($params)
    {
        return self::$courier->store($params);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  plugin name  $plugin
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function list()
    {
        return self::$courier->list();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return self::$courier->edit($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($params)
    {
        return self::$courier->update($params);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public static function destroy($params)
    {
        return self::$courier->destroy($params);
    }
    
}