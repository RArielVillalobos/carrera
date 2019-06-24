<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\Usuario;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\RegistroForm;
use app\models\RecupassForm;
use app\models\CambiapassForm;
use app\models\Permiso;

class SiteController extends Controller
{
    public $usuario_log;
    /**
     * {@inheritdoc}
     */

     public function init() {
        $this->usuario_log = (!empty($_SESSION['__id'])) ? $_SESSION['__id'] : 0;
    }


    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index,view,create,update,delete','recupass'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['login','registro','recupass'],
                        'roles' => ['?'],
                    ],

                    [
                        'actions' => ['index,view,create,update,delete,logout, admin'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback'=>function($rule,$action){
                            return Permiso::requerirRol('administrador') && Permiso::requerirActivo(1);
                        }
                    ],
                    [
                        'actions' => ['index,view,create,logout'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback'=>function($rule,$action){
                            return Permiso::requerirRol('gestor') && Permiso::requerirActivo(1);
                        }
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
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
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
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('index', [
            'model' => $model,
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin(){
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $model = new LoginForm();
        $mensaje="";
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            if(Permiso::requerirActivo(0)){// activado=1
                Yii::$app->user->logout();
                if (Yii::$app->user->isGuest) {
                $mensaje = "Enviamos un email de verificacion y/o activacion a tu correo, abrelo para activar tu cuenta";
                return $this->render('correo', ['mensaje' => $mensaje]);
                // return $this->redirect(site/enviomail)
                }
            }else{
                if (Permiso::requerirRol('administrador')){
                    return $this->redirect(["site/admin"]);
                }elseif(Permiso::requerirRol('gestor')){
                    return $this->redirect(["site/gestor"]); 
                }   
            }
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

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
   /* public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }*/
    /**
     * funcion random para claves,long 50 hexa
     */
    private function randKey($str='', $long=0){//este
        $key = null;
        $str = str_split($str);
        $start = 0;
        $limit = count($str)-1;
        for($x=0; $x<$long; $x++)
        {
            $key .= $str[rand($start, $limit)];
        }
        return $key;
    }

    /**
     * funcion registro de usuarios
     */
    public function actionRegistro()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $mensaje = null;//Mostrará un mensaje
        $model = new RegistroForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                    //vaciamos valores
						   $model->dni = null; $model->password = null; $model->email = null;
                        Yii::$app->session->setFlash('registroFormSubmitted');
                        return $this->refresh();
                }else{
                     $model->dni = null;
                     Yii::$app->getSession()->setFlash('error', 'DNI ingresado ya existe, comunicate con el administrador.');
                }
            }
        $model->dni = '';
        return $this->render('registro', [
            'model' => $model,
        ]);
    }


    /**
     * Displays activa cuenta.
     *
     * @return string
     */
     public function actionActivarcuenta() {
     if (Yii::$app->request->get()){
        $dni = Html::encode($_GET["d"]);
        $authkey =Html::encode($_GET["c"]);
        $usuActivar = Usuario::getElusuario($dni,$authkey);
       if (!empty($usuActivar)) {
            $activar = Usuario::findOne($usuActivar->idUsuario);
            $activar->activado = 1;
            $activar->authkey = $this->randKey("AxWb98760z", 50);//nueva clave será utilizada para activar el usuario
            if ($activar->save()){
                        echo "Registro llevado a cabo correctamente. Será redirigido a la página de Desafio por Bardas...";
                        echo "<meta http-equiv='refresh' content='6; ".Url::toRoute("site/login")."'>";
                            //echo Url::to('site/login');//redirige al login
           } else {
               $mensaje="No se pudo activar la cuenta, comunicate con el administrador";
               return $this->render('error', ['mensaje' => $mensaje]);
            }
       }else{
             $mensaje="No existe usuario registrado con ese numero de documento, vuelva a intentarlo";
             return $this->render('error', ['mensaje' => $mensaje]);
       }
     }else{
      $mensaje="Ups!! hubo un inconveniente, vuelva a intentarlo, redireccionando ...";
      return $this->render('error', ['mensaje' => $mensaje]);
     }
}

     /**
     * funcion recuperar password.
     *
     *  @return string
     */
    public function actionRecupass(){
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $model = new RecupassForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('registroFormSubmitted');
                //return $this->refresh();

                echo "<meta http-equiv='refresh' content='8; ".Url::toRoute("site/login")."'>";
                //return $this->goHome();
            } else {
                Yii::$app->getSession()->setFlash('error', 'Hubo un problema, vuelve a intentarlo.');
            }
        }
        $model->dni='';$model->email='';
        return $this->render('recupass', [
            'model' => $model,
        ]);
    }
    /**
     * Displays cambia password.
     *
     * @return string
     */
    public function actionCambiapass(){
        if (Yii::$app->user->isGuest) {
            return $this->redirect(["site/login"]); 
        }
        $model = new CambiaPassForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->validaCambio()) {
                Yii::$app->user->logout();//se cierra la sesion
                if (Yii::$app->user->isGuest) {
                    return $this->redirect(["site/login"]); //se redirige a iniciar sesion
                }
            } else {
                Yii::$app->getSession()->setFlash('error', 'Hubo un problema, vuelve a intentarlo.');
            }
        }
        return $this->render('cambiapass', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout(){
        return $this->render('about');
    }

    /**
     * Displays admin page.
     *
     * @return string
     */
    public function actionAdmin(){
            $this->layout = '/main2';
            return $this->render('administrar');
    }
    /**
     * Displays admin page.
     *
     * @return string
     */
        public function actionGestor(){
            $this->layout = '/main3';
            return $this->render('gestionar');
    }

        public function actionReglamento(){
        
            return $this->render('reglamento');
    }
}
