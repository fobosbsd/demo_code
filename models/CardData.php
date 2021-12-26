<?php

namespace api\modules\v1\models;

use Yii,
    yii\base\Model;
use common\models\Card,
    common\models\CardProduct;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CardData
 *
 * @author fobos
 */
class CardData extends Model {

    // Get products of the card
    public static function get(int $lagnId = 1): array {

        // Get card id
        $cardId = Card::find()->where(['user_id' => Yii::$app->user->id])->scalar();

        if ($cardId == 0) {
            return [
                'status' => 'success',
                'error' => '',
                'data' => []
            ];
        }

        $data = CardProduct::find()->with(['product' => function($q)use($lagnId) {
                        $q->joinWith(['productTranslates' => function($sq)use($lagnId) {
                                        $sq->where(['country_language_id' => $lagnId]);
                                    }])
                                ->with(['product1cOptionData' => function($sq) {
                                        $sq->with(['productOption']);
                                    }])->with('productImage');
                    }])->where(['card_product.card_id' => $cardId])->asArray()->all();

        return [
            'status' => 'success',
            'error' => '',
            'data' => $data
        ];
    }

}
