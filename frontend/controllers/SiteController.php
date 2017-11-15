<?php
namespace frontend\controllers;

use backend\models\GoodsCategory;
use Yii;
use yii\base\InvalidParamException;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
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
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                'minLength'=>3,
                'maxLength'=>3,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }


    //商品列表
    public function actionList($goods_category_id){
        //商品分类  一级  二级  三级
        $goods_category = GoodsCategory::findOne(['id'=>$goods_category_id]);
        //三级分类
        if($goods_category->depth == 2){
            $query = Goods::find()->where(['goods_category_id'=>$goods_category_id]);

        }else{
            //二级分类  14
            //获取二级分类下面的所有三级分类 17,18
            //根据三级分类id[17,18,20,21,22,9999]查商品
            // sql: select * from goods where goods_category_id=17 or goods_category_id=18 or goods_category_id=18 or goods_category_id=18 or goods_category_id=18 or goods_category_id=18 or goods_category_id=18 or goods_category_id=18
            //select * from goods where  goods_category_id in (17,18,29...999)
            /*$result = $goods_category->children(1)->all();
            //var_dump($result);exit;
            $ids = [];
            foreach ($result as $category){
                $ids[] = $category->id;
            }*/
            //sql:select * from goodscategory where parent_id=14
            //$ids = $goods_category->children()->andWhere(['depth'=>2])->column();
            $ids = $goods_category->children()->andWhere(['depth'=>2])->column();
            //$goods_category->children(1)->
            //$ids = [17,18];
            //var_dump($ids);exit;
            $query = Goods::find()->where(['in','goods_category_id',$ids]);

        }/*elseif ($goods_category->depth == 0){
            //一级分类
            $ids = $goods_category->children(2)->andWhere(['depth'=>2])->column();
            var_dump($ids);exit;
        }*/

        $pager = new Pagination();
        $pager->totalCount = $query->count();
        $pager->pageSize = 20;

        $models = $query->limit($pager->limit)->offset($pager->offset)->all();
        return $this->render('list',['models'=>$models,'pager'=>$pager]);
    }
}
