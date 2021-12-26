<?php

namespace api\modules\v1\models;

use Yii,
    yii\base\Model;
use common\models\Order,
    common\models\OrderProduct,
    common\models\Product;
use api\modules\v1\models\OrderHelper;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OrderModify
 *
 * @author fobos
 */
class OrderModify extends Model {

    // Set new order
    public static function setNewOrder(): array {

        $userId = Yii::$app->user->id;
        $orderId = 0;
        $ordersId = array();

        // Get card data
        if (($modelCard = Card::findOne(['user_id' => $userId])) === null) {
            return [
                'status' => 'success',
                'error' => 'Card is empty'
            ];
        }

        if ($modelCard->amount <= 0) {
            return [
                'status' => 'success',
                'error' => 'Card is empty'
            ];
        }

        $arrProdsByOrder = OrderHelper::splitProduct($modelCard->cardProducts);

        foreach ($arrProdsByOrder as $key => $val) {

            $orderAmount = 0;
            foreach ($val as $skey => $sval) {
                $orderAmount += $sval['price'];
            }

            $modelOrder = new Order();
            $modelOrder->user_id = $userId;
            $modelOrder->user_shipping_methods_id = null;
            $modelOrder->id1c = null;
            $modelOrder->amount = $orderAmount;
            $modelOrder->create_at = time();
            $modelOrder->status_pay = Order::STATUS_PAY_DEFAULT;
            $modelOrder->status_delivery = Order::STATUS_DELIVERY_DEFAULT;
            $modelOrder->ttn = null;
            $modelOrder->insert();

            $orderId = $modelOrder->id;

            foreach ($val as $pkey => $pval) :
                // Get product data
                $product = Product::findOne(['id' => $pkey]);
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $modelOrderProd = new OrderProduct();
                    $modelOrderProd->order_id = $orderId;
                    $modelOrderProd->product_id = $pkey;
                    $modelOrderProd->quantity = $pval['quantity'];
                    $modelOrderProd->cost = $pval['price'];
                    $modelOrderProd->insert();

                    if ($key == 0) {
                        $product->in_stock -= $pval['quantity'];
                        $product->update();
                    }

                    $transaction->commit();
                } catch (Exception $ex) {
                    $transaction->rollBack();
                }

            endforeach;

            array_push($ordersId, $orderId);
        }

        $modelCard->amount = 0;
        $modelCard->update();

        CardProduct::deleteAll(['card_id' => $modelCard->id]);

        return [
            'status' => 'success',
            'error' => '',
            'orders_id' => $ordersId
        ];
    }

    // Set shipping method for the order
    public static function setShippingMethodOrder(array $putData): array {

        $ordersId = $putData['orders_id'] ?? [];
        $shippingId = $putData['method_id'] ?? 0;

        foreach ($ordersId as $orderId) :

            // Get order data
            if (($modelOrder = Order::findOne(['user_id' => Yii::$app->user->id, 'id' => $orderId])) === null) {
                return [
                    'status' => 'success',
                    'error' => 'Order is empty or not exist'
                ];
            }

            if ($shippingId == 0) {
                return [
                    'status' => 'success',
                    'error' => 'Shipping method not exist'
                ];
            }

            $modelOrder->user_shipping_methods_id = $shippingId;
            $modelOrder->update();
        endforeach;

        return [
            'status' => 'success',
            'error' => '',
            'order_id' => $ordersId
        ];
    }
    
}
