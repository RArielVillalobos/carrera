<?php
/**
 * Created by PhpStorm.
 * User: ariel
 * Date: 29/08/19
 * Time: 23:31
 */
namespace app\controllers;

use app\models\Equipo;
use app\models\Gestores;
use app\models\Grupo;
use app\models\Persona;
use app\models\Usuario;
use yii\web\Controller;

class EstadisticaController extends  Controller{

    public function actionNoinscriptos(){
        $this->layout = '/main2';
        $usuariosNoInscriptos=[];
        $usuariariosTodos=Usuario::find()->all();

        foreach ($usuariariosTodos as $usuario){
               if(Persona::findOne(['idUsuario'=>$usuario->idUsuario])==null){
                   if(Gestores::findOne(['idUsuario'=>$usuario->idUsuario])==null){
                       $usuariosNoInscriptos[]=$usuario;
                   }

               }
        }

        return $this->render('index',['usuariosNoInscriptos'=>$usuariosNoInscriptos]);
    }

    public function actionGenerales(){
        $this->layout = '/main2';
        $equipos=Equipo::findAll(['deshabilitado'=>0]);
        $equiposIncompletos=0;
        $personasFaltanInscribirse=0;
        $equiposOcupandoCuposSinPagar=[];
        $dniCapitanes=[];
        foreach ($equipos as $equipo){
            //cuenta las personas que hay cada  equipo
            $totalParticipantesEquipo=Grupo::findAll(['idEquipo'=>$equipo->idEquipo]);
            $totalParticipantesEquipoNum=count($totalParticipantesEquipo);
            //si la cantidad de personas en el equipo es diferente a la que dice el equipo que tendra
            if($equipo->cantidadPersonas!=$totalParticipantesEquipoNum){
                $dniCapitanes[]=$equipo->dniCapitan;

                //cuenta cuantas personas le faltan al equipo y luego las suma en la variable
                $personasFaltanInscribirse=$personasFaltanInscribirse+($equipo->cantidadPersonas-$totalParticipantesEquipoNum);
                //suma la cantidad de equipos incompletos
                $equiposIncompletos++;

            }

            //si el equipo no pago y esta ocupando cupo
            if($equipo->pagoInscripcion()==false && $equipo->capEquipoEnListaEspera()==false){
                $equiposOcupandoCuposSinPagar[]=$equipo;
            }

        }
        return $this->render('generales',['equipos'=>$equipos,'equiposIncompletos'=>$equiposIncompletos,'personasFaltanInscribirse'=>$personasFaltanInscribirse,'dniCapitanes'=>$dniCapitanes,'equiposOcupandoCuposSinPagar'=>$equiposOcupandoCuposSinPagar]);
    }
}