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
use app\models\Tipocarrera;
use app\models\Parametros;
use app\models\Carrerapersona;
use app\models\Listadeespera;
use app\models\RegistroForm;
use app\models\Permiso;
use yii\helper\Json;
use yii\base\Security;

class InvitadoController extends Controller
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
     * Lista de todos los modelos de inscripcion
     * @return mixed
     */
    public function actionIndex()
    {
        if(Permiso::requerirRol('administrador')){
            $this->layout='/main2';
        }elseif(Permiso::requerirRol('gestor')){
            $this->layout='/main3';
        }
        //se instancia una variable por cada modelo a utilizar
        $persona = new \app\models\Persona(); //Instanciamos una variable
        $usuario = new \app\models\Usuario(); //Instanciamos una variable
        $personaDireccion = new \app\models\Personadireccion(); //Instanciamos una variable
        $fichaMedica = new \app\models\Fichamedica(); //Instanciamos una variable
        $datosEmergencia = new \app\models\Personaemergencia();//Instanciamos una variable
        $localidad = new \app\models\Localidad(); //Instanciamos una variable
        $provincia = new \app\models\Provincia(); //Instanciamos una variable
        $equipo = new \app\models\Equipo(); //Instanciamos una variable
        $talleRemera=new Talleremera();
        $provinciaLista = ArrayHelper::map(\app\models\Provincia::find()->all(),'idProvincia','nombreProvincia'); //Lista de las provincias
        $listadoTalles=ArrayHelper::map(\app\models\Talleremera::find()->all(),'idTalleRemera','talleRemera');
        $respuesta=new \app\models\Respuesta();
        $tipoCarrera = new \app\models\Tipocarrera(); //Instanciamos una variable
        $tipocarreraLista =ArrayHelper::map(\app\models\Tipocarrera::find()->all(),'idTipoCarrera','descripcionCarrera');
        $cantCorredores =ArrayHelper::map(\app\models\Parametros::find()->all(),'idParametros','cantidadCorredores');
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

        return $this->render('index',[
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
            'swicht'=>null,
            'datos' => null,
            'respuesta'=>$respuesta,
            'user'=>$userLogueado,
            'carrerapersona'=>$carrerapersona
            ]);
    }


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


    /**
     * Guarda los datos del formulario en sus correspondientes tablas de la base de datos
     */
    public function actionStore(){
        $guardado=false; //Asignamos false a la variable guardado
        $transaction = Persona::getDb()->beginTransaction(); // Iniciamos una transaccion
        $userLogueado=Yii::$app->user;  // Obtenemos el objeto del usuario logeado

        try {

            //MODELO USUARIO
            // Obtenemos los datos recibidos por POST, necesarios para crear la tupla

            $modeloUsuario=Yii::$app->request->post()['Usuario'];
            $dniUsuario = $modeloUsuario['dniUsuario'];
            $idRol = $userLogueado->identity->idRol; // Obtenemos el ID rol del usuario logeado

            $modeloPersona=Yii::$app->request->post()['Persona'];
            $mailUsuario = $modeloPersona['mailPersona'];
            $usuario = new Usuario(); // Instanciamos una variable de la clase Usuario y le asignamos los valores
            $usuario->dniUsuario = $dniUsuario;
            $usuario->mailUsuario = $mailUsuario;
            $usuario->activado = 1; // Activado es true, debido a que no hará validación por mail el corredor
            $usuario->idRol = 4; //ID del rol del invitado

            $security = new Security();
            //A traves de la funcion generateRandomString, generamos un string aleatorio para completar el campo Authkey
            $authkey = $security->generateRandomString(50);
            $usuario->authkey = $authkey; //clave será utilizada para activar el usuario
            //Encripstamos la clave
            $usuario->claveUsuario = crypt($dniUsuario,Yii::$app->params["salt"]);
            // Buscamos si existe un usuario con este DNI
            $objUsuario = Usuario::find()->where(['dniUsuario'=>$dniUsuario])->One();
            if ($objUsuario == null) { // Es decir, no existe el usuario con ese DNI en la BD
                $existeUsuario = false;

            } else {
                $existeUsuario = true;
                echo "ja";
            }

            if ($usuario->validate() && !($existeUsuario)) {

                // toda la entrada es válida y no existe un usuario con ese DNI
                $usuario->save(); //Realiza el llenado de la tabla
                $idUsuario = Yii::$app->db->getLastInsertID(); //Obtenemos el ID del ultimo usuario ingresado

            } else {
                // la validación falló: $erroresPersonaDireccion es un array que contienen los mensajes de error
                $usuario = $usuario->errors;

            }

            //MODELO LOCALIDAD
            $modeloLocalidad=Yii::$app->request->post()['Localidad'];

            //MODELO PERSONA DIRECCION
            // Concatenamos todos los campos relacionados con la Direccion de la persona
            $direccion=Yii::$app->request->post()['calle'].' '.Yii::$app->request->post()['numero'].' '.Yii::$app->request->post()['piso'].' '.Yii::$app->request->post()['departamento'];
            $personaDireccion=new Personadireccion(); // Instanciamos una variable de la clase Persona Direccion
            // y le asignamos los valores

            $personaDireccion->idLocalidad=$modeloLocalidad['idLocalidad'];
            $personaDireccion->direccionUsuario=$direccion;
            if ($personaDireccion->validate()) {
                // toda la entrada es válida
                $personaDireccion->save(); //Realiza el llenado de la tabla
            } else {
                // la validación falló: $erroresPersonaDireccion es un array que contienen los mensajes de error
                $erroresPersonaDireccion = $personaDireccion->errors;
            }

            //MODELO FICHA MEDICA
            $modeloFichaMedica=Yii::$app->request->post()['Fichamedica'];
            $fichaMedica=new Fichamedica(); //Instanciamos una variable de la clase Ficha Medica
            // Asignamos los valores
            $fichaMedica->obraSocial=$modeloFichaMedica['obraSocial'];
            $fichaMedica->peso=$modeloFichaMedica['peso'];
            $fichaMedica->altura=$modeloFichaMedica['altura'];
            $fichaMedica->frecuenciaCardiaca=$modeloFichaMedica['frecuenciaCardiaca'];
            $fichaMedica->idGrupoSanguineo=$modeloFichaMedica['idGrupoSanguineo'];
            $fichaMedica->evaluacionMedica=$modeloFichaMedica['evaluacionMedica'];
            $fichaMedica->intervencionQuirurgica=$modeloFichaMedica['intervencionQuirurgica'];
            $fichaMedica->tomaMedicamentos=$modeloFichaMedica['tomaMedicamentos'];
            $fichaMedica->suplementos=$modeloFichaMedica['suplementos'];
            $fichaMedica->observaciones=$modeloFichaMedica['observaciones'];
            if ($fichaMedica->validate()) {
                // toda la entrada es válida
                $fichaMedica->save(); // Realiza el llenado de la tabla
            } else {
                // la validación falló: $erroresFichaMedica es un array que contienen los mensajes de error
                $erroresFichaMedica = $fichaMedica->errors;
            }


            //MODELO PERSONAEMERGENCIA
            $modeloPersonaemergencia=Yii::$app->request->post()['Personaemergencia'];
            $personaEmergencia=new Personaemergencia(); // Instanciamos una variable de la clase Persona Emergencia
            // y le asignamos los valores

            $personaEmergencia->nombrePersonaEmergencia=$modeloPersonaemergencia['nombrePersonaEmergencia'];
            $personaEmergencia->apellidoPersonaEmergencia=$modeloPersonaemergencia['apellidoPersonaEmergencia'];
            $personaEmergencia->telefonoPersonaEmergencia=$modeloPersonaemergencia['telefonoPersonaEmergencia'];
            $personaEmergencia->idVinculoPersonaEmergencia=$modeloPersonaemergencia['idVinculoPersonaEmergencia'];
            if ($personaEmergencia->validate()) {
                // toda la entrada es válida
                $personaEmergencia->save(); // Realiza el llenado de la tabla
            } else {
                // la validación falló: $erroresPersonaEmergencia es un array que contienen los mensajes de error
                $erroresPersonaEmergencia = $personaEmergencia->errors;
            }

            //Obtenemos el ID del talle de remera seleccionado
            $idTalleRemera=Yii::$app->request->post()['Talleremera']['idTalleRemera'];


            //MODELO PERSONA
            $modeloPersona=Yii::$app->request->post()['Persona'];
            $persona=new Persona(); //Instanciamos una variable de la clase Persona
            // Asignamos los valores
            $persona->idTalleRemera=$idTalleRemera;
            $persona->nombrePersona=$modeloPersona['nombrePersona'];
            $persona->apellidoPersona=$modeloPersona['apellidoPersona'];
            $persona->fechaNacPersona=Yii::$app->request->post()['Persona']['fechaNacPersona'];
            $persona->sexoPersona=$modeloPersona['sexoPersona'];
            $persona->nacionalidadPersona=$modeloPersona['nacionalidadPersona'];
            $persona->telefonoPersona=$modeloPersona['telefonoPersona'];
            $persona->mailPersona=$modeloPersona['mailPersona'];
            $persona->idUsuario=$idUsuario;
            $persona->idPersonaDireccion=$personaDireccion->idPersonaDireccion;
            $persona->idFichaMedica=$fichaMedica->idFichaMedica;
            $persona->fechaInscPersona=null;
            $persona->idPersonaEmergencia=$personaEmergencia->idPersonaEmergencia;
            $persona->donador=$modeloPersona['donador'];
            $persona->deshabilitado=0;
            //$persona->estadoPago=null;
            if ($persona->validate()) {
                // toda la entrada es válida
                $persona->save(); //Realiza el llenado de la tabla
            } else {
                // la validación falló: $erroresPersonaEmergencia es un array que contienen los mensajes de error
                $erroresPersona = $persona->errors;
            }

            $idPersona=$persona->idPersona;  // Obtenemos el ID de la persona ingresada

            //MODELO EQUIPO
            if (!Yii::$app->request->post()['swichtCapitan']){
                //Ingresa acá si NO es capitan
                $modeloEquipo=Yii::$app->request->post()['Equipo']['idEquipo'];
                $grupo=new Grupo(); //Instanciamos una variable de la clase Grupo
                // Asignamos los valores
                $grupo->idEquipo=$modeloEquipo;
                $grupo->idPersona=$idPersona;
                $grupo->save(); // Realiza el llenado de la tabla

            }else{
                // Acá ingresa SI es capitan
                $grupo=new Grupo();
                $equipo=new Equipo();
                // Instanciamos una variable de las clases Grupo y Equipo
                // Asignamos los valores

                $cantidadPersonas=Yii::$app->request->post()['Equipo']['cantidadPersonas'];
                $idTipoCarrera=Yii::$app->request->post()['Tipocarrera']['idTipoCarrera'];
                $parametricaCantidadPersonas = ArrayHelper::map(\app\models\Parametros::find()->where(['idParametros' => $cantidadPersonas])->all(),'idParametros','cantidadCorredores');
                // Obtenemos la cantidad de personas que pueden ingresar al grupo
                $equipo->cantidadPersonas=$parametricaCantidadPersonas[$cantidadPersonas];
                // La carrera seleccionada
                $equipo->idTipoCarrera=$idTipoCarrera;

                $equipo->dniCapitan=Yii::$app->request->post()['Usuario']['dniUsuario'];
                $equipo->deshabilitado=0;
                //El DNI del capitan
                $equipo->save();  //Realiza el llenado de la tabla
                $idDbEquipo = Yii::$app->db->getLastInsertID(); //Obtenemos el ID del grupo ingresado
                $equipo->nombreEquipo=$idDbEquipo;//Le asignamos como nombre de equipo, el ID del grupo
                $equipo->update(); //Actualizamos la tupla
                $grupo->idEquipo=$idDbEquipo;
                $grupo->idPersona=$idPersona;
                $grupo->save(); //Realiza el llenado de la tabla

            }

            // Instanciamos una variable de la clase Carrera Persona

            $carreraPersona = new Carrerapersona();

            if (!Yii::$app->request->post()['swichtCapitan']){ //Si no es capitan, obtenemos el id del tipo carrera. Si lo es, ya tenemos el valor mas arriba
                $idEquipo=Yii::$app->request->post()['Equipo']['idEquipo'];
                $objEquipo = Equipo::find()->where(['idEquipo'=>$idEquipo])->one();
                $idTipoCarrera=$objEquipo['idTipoCarrera'];
            }

            // Asignamos los valores
            $carreraPersona->idPersona=$persona->idPersona;
            $carreraPersona->idTipoCarrera = $idTipoCarrera;
            $carreraPersona->reglamentoAceptado = 1; //Acepta el reglamento obligatoriamente
            $carreraPersona->retiraKit=0;
            $carreraPersona->save(); //Realiza el llenado de la tabla

            $objTipoCarrera = Tipocarrera::find()->where(['idTipoCarrera'=>$idTipoCarrera])->one(); //Obtenemos el obj Tipo carrera
            $cantidadMaximaCorredores = $objTipoCarrera->cantidadMaximaCorredores; // Obtenemos cantidad maxima de corredores de esa carrera

            $cantidadInscriptos=Carrerapersona::find() //Obtenemos la cantidad de personas habilitadas inscriptas en una carrera particular
            ->innerJoin('persona','carrerapersona.idPersona=persona.idPersona')
             ->where(['persona.deshabilitado'=>'<>1'])
            ->andWhere(['carrerapersona.idTipocarrera'=>$idTipoCarrera])
            ->count();
            // Este count siempre da +1, por el nuevo llenado de la tabla pero que no se confirma hasta que no se hace el commit

            $enListaDeEspera = false; // Por defecto, no está en lista de espera. Si lo está, abajo se setea en true
            if ($cantidadInscriptos>$cantidadMaximaCorredores){
                 $listaDeEspera = new Listadeespera();
                 $listaDeEspera->idPersona=$idPersona;
                 $listaDeEspera->save(); // Realiza el llenado de la tabla
                 $enListaDeEspera = true;
            }


            $transaction->commit();
            $guardado=true;
            if ($guardado){     // Si la inscripcion es guardada correctamente, se envia un mail de confirmacion

                // Obtenemos el Objeto usuario para obtener sus dato
                $objUsuario=Usuario::find()->where(['idUsuario'=>$idUsuario])->one();
                $objPersona=Persona::find()->where(['idUsuario'=>$idUsuario])->one();
                $nombrePersona = $objPersona->nombrePersona;
                $apellidoPersona = $objPersona->apellidoPersona;
                $mailUsuario = $objUsuario->mailUsuario;
                $host=Yii::$app->request->hostInfo;

                //mail de confirmacion de inscripcion
                $subject = "Inscripcion y reglamento"; // Asunto del mail
                // Cuerpo del mail
                $body.=
                    "<div style='width:100%; background:#eee; position:relative; font-family:sans-serif; padding-bottom:40px'>
                                <div class='col-lg-12 col-xs-6' style='position:relative; margin: auto; max-width: 500px; background:white; padding:20px'>

                                        <center>


                                        <img style='width: 40%' src='https://1.bp.blogspot.com/-Bwoc6FKprQ8/XRECC8jNE-I/AAAAAAAAAkQ/m_RHJ_t3w5ErKBtNPIWqhWrdeSy2pbD7wCLcBGAs/s320/logo-color.png'>

                                        <h2 style='font-weight:100; color:black'>DESAFIO POR LAS BARDAS</h2>

                                        <hr style='border:1px solid #ccc; width:90%'>

                                        <h3 style='font-weight:100; color:black; padding:0 20px'><strong>Gracias por inscribirse a la carrera ".$nombrePersona." ".$apellidoPersona." </strong></h3>";

                if ($enListaDeEspera){ // Si esta en lista de espera se cambia una parte del texto
                    $body.="<h3 style='font-weight:100; color:black; padding:0 20px'><strong>Como ya se han completado la cantidad de cupos dispuestos inicialmente, actualmente te encuentras en lista de espera</strong></h3>";
                }

                "<h4 style='font-weight:100; color:black; padding:0 20px'>Podes ver los terminos y condiciones que has aceptado en el siguiente enlace:</h4>

                                        <a href='$host/index.php?r=site%2Freglamento' style='text-decoration:none'>

                                        <div style='line-height:60px; background:#ff8f04; width:60%; color:white'>Reglamento</div>

                                        </a>

                                        <br>

                                        <hr style='border:1px solid #ccc; width:90%'>

                                        <img style='padding:20px; width:60%' src='https://1.bp.blogspot.com/-ZWmNFzB7l40/XURFF_nIAYI/AAAAAAAAAmc/-clxrSodbUkxSaTqVV-F0yECTvuwsBsdACLcBGAs/s320/placas%2B5-01.jpg'>

                                        <h5 style='font-weight:100; color:black'>Este mensaje de correo electrónico se envió a ".$mailUsuario."</h5>

                                        <h5 style='font-weight:100; color:black'>Te invitamos a que veas nuestras redes sociales.</h5>

                                        <a href='https://www.facebook.com/bienestaruncoma/'><img src='https://1.bp.blogspot.com/-BR60W75cIco/XREFTGbPHZI/AAAAAAAAAks/FQUMI8DkynoP69YnYRjGZ1ylnNeYhM5BwCLcBGAs/s320/facebook-logo.png' style='width: 7%'></a>
                                        <a href='https://www.instagram.com/sbucomahue/'><img src='https://1.bp.blogspot.com/-NKIBF9SSXCU/XREFTOvwjII/AAAAAAAAAkw/cn679IM4LMQvcIMVCsgetU7gTDyM5DhwgCLcBGAs/s320/instagram-logo.png' style='width: 7%'></a>

                                        </center>

                                </div>

                        </div>";

                Yii::$app->mailer->compose()
                    ->setFrom('carreraxbarda@gmail.com')
                    //->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->params['title']])
                    ->setTo($usuario->mailUsuario)
                    //->setTo('carreraxbarda@gmail.com')
                    ->setSubject($subject)
                    ->setHTMLBody($body)
                    ->send();


                $mensaje = "Enviamos un email con su registro de inscripcion ";
                if ($idRol == 3){ // Si es gestora, implica que va a inscribir a algun corredor que no pudo inscribirse y que no tiene Usuario.
                    return Yii::$app->response->redirect(['site/gestor','guardado'=>$guardado,'mensaje'=>$mensaje])->send();
                } elseif ($idRol==2){
                    return Yii::$app->response->redirect(['site/admin','guardado'=>$guardado,'mensaje'=>$mensaje])->send();
                } else {
                    return Yii::$app->response->redirect(['site/index','guardado'=>$guardado,'mensaje'=>$mensaje])->send();
                }

            }else{
                $mensaje = "Ha ocurrido un error al llevar a cabo tu inscripcion,vuelve a intentarlo";
                if ($idRol == 3) { // Si es gestora, implica que va a inscribir a algun corredor que no pudo inscribirse y que no tiene Usuario.
                    return Yii::$app->response->redirect(['site/gestor', 'guardado' => $guardado, 'mensaje' => $mensaje])->send();
                } elseif ($idRol == 2) {
                    return Yii::$app->response->redirect(['site/admin', 'guardado' => $guardado, 'mensaje' => $mensaje])->send();
                } else {
                    return Yii::$app->response->redirect(['site/index', 'guardado' => $guardado, 'mensaje' => $mensaje])->send();
                }
            }




        } catch(\Exception $e) {
            $guardado=false;

            $transaction->rollBack();
            throw $e;
        }


            //return Yii::$app->response->redirect(['site/index','guardado'=>$guardado])->send();



    }

}
