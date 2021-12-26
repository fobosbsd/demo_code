<?php

namespace api\modules\v1\models;

use Yii,
    yii\base\Model,
    yii\helpers\ArrayHelper;
use common\models\Card,
    common\models\CardProduct,
    common\models\Product,
    common\models\Order,
    common\models\OrderProduct;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OrderData
 *
 * @author fobos
 */
class OrderData extends Model {

    // Get products of the order
    public static function get(int $orderId, int $lagnId = 1): array {

        // Test order user       
        if (Order::findOne(['user_id' => Yii::$app->user->id, 'id' => $orderId]) === null) {
            return [
                'status' => 'success',
                'error' => '',
                'data' => []
            ];
        }

        $data = OrderProduct::find()->with(['product' => function($q)use($lagnId) {
                        $q->joinWith(['productTranslates' => function($sq)use($lagnId) {
                                        $sq->where(['country_language_id' => $lagnId]);
                                    }])
                                ->with(['product1cOptionData' => function($sq) {
                                        $sq->with(['productOption']);
                                        $sq->andFilterWhere(['in', 'product_1c_option_value.product_option_id', [1, 3, 4, 13, 5]]);
                                    }])->with('productImage');
                    }])->where(['order_product.order_id' => $orderId])->asArray()->all();

        return [
            'status' => 'success',
            'error' => '',
            'data' => $data
        ];
    }

}
