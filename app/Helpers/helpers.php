<?php



    function display_price($price, $currency = '₹') {
        if (is_null($price) || $price <= 0) {
            return '';
        }
        // Format without decimals
        return $currency . number_format($price, 0);
    }


