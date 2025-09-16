<?php
require_once 'vendor/veritrans/veritrans-php/Veritrans.php';

class MidtransConfig {
    public static function init($serverKey, $isProduction = false) {
        Veritrans_Config::$serverKey = $serverKey;
        Veritrans_Config::$isProduction = $isProduction;
        Veritrans_Config::$isSanitized = true;
        Veritrans_Config::$is3ds = true;
    }
    
    public static function createTransaction($params) {
        try {
            $snapToken = Veritrans_Snap::getSnapToken($params);
            return $snapToken;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>