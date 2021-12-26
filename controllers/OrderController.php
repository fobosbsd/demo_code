<?php

namespace api\modules\v1\controllers;

use Yii,
    yii\filters\auth\HttpBearerAuth,
    yii\filters\VerbFilter,
    yii\filters\AccessControl;
use yii\rest\Controller;
use api\modules\v1\models\OrderData,
    api\modules\v1\models\CardData,
    api\modules\v1\models\OrderModify,
    api\modules\v1\models\CardModify;

/**
 * Description of OrderController
 *
 * @author fobos
 */
class OrderController extends Controller {

    public function behaviors() {
        $behaviors = parent::behaviors();

        // Filter actions
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['get-card', 'set-card', 'clean-card', 'remove-from-card', 'add-order', 'add-order-shipping', 'get-item'],
            'rules' => [
                [
                    'allow' => true,
                    'verbs' => ['POST', 'PUT', 'GET', 'DELETE', 'OPTIONS'],
                ],
                [
                    'actions' => ['get-card', 'set-card', 'clean-card', 'remove-from-card', 'add-order', 'add-order-shipping', 'get-item'],
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ],
        ];

        $behaviors['corsFilter'] = [
            'class' => \common\filters\CorsCustom::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Allow-Origin' => ['*'],
                'Access-Control-Request-Method' => ['POST', 'PUT', 'GET', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => false,
            ],
        ];

        // Filter actions
        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'set-card' => ['put', 'options'],
                'get-card' => ['post', 'options'],
                'clean-card' => ['post', 'options'],
                'remove-from-card' => ['delete', 'options'],
                'add-order' => ['post', 'options'],
                'add-order-shipping' => ['put', 'options'],
                'get-item' => ['post', 'options'],
            ]
        ];

        unset($behaviors['authenticator']);
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'only' => ['set-card', 'get-card', 'clean-card', 'remove-from-card', 'add-order', 'add-order-shipping', 'get-item']
        ];

        return $behaviors;
    }

    // Get card data
    public function actionGetCard() {
        $lagnId = (int) Yii::$app->request->post('country_language_id', 1);
        return CardData::get($lagnId);
    }

    // Set card data
    public function actionSetCard() {
        return CardModify::set(Yii::$app->request->getBodyParams());
    }

    // Clean card data
    public function actionCleanCard() {
        return CardModify::clean();
    }

    // Remove product from card
    public function actionRemoveFromCard() {
        return CardModify::removeProductFromCard(Yii::$app->request->getBodyParams());
    }

    // Set new order
    public function actionAddOrder() {
        return OrderModify::setNewOrder();
    }

    // Set shipping method for order
    public function actionAddOrderShipping() {
        return OrderModify::setShippingMethodOrder(Yii::$app->request->getBodyParams());
    }
    
    // Get one order data
    public function actionGetItem() {
        $orderId = (int) Yii::$app->request->post('order_id', 0);
        $lagnId = (int) Yii::$app->request->post('country_language_id', 1);
        return OrderData::get($orderId, $lagnId);
    }

}
