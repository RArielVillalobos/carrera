<?php

namespace app\models;

use Yii;
use app\models\Persona;

/**
 * This is the model class for table "carrerapersona".
 *
 * @property int $idTipoCarrera
 * @property int $idPersona
 * @property int $reglamentoAceptado
 * @property int $retiraKit
 *
 * @property Persona $persona
 * @property Tipocarrera $tipoCarrera
 */
class Carrerapersona extends \yii\db\ActiveRecord    
{
	public $apellidoPersona;
	public $nombrePersona;
	public $dniUsuario;
	public $talleRemera;
	public $nombreEquipo;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'carrerapersona';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idTipoCarrera', 'idPersona','reglamentoAceptado'], 'required'],
            ['reglamentoAceptado','compare','compareValue'=>1,'message'=>'Debe aceptar el reglamento para inscribirse a la carrera'],
            [['idTipoCarrera', 'idPersona', 'reglamentoAceptado', 'retiraKit'], 'integer'],
            [['idTipoCarrera', 'idPersona'], 'unique', 'targetAttribute' => ['idTipoCarrera', 'idPersona']],
            [['idPersona'], 'exist', 'skipOnError' => true, 'targetClass' => Persona::className(), 'targetAttribute' => ['idPersona' => 'idPersona']],
            [['idTipoCarrera'], 'exist', 'skipOnError' => true, 'targetClass' => Tipocarrera::className(), 'targetAttribute' => ['idTipoCarrera' => 'idTipoCarrera']],
            [['apellidoPersona','nombrePersona','talleRemera','nombreEquipo'], 'safe'],
			
	   ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'idTipoCarrera' => 'Tipo Carrera',
            'idPersona' => 'Id Persona',
            'reglamentoAceptado' => 'Reglamento Aceptado',
            'retiraKit' => 'Retira Kit',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPersona()
    {
        return $this->hasOne(Persona::className(), ['idPersona' => 'idPersona']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTipoCarrera()
    {
        return $this->hasOne(Tipocarrera::className(), ['idTipoCarrera' => 'idTipoCarrera']);
    }
	public function getEquipo()
    {
        return $this->hasOne(Equipo::className(), ['idTipoCarrera' => 'idTipoCarrera']);
    }
   
    public function getTalleRemera(){
	   return $this->hasOne(Persona::className(),['idTalleRemera'=> 'idTalleRemera'])->viaTable (TalleRemera::className(),['idTalleRemera'=>'idTalleRemera']);
    }
	
}
