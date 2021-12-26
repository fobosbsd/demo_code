<?php

namespace api\modules\v1\models;

use Yii,
    yii\base\Model;
use common\models\Card,
    common\models\CardProduct,
    common\models\Product;
use api\modules\v1\models\OrderHelper;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CardModify
 *
 * @author fobos
 */
class CardModify extends Model {

    // Set products to the card
    public static function set(array $putData): array {

        $userId = Yii::$app->user->id;
        $productId = $putData['product_id'] ?? 0;
        $quantity = $putData['quantity'] ?? 1;

        // Get product data
        $product = Product::findOne(['id' => $productId]);

        if ($product === null) {
            return [
                'status' => 'error',
                'error' => 'Product with this ID not exist',
                'data' => []
            ];
        }

        if (OrderHelper::validateMinimum($product, $quantity) === false) {
            return [
                'status' => 'error',
                'error' => 'You dont order this product of the quantity',
                'data' => []
            ];
        }

        if (($modelCard = Card::findOne(['user_id' => $userId])) === null) {
            $modelCard = new Card();
            $modelCard->user_id = $userId;
            $modelCard->create_at = time();
            $modelCard->amount = round($quantity * $product->price, 2);
            $modelCard->save();
        } else {
            $modelCard->amount += round($quantity * $product->price, 2);
            $modelCard->save();
        }

        if (($modelCardProd = CardProduct::findOne(['card_id' => $modelCard->id, 'product_id' => $productId])) === null) {
            $modelCardProd = new CardProduct();
            $modelCardProd->card_id = $modelCard->id;
            $modelCardProd->product_id = $productId;
            $modelCardProd->quantity = $quantity;
            $modelCardProd->cost = round($quantity * $product->price, 2);
            $modelCardProd->save();
        } else {
            $modelCardProd->quantity = $quantity;
            $modelCardProd->cost = round($quantity * $product->price, 2);
            $modelCardProd->save();
        }

        return [
            'status' => 'success',
            'error' => ''
        ];
    }

    // Clean products from the card
    public static function clean(): array {

        // Get card id
        $cardId = Card::find()->where(['user_id' => Yii::$app->user->id])->scalar();

        CardProduct::deleteAll(['card_id' => $cardId]);

        return [
            'status' => 'success',
            'error' => ''
        ];
    }

    // Set products to the card
    public static function removeProductFromCard(array $deleteData): array {

        $productId = (isset($deleteData['product_id'])) ? (int) $deleteData['product_id'] : 0;

        // Get card id
        $cardId = Card::find()->where(['user_id' => Yii::$app->user->id])->scalar();

        CardProduct::deleteAll(['card_id' => $cardId, 'product_id' => $productId]);

        return [
            'status' => 'success',
            'error' => ''
        ];
    }

}
