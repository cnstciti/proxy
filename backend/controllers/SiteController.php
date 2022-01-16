<?php

namespace backend\controllers;

use common\models\LoginForm;
use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use backend\models\form\UploadForm;
use yii\web\UploadedFile;
use common\models\Address;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * Загрузка данных с неизвестным типом
     *
     */
    public function actionLoadDataUnknown()
    {
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $loadFile = UploadedFile::getInstance($model, 'loadFile');
            $result = Address::loadFromFile($loadFile, '');

            return $this->render('upload-finish', ['result' => $result]);
        }

        return $this->render('upload-unknown', ['model' => $model]);
    }

    /**
     * Загрузка данных с типом HTTP
     *
     */
    public function actionLoadDataHttp()
    {
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $loadFile = UploadedFile::getInstance($model, 'loadFile');
            $result = Address::loadFromFile($loadFile, 'HTTP');

            return $this->render('upload-finish', ['result' => $result]);
        }

        return $this->render('upload-http', ['model' => $model]);
    }


    /**
     * {@inheritdoc}
     * /
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return string|Response
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = 'blank';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
