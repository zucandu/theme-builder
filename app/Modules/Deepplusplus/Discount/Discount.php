<?php

namespace App\Modules\Deepplusplus\Discount;

/**
 * Class Discount.
 *
 */
class Discount {

    /**
     * Load all of discount modules in admin
     */
    public static function showModules()
    {
        $discountModules = [];

        $path = app_path() . '/Modules/Deepplusplus/Discount/*.php';
        foreach (glob($path) as $module) {
            $className = basename($module, '.php');
            if($className !== 'Discount') {
                $discount = new $className;
                $discountModules[] = [
                    'code' => $discount->code,
                    'name' => $discount->name,
                    'status' => $discount->status(true),
                    'description' => $discount->description,
                    'fields' => $discount->fields()
                ];
            }
        }
        return $discountModules;
    }

    /**
     * Show enabled discount modules on the checkout
     */
    public static function enabledModules() {
        
        $enabledModules = [];

        $path = app_path() . '/Modules/Deepplusplus/Discount/*.php';
        foreach (glob($path) as $module) {
            $className = basename($module, '.php');
            if($className !== 'Discount') {
                $discount = new $className;
                if($discount->status() === true) {
                    $enabledModules[] = $discount->selection();
                }
            }
        }

        return $enabledModules;
    }
    
}