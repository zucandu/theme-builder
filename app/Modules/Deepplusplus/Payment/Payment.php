<?php

namespace App\Modules\Deepplusplus\Payment;

/**
 * Class Payment.
 *
 */
class Payment {

    public static $payment;

    public static function init($payment) {
        $payment = ucfirst($payment);
        return self::$payment = new $payment;
    }

    /**
     * Load all of payment modules in admin
     */
    public static function showModules()
    {
        $paymentModules = [];

        $path = app_path() . '/Modules/Deepplusplus/Payment/*.php';
        foreach (glob($path) as $module) {
            $className = basename($module, '.php');
            if($className !== 'Payment') {
                $payment = new $className;
                $paymentModules[] = [
                    'code' => $payment->code,
                    'name' => $payment->name,
                    'status' => $payment->status(true),
                    'description' => $payment->description,
                    'fields' => $payment->fields()
                ];
            }
        }
        return $paymentModules;
    }

    /**
     * Show enabled shipping modules on the checkout
     */
    public static function enabledModules() {
        
        $enabledModules = [];

        $path = app_path() . '/Modules/Deepplusplus/Payment/*.php';
        foreach (glob($path) as $module) {
            $className = basename($module, '.php');
            if($className !== 'Payment') {
                $payment = new $className;
                if($payment->status() === true) {
                    $enabledModules[] = $payment->selection();
                }
            }
        }

        return $enabledModules;
    }

}