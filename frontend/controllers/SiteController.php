<?php
namespace frontend\controllers;

use backend\models\GoodsCategory;
use frontend\models\Goods;
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
use yii\web\Cookie;

/**
 * Site controller
 */
class SiteController extends Controller
{
    public $enableCsrfValidation = false;
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

    //测试:将测试数据保存到cookie
    public function actionTestCart(){
        $carts = ['1'=>'3','2'=>'2'];
        $cookies = Yii::$app->response->cookies;
        $cookie = new Cookie();
        $cookie->name = 'carts';
        $cookie->value = serialize($carts);
        $cookies->add($cookie);
        echo '购物车测试数据准备完成';
    }
    //添加购物车   商品详情页,点击添加到购物车---->添加成功提示页(商品已经添加到购物车)---->进入购物车,显示购物车商品
    public function actionAddCart($goods_id,$amount){
        //(商品已经添加到购物车)添加操作是在当前页面执行
        //需要判断登录和未登录
        if(Yii::$app->user->isGuest){
            //操作cookie购物车

            //获取cookie中购物车数据
            $cookies = Yii::$app->request->cookies;
            $carts = $cookies->getValue('carts');
            if($carts){
                $carts = unserialize($carts);//$carts = ['1'=>'3','2'=>'2'];
            }else{
                $carts = [];
            }

            //$carts = [2=>10];//=>[2=>10,1=>99]
            //购物车中是否存在该商品,如果存在数量累加 不存在,直接添加
            if(array_key_exists($goods_id,$carts)){
                $carts[$goods_id] += $amount;
            }else{
                $carts[$goods_id] = $amount;
            }
            //var_dump($carts);exit;
            //$carts = [$goods_id=>$amount];
            $cookies = Yii::$app->response->cookies;
            $cookie = new Cookie();
            $cookie->name = 'carts';
            $cookie->value = serialize($carts);
            $cookies->add($cookie);


        }else{
            //操作数据库购物车
        }

        //跳转到购物车页面
        return $this->redirect(['cart']);// site=>site/login  member=>member/login
    }


    //购物车页面
    public function actionCart(){
        //需要判断登录和未登录
        if(Yii::$app->user->isGuest){
            //未登录,购物车数据存放到cookie
            //举例: id为1的商品3个 id为8的商品有2个
            /*$carts = [
                ['goods_id'=>1,'amount'=>3],
                ['goods_id'=>8,'amount'=>2],
            ];*/
            //能否使用使用一维数组来简化购物车
            //$carts = ['1'=>'3','2'=>'2'];

            //从cookie中取出购物车数据,调用视图展示
            $cookies = Yii::$app->request->cookies;
            $carts = $cookies->getValue('carts');
            if($carts){
                $carts = unserialize($carts);//$carts = ['1'=>'3','2'=>'2'];
            }else{
                $carts = [];
            }
            //$carts肯定是一个数组
            //获取购物车商品信息
            $models = Goods::find()->where(['in','id',array_keys($carts)])->all();
            //$models = [GOODS,GOODS,GOODS]
            //var_dump($models);exit;


        }else{
            //已登录,购物车数据存放到数据表
            //$carts= [CART,CART...];  =>['1'=>'3','2'=>'2']
            //$carts需要转换一下格式
        }
        return $this->render('cart',['carts'=>$carts,'models'=>$models]);
    }

    //AJAX操作购物车
    public function actionAjaxCart($type){
        //登录操作数据库 未登录操作cookie
        switch ($type){
            case 'change'://修改购物车
                $goods_id = Yii::$app->request->post('goods_id');
                $amount = Yii::$app->request->post('amount');
                if(Yii::$app->user->isGuest){
                    //取出cookie中的购物车
                    $cookies = Yii::$app->request->cookies;
                    $carts = $cookies->getValue('carts');
                    if($carts){
                        $carts = unserialize($carts);//$carts = ['1'=>'3','2'=>'2'];
                    }else{
                        $carts = [];
                    }
                    //修改购物车商品数量
                    $carts[$goods_id] = $amount;
                    //保存cookie
                    $cookies = Yii::$app->response->cookies;
                    $cookie = new Cookie();
                    $cookie->name = 'carts';
                    $cookie->value = serialize($carts);
                    $cookies->add($cookie);

                }else{

                }
                break;
            case 'del':

                break;
        }
    }
}
