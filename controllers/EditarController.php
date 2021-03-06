<?php
namespace app\controllers;
use app\models\Estadopagopersona;
use app\models\Talleremera;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\Persona;
use app\models\Usuario;
use app\models\Fichamedica;
use app\models\Localidad;
use app\models\Sexo;
use app\models\Gruposanguineo;
use app\models\Personadireccion;
use app\models\Personaemergencia;
use app\models\Provincia;
use yii\helpers\ArrayHelper;
use app\models\Respuesta;
use app\models\RespuestaOpcion;
use app\models\Equipo;
use app\models\Grupo;
use app\models\Parametros;
use app\models\Carrerapersona;
use app\models\Listadeespera;
use app\models\Permiso;
use yii\base\Security;
use yii\helper\Json;

class EditarController extends Controller
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
    //muestra el formulario para actualizar los datos
    public function actionEditar(){
        if (Yii::$app->user->isGuest) {
            return $this->redirect(["site/login"]);
        }

        $persona=Persona::findOne(['idUsuario' => $_SESSION['__id']]);
        //busca la persona logueada

        //si la persona logueada no se inscribio, no puede modificar sus datos
        if($persona==null){
            return $this->goHome();
        }
        $usuario=Usuario::findOne(['idUsuario'=>$_SESSION['__id']]);
        $equipo=Equipo::findOne(['dniCapitan'=>$usuario->dniUsuario]);
        $nombreCapitan=null;
        $capitan=false;
        //si es capitan
        if($equipo!=null){
            $swicht=1;
            $tipoCarrera=$equipo->tipoCarrera;
            $cantCorredores=$equipo->cantidadPersonas;
            $capitan=true;
            $tipocarreraLista =ArrayHelper::map(\app\models\Tipocarrera::findAll(['idTipoCarrera'=>$equipo->idTipoCarrera]),'idTipoCarrera','descripcionCarrera');
        }else{
            //como no es capitan se pone en false
            $capitan=false;
            //se accede al grupo de la persona autenticada
            $grupo=Grupo::findOne(['idPersona'=>$persona->idPersona]);
            //se accede al modelo del equipo
            $equipo=$grupo->equipo;
            $tipoCarrera=$equipo->tipoCarrera;
            $cantCorredores=$equipo->cantidadPersonas;
            $swicht=0;
            $tipocarreraLista =ArrayHelper::map(\app\models\Tipocarrera::findAll(['idTipoCarrera'=>$equipo->idTipoCarrera]),'idTipoCarrera','descripcionCarrera');
            //accedemos al usuario del capitan
            $usuarioCapitan=Usuario::findOne(['dniUsuario'=>$equipo->dniCapitan]);
            $personaCapitan=Persona::findOne(['idUsuario'=>$usuarioCapitan->idUsuario]);
            $nombreCapitan=$personaCapitan->nombrePersona.' '.$personaCapitan->apellidoPersona;
        }
        $personaDireccion=$persona->personaDireccion;
        //accedemos a la ficha medica de la persona
        $fichaMedica =$persona->fichaMedica;
        //accedemos a el modelo personaEmergencia de la persona
        $datosEmergencia =$persona->personaEmergencia;
        $localidad = $personaDireccion->localidad;
        $provincia = $localidad->provincia;
        //accedemos al modelo talle remera de la persona
        $talleRemera=$persona->talleRemera;
        $provinciaLista = ArrayHelper::map(\app\models\Provincia::find()->all(),'idProvincia','nombreProvincia'); //Lista de las provincias
        $listadoTalles=ArrayHelper::map(\app\models\Talleremera::find()->all(),'idTalleRemera','talleRemera');
        //$respuesta=new \app\models\Respuesta();
        //$tipoCarrera = new \app\models\Tipocarrera(); //Instanciamos una variable
        //$cantCorredores =ArrayHelper::map(\app\models\Parametros::findAll(['cantidadCorredores'=>$equipo->cantidadPersonas]),'idParametros','cantidadCorredores');
        $carrerapersona = new \app\models\Carrerapersona();
        $equipoLista= ArrayHelper::map(\app\models\Equipo::find()
            ->select('COUNT(equipo.idEquipo) AS cantidadCorredores','grupo.idEquipo,equipo.cantidadPersonas,equipo.dniCapitan,')
            ->innerJoin('grupo','equipo.idEquipo=grupo.idEquipo')
            ->groupBy(['equipo.idEquipo'])
            ->having('COUNT(equipo.idEquipo)<equipo.cantidadPersonas')
            ->all(),'idEquipo','dniCapitan');
        if(yii::$app->user->isGuest){
            return $this->goHome();
        }
        $userLogueado=Yii::$app->user;
        return $this->render('indexEdit',[
            'capitan'=>$capitan,
            'persona'=>$persona,
            'usuario'=>$usuario,
            'personaDireccion'=>$personaDireccion,
            'fichaMedica'=>$fichaMedica,
            'datosEmergencia'=>$datosEmergencia,
            'localidad' => $localidad,
            'provincia' => $provincia,
            'provinciaLista' => $provinciaLista,
            'listadoTalles'=>$listadoTalles,
            'talleRemera'=>$talleRemera,
            'equipoLista'=>$equipoLista,
            'equipo'=>$equipo,
            'tipoCarrera'=>$tipoCarrera,
            'tipocarreraLista'=>$tipocarreraLista,
            'cantCorredores'=>$cantCorredores,
            'swicht'=>$swicht,
            'datos' => null,
            // 'respuesta'=>$respuesta,
            'user'=>$userLogueado,
            'carrerapersona'=>$carrerapersona,
            'nombreCapitan'=>$nombreCapitan,
        ]);
    }
    //funcion que actualiza los datos en la bd
    public function actionUpdate(){



        $transaction=Yii::$app->db->beginTransaction();
        try{
            //se accede a los datos Persona del formulario
            $personaForm=Yii::$app->request->post()['Persona'];
            //se accede a los datos TalleRemera del formulario
            $talleRemeraForm=Yii::$app->request->post()['Talleremera'];
            //se accede a los datos TersonaEmergencia del formulario
            $personaEmergenciaForm=Yii::$app->request->post()['Personaemergencia'];
            //se accede al modeo FichaMedica del formulario
            $fichaMedicaForm=Yii::$app->request->post()['Fichamedica'];
            //se busca el modelo Persona de la persona logueada
            $persona=Persona::findOne(['idUsuario' => $_SESSION['__id']]);
            //se accede al modelo PersonaEmrgencia del usuario
            $personaEmergencia=$persona->personaEmergencia;
            //se accede al modelo PersonaDireccion del usuario
            $personaDireccion=$persona->personaDireccion;
            $personaDireccion->idLocalidad=Yii::$app->request->post()['Personadireccion']['idLocalidad'];
            $personaDireccion->direccionUsuario=Yii::$app->request->post()['calle'];
            //se actualiza el modelo
            $personaDireccion->save();
            $personaEmergencia->nombrePersonaEmergencia=$personaEmergenciaForm['nombrePersonaEmergencia'];
            $personaEmergencia->apellidoPersonaEmergencia=$personaEmergenciaForm['apellidoPersonaEmergencia'];
            $personaEmergencia->telefonoPersonaEmergencia=$personaEmergenciaForm['telefonoPersonaEmergencia'];
            $personaEmergencia->idVinculoPersonaEmergencia=$personaEmergenciaForm['idVinculoPersonaEmergencia'];
            //se actualiza  el modelo
            $personaEmergencia->save();
            //accedemos al modelo FichaMedica de la persona
            $fichaMedica=$persona->fichaMedica;
            $fichaMedica->obraSocial=$fichaMedicaForm['obraSocial'];
            $fichaMedica->peso=$fichaMedicaForm['peso'];
            $fichaMedica->altura=$fichaMedicaForm['altura'];
            $fichaMedica->frecuenciaCardiaca=$fichaMedicaForm['frecuenciaCardiaca'];
            $fichaMedica->idGrupoSanguineo=$fichaMedicaForm['idGrupoSanguineo'];
            $fichaMedica->evaluacionMedica=$fichaMedicaForm['evaluacionMedica'];
            $fichaMedica->intervencionQuirurgica=$fichaMedicaForm['intervencionQuirurgica'];
            $fichaMedica->suplementos=$fichaMedicaForm['suplementos'];
            $fichaMedica->tomaMedicamentos=$fichaMedicaForm['tomaMedicamentos'];
            $fichaMedica->observaciones=$fichaMedicaForm['observaciones'];
            //se actualiza el modelo
            $fichaMedica->save();
            $persona->nacionalidadPersona=$personaForm['nacionalidadPersona'];
            $persona->nombrePersona=$personaForm['nombrePersona'];
            $persona->apellidoPersona=$personaForm['apellidoPersona'];
            $persona->fechaNacPersona=$personaForm['fechaNacPersona'];
            $persona->telefonoPersona=$personaForm['telefonoPersona'];
            $persona->donador=$personaForm['donador'];
            $persona->idTalleRemera=$talleRemeraForm['idTalleRemera'];
            $persona->sexoPersona=$personaForm['sexoPersona'];


            //si el usuario introduce el mismo email que el que tenia no se actualiza
            if($persona->mismoUsuarioEmail($personaForm['mailPersona'])==false){
                //de lo contrario, primero verifica que no exista un usuario cone l mismo email
                if($persona->noExisteEmail($personaForm['mailPersona'])){
                    $persona->mailPersona=$personaForm['mailPersona'];
                    if($persona->save()){
                        $usuarioDelaPersona=$persona->usuario;
                        $usuarioDelaPersona->mailUsuario=$personaForm['mailPersona'];
                        $actualizado=$usuarioDelaPersona->save();

                    }
                    //si el email ya esta introducido no se guardan los datos
                }else{
                    $actualizado=false;
                }

             //si el usuario no quiere actualizar su email, se guardan los demas registros , sin actualizar el email
            }else{
                $actualizado=$persona->save();
            }


            if($actualizado){
                $transaction->commit();
                $guardado=1;
            }else{
                $transaction->rollBack();
                $guardado=0;
            }

            if ($guardado){ // Si la actualizacion es correcta se redirecciona al index
                $mensaje = "Se actualizaron correctamente tus datos ";
                return Yii::$app->response->redirect(['site/index','guardado'=>$guardado,'mensaje'=>$mensaje])->send();
            }else{
                $mensaje = "Ha ocurrido un error,vuelve a intentarlo";
                return Yii::$app->response->redirect(['site/index','guardado'=>$guardado,'mensaje'=>$mensaje])->send();
            }
        }catch (\Exception $e){
            $transaction->rollBack();
            throw $e;
        }
    }
    /**
     * Lista del modelo de Contacto emergencia.
     * @return mixed
     */
    public function actionContactoemergencia()
    {
        $model = new \app\models\Personaemergencia(); //Instanciamos una variable
        return $this->render('contactoemergencia', [
            'model' => $model,
        ]);
    }
    /**
     * Lista del modelo de Datos de contacto.
     * @return mixed
     */
    public function actionDatoscontacto()
    {
        $personaDireccion = new \app\models\Personadireccion(); //Instanciamos una variable
        $persona= new \app\models\Persona(); //Instanciamos una variable
        return $this->render('datoscontacto', [
            'personaDireccion' => $personaDireccion,
            'persona' => $persona,
        ]);
    }
    /**
     * Lista del modelo de Datos Medicos.
     * @return mixed
     */
    public function actionDatosmedicos()
    {
        $model = new \app\models\Fichamedica(); //Instanciamos una variable
        return $this->render('datosmedicos', [
            'model' => $model,
        ]);
    }
    /**
     * Lista del modelo de Datos Personales.
     * @return mixed
     */
    public function actionDatospersonales()
    {
        $model = new \app\models\Persona(); //Instanciamos una variable
        $model1 = new \app\models\Usuario(); //Instanciamos una variable
        return $this->render('datospersonales', [
            'model' => $model,
            'model1' => $model1,
        ]);
    }
    /**
     * Busco la carrera donde está inscripto el equipo del DNI ingresado
     * @return array
     */
    public function actionTipocarrera()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $idEquipo = $parents[0]; //Obtenemos el ID del equipo
                // Con ese ID, buscamos el id de la carrera a la que está inscripto el equipo
                $equipo= ArrayHelper::map(\app\models\Equipo::find()->where(['idEquipo' => $idEquipo])->all(),'idEquipo','idTipoCarrera');
                $idTipoCarrera = $equipo[$idEquipo]; //Obtenemos el ID del tipo de la carrera
                // A través de este ID, obtenemos la descripción de la carrera.
                $carrera= ArrayHelper::map(\app\models\Tipocarrera::find()->where(['idTipoCarrera' => $idTipoCarrera])->all(),'idTipoCarrera','descripcionCarrera');
                $out = [
                    ['id' => $idTipoCarrera, 'name' => $carrera[$idTipoCarrera]]
                ];
                return ['output'=>$out, 'selected'=>$idTipoCarrera];
            }
        }
        return ['output'=>'', 'selected'=>''];
    }
    /**
     * Busco la cantidad de personas que pueden ingresar al equipo del DNI ingresado
     * @return array
     */
    public function actionCantpersonas()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $idEquipo = $parents[0]; //Obtenemos el ID del equipo
                // Con este ID, buscamos cuantas personas pueden ingresar en ese equipo
                $elEquipo= ArrayHelper::map(\app\models\Equipo::find()->where(['idEquipo' => $idEquipo])->all(),'idEquipo','cantidadPersonas');
                $out = [
                    ['id' => $idEquipo, 'name' => $elEquipo[$idEquipo]]
                ];
                return ['output'=>$out, 'selected'=>$idEquipo];
            }
        }
        return ['output'=>'', 'selected'=>''];
    }
    /**
     * Mostramos el nombre y apellido del capitan del equipo creado por el DNI ingresado
     * @return array
     */
    public function actionNombrecapitan()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $idEquipo = $parents[0]; //Obtenemos el ID del equipo
                // Buscamos el equipo a través del DNI ingresado
                $objEquipo = Equipo::find()->where(['idEquipo'=>$idEquipo])->one();
                $dniCapitan=$objEquipo['dniCapitan'];  //Obtenemos el DNI del capitan del equipo
                // A través del DNI del capitan, buscamos su objeto Usuario
                $objUsuario = Usuario::find()->where(['dniUsuario'=>$dniCapitan])->one();
                $idUsu = $objUsuario['idUsuario']; //Obtenemos el ID del usuario
                // Con el ID del usuario, obtenemos el objeto Persona, para así obtener su nombre y apellido
                $objPersona = Persona::find()->where(['idUsuario'=>$idUsu])->one();
                $nombrePersona = $objPersona['nombrePersona'];
                $apellidoPersona = $objPersona['apellidoPersona'];
                $nombreCompleto = $nombrePersona . " " . $apellidoPersona; // Concatenamos su nombre y apellido
                $out = [
                    ['id' => $idUsu, 'name' => $nombreCompleto]
                ];
                return ['output'=>$out, 'selected'=>$idUsu];
            }
        }
        return ['output'=>'', 'selected'=>''];
    }
    public function actionLocalidades() {
        $persona=Persona::findOne(['idUsuario' => $_SESSION['__id']]);
        $personaDire=$persona->personaDireccion;
        $idLocalidad=$personaDire->idLocalidad;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = [];
        if (isset($_POST['depdrop_parents'])) {
            $parents = $_POST['depdrop_parents'];
            if ($parents != null) {
                $idProvincia = $parents[0];
                // the getSubCatList function will query the database based on the
                // cat_id and return an array like below:
                //$out = [
                //    ['id'=>'1', 'name'=>$idProvincia],
                //    ['id'=>'2', 'name'=>'<sub-cat-name2>']
                //];
                $locali = new \app\models\Localidad();
                $out = $locali::getLocalidades($idProvincia);
                return ['output'=>$out, 'selected'=>$idLocalidad];
            }
        }
        return ['output'=>'', 'selected'=>''];
    }

    public function actionEmail(){
        //obtengo persona autenticada
        $persona=Persona::findOne(['idUsuario'=>$_SESSION['__id']]);
        $existe=1;
        //recibo el email de la peticion ajax
        $emailUsu=Yii::$app->request->get()['emailUsu'];
        //busco un usuario que coincida con el email
        $usu=Usuario::findOne(['mailUsuario'=>$emailUsu]);
        //compruebo que el email del usuario autenticado sea el mismo que el enviado
        //con esto verifico que la persona no quiere actualziar su email
        if($persona->mismoUsuarioEmail($emailUsu)){
            $existe=0;
            return $existe;
        }
        //si no encontro un usuario con un email similar significa que se puede utilizar
       if($usu==null){
           $existe=0;

       }
       return $existe;

    }
}