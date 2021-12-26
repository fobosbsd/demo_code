<?php

namespace api\modules\v1\models;

use Yii,
    yii\base\Model;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OrderHelper
 *
 * @author fobos
 */
class OrderHelper extends Model {

    // Validate add product to cars
    public static function validateMinimum($product, int $quantity): bool {

        //Get minimal order
        $minOrder = 1;
        foreach ($product->product1cOptionData as $options) :
            if (isset($options['product_option_id']) && $options['product_option_id'] == 5) {
                $minOrder = (int) $options['option_value'] ?? 1;
                break;
            }
        endforeach;

        // Get max product quantity
        $maxQuantity = (int) $product->in_stock;
        
        if($minOrder <= 0) $minOrder = 1;

        $lx = $quantity % $minOrder;
        if ($lx == 0 && $quantity <= $maxQuantity) {
            return true;
        }
        if ($lx == 0 && $quantity > $maxQuantity && $product->in_seller >= 0) {
            return true;
        }
        if ($lx != 0 && $product->in_seller == -1 && $quantity == $maxQuantity) {
            return true;
        }
        if ($lx != 0 && $product->in_seller == -1) {
            return false;
        }

        return false;
    }

    // De products for orders
    public static function splitProduct(array $products) {
        $arrOrdersProd = array();
        foreach ($products as $prod) :
            if (($prod->product->in_stock - $prod->quantity) >= 0) {
                $arrOrdersProd[0][$prod->product_id] = ['quantity' => $prod->quantity, 'price' => $prod->quantity * ($prod->product->price * (1 - $prod->product->discount / 100))];
            } else {
                if ($prod->product->in_seller == 0)
                    $prod->product->in_seller = 60;
                if ($prod->product->in_stock > 0) {
                    $arrOrdersProd[0][$prod->product_id] = ['quantity' => $prod->product->in_stock, 'price' => $prod->product->in_stock * ($prod->product->price * (1 - $prod->product->discount / 100))];
                    $arrOrdersProd[$prod->product->in_seller][$prod->product_id] = ['quantity' => $prod->quantity - $prod->product->in_stock, 'price' => ($prod->quantity - $prod->product->in_stock) * ($prod->product->price * (1 - $prod->product->discount / 100))];
                } else {
                    $arrOrdersProd[$prod->product->in_seller][$prod->product_id] = ['quantity' => $prod->quantity, 'price' => $prod->quantity * ($prod->product->price * (1 - $prod->product->discount / 100))];
                }
            }
        endforeach;

        return $arrOrdersProd;
    }

}
