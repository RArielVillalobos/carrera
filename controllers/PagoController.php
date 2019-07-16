<?php

namespace app\controllers;

use Yii;
use app\models\Pago;
use app\models\PagoSearch;
use app\models\Usuario;
use app\models\Permiso;
use app\models\Persona;
use app\models\Equipo;
use app\models\Grupo;
use app\models\Tipocarrera;
use app\models\Importeinscripcion;
use app\models\Controlpago;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use yii\data\arrayDataProvider;
use yii\db\Query; 
/**
 * PagoController implements the CRUD actions for Pago model.
 */
class PagoController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Pago models.
     * @return mixed
     */
    public function actionIndex()
    {
        if(Permiso::requerirRol('administrador')){
            $this->layout='/main2';
        }elseif(Permiso::requerirRol('gestor')){
            $this->layout='/main3';
        }
        $searchModel = new PagoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        //echo '<pre>';var_dump($dataProvider);echo '</pre>';die();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Pago models.
     * @return mixed
     */
    public function actionIndex1()
    {
        if(Permiso::requerirRol('administrador')){
            $this->layout='/main2';
        }elseif(Permiso::requerirRol('gestor')){
            $this->layout='/main3';
        }
        $searchModel = new PagoSearch();
        $dataProvider = $searchModel->check();
        //echo '<pre>';var_dump($dataProvider);echo '</pre>';die();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Pago models.
     * @return mixed
     */
    public function actionIndex2()
    {
        if(Permiso::requerirRol('administrador')){
            $this->layout='/main2';
        }elseif(Permiso::requerirRol('gestor')){
            $this->layout='/main3';
        }
        $searchModel = new PagoSearch();
        $dataProvider = $searchModel->nocheck();
        //echo '<pre>';var_dump($dataProvider);echo '</pre>';die();
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    

    /**
     * Displays a single Pago model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        if(Permiso::requerirRol('administrador')){
            $this->layout='/main2';
        }elseif(Permiso::requerirRol('gestor')){
            $this->layout='/main3';
        }
        $model= $this->findModel($id);
        return $this->render('view', [
            'model' => $model,
            'controlpago'=>Controlpago::findOne(['idPago'=>$model->idPago]),
        ]);
    }
    /**
     * Vista para elusuario una vez ingresado el pago.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView1($id)
    {
        return $this->render('view1', [
            'model' => $this->findModel($id),]//vista para el usuario
        );
    }

    
    /**
     * Crea pagos por el usuario dnicapitan.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(["site/login"]); 
        }
        if(Permiso::requerirRol('administrador')){
            $this->layout='/main2';
        }elseif(Permiso::requerirRol('gestor')){
            $this->layout='/main3';
        }
        $saldo='';
        //obtenemos todos los modelos necearios para registrar el pago
        $usuario=Usuario::findIdentity($_SESSION['__id']);
        $persona=Persona::findOne(['idUsuario' => $_SESSION['__id']]);
        if($grupo=Grupo::findOne(['idPersona'=>$persona->idPersona])){
              $equipo=Equipo::findOne(['idEquipo'=>$grupo->idEquipo]);
              $suma=Pago::sumaTotalEquipo($grupo->idEquipo);
              $tipocarrera=TipoCarrera::findOne(['idTipoCarrera'=>$equipo->idTipoCarrera]);
              $importecarrera=Importeinscripcion::findOne(['idTipoCarrera'=>$equipo->idTipoCarrera]);
              $saldo=$importecarrera->importe - $suma;//saldo de lo pagado para control form
        }else{
            return $this->goHome();  
        }
       
        $guardado=false; //Asignamos false a la variable guardado
        $transaction = Pago::getDb()->beginTransaction(); // Iniciamos una transaccion
        $model = new Pago();
        if ($model->load(Yii::$app->request->post())) {
           try {
       
            $model->idPersona=$persona->idPersona;
        //preparamos el modelo para guarar la imagen del ticket
           $model->imagenComprobante = UploadedFile::getInstance($model, 'imagenComprobante');
           $imagen_nombre=rand(0,4000).'pers_'.$model->idPersona.'.'.$model->imagenComprobante->extension;
           $imagen_dir='archivo/pagoinscripcion/'.$imagen_nombre;
           $model->imagenComprobante->saveAs($imagen_dir);
           $model->imagenComprobante=$imagen_dir;
           $model->idEquipo=$equipo->idEquipo;
           $model->idImporte=$importecarrera->idImporte;   
           if(!$model->save()){
              throw new Exception('pago error al cargar datos');
           }
            $idpago = Yii::$app->db->getLastInsertID(); //Obtenemos el ID del ultimo usuario ingresado
            
            $model1=new Controlpago;
            $model1->idPago=$idpago;
            $model1->chequeado=0;
            $model1->idGestor=1;
            if(!$model1->save()){
                throw new Exception('controlpago error al cargar datos');
           }//fin carga tabla pago
                $transaction->commit();
                $guardado=true;
                if ($guardado){ 
                    if($importecarrera->importe == $model->importePagado){
                       $total='pago total';//pago total
                       Yii::$app->session->setFlash('pagoTotal');//enviamos mensaje
                       return $this->redirect(['view1', 'id' => $idpago]);
               
                    }elseif($importecarrera->importe > $model->importePagado){
                        $total='pago parcial';//pago parcial
                        Yii::$app->session->setFlash('pagoParcial');//enviamos mensaje
                        return $this->redirect(['view1', 'id' => $idpago]);
              
                    }   
                }else{//fin guardado true
                    $this->refresh();
                    $this->goHome();//envia a pagina principal
                }
      
        } catch(\Exception $e) {//atrapa el error
           $error=$e->getMessage();//mensaje de error

            $transaction->rollBack();
            throw $e;
          }
     }//fin verificarion de datos
        $this->refresh();
        return $this->render('create', [//renderiza al formulario pago
            'model' => $model,
            'equipo'=> $equipo,//dniCapitan,idEquipo,idTipoCarrera
            'persona'=> $persona,//idPersona
            'usuario'=> $usuario,//idUsuario, dniUsuario,mailUsuario
            'tipocarrera'=>$tipocarrera,//descripcionCarrera
            'importecarrera'=>$importecarrera,//importe del tipo de carrera
            'saldo'=>$saldo,//saldo de lo pagado
            ]);
        }

    /**
     * Crea pagos desde la pagina de gestores.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate1()
    {
        if(Permiso::requerirRol('administrador')){
                $this->layout='/main2';
        }elseif(Permiso::requerirRol('gestor')){
                $this->layout='/main3';
        }
       
        $model = new Pago();

        if ($model->load(Yii::$app->request->post())) {
            $usuario=Usuario::findOne(['dniUsuario'=>$model->dniUsu]);
            $persona=Persona::findOne(['idUsuario' =>$usuario->idUsuario]);
            if($grupo=Grupo::findOne(['idPersona'=>$persona->idPersona])){
                $equipo=Equipo::findOne(['idEquipo'=>$grupo->idEquipo]);
                $tipocarrera=TipoCarrera::findOne(['idTipoCarrera'=>$equipo->idTipoCarrera]);
                $importecarrera=Importeinscripcion::findOne(['idTipoCarrera'=>$equipo->idTipoCarrera]);
            } 
            $model->idPersona=$persona->idPersona;
        
           $model->imagenComprobante = UploadedFile::getInstance($model, 'imagenComprobante');
           $imagen_nombre='persona_'.$model->idPersona.'.'.$model->imagenComprobante->extension;
           $imagen_dir='archivo/pagoinscripcion/'.$imagen_nombre;
           $model->imagenComprobante->saveAs($imagen_dir);
           $model->imagenComprobante=$imagen_dir;
           $model->idEquipo=$equipo->idEquipo;
           $model->idImporte=$importecarrera->idImporte;
           
           if($model->save()){
            $idpago = Yii::$app->db->getLastInsertID(); //Obtenemos el ID del ultimo usuario ingresado
            $model1=new Controlpago;
            $model1->idPago=$idpago;
            $model1->chequeado=0;
            $model1->idGestor=1;
            if($model1->save()){
               
              if($importecarrera->importe == $model->importePagado){
                $total='pago total';//pago total
                Yii::$app->session->setFlash('pagoTotal');//enviamos mensaje
                return $this->redirect(['view1', 'id' => $idpago]);
              
              }elseif($importecarrera->importe > $model->importePagado){
                $total='pago parcial';//pago parcial
                Yii::$app->session->setFlash('pagoParcial');//enviamos mensaje
                return $this->redirect(['view', 'id' => $idpago]);
               
              }   
            }else{
                //mandamos error
                $error=$model1->errors;
            }
          }
        }
    
        return $this->render('create1', [
            'model' => $model,
            
        ]);
}


    /**
     * Updates an existing Pago model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        if(Permiso::requerirRol('administrador')){
            $this->layout='/main2';
        }elseif(Permiso::requerirRol('gestor')){
            $this->layout='/main3';
        }
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->idPago]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Pago model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        if(Permiso::requerirRol('administrador')){
            $this->layout='/main2';
        }elseif(Permiso::requerirRol('gestor')){
            $this->layout='/main3';
        }
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Pago model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Pago the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Pago::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
