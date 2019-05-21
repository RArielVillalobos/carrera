<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "personadireccion".
 *
 * @property int $idPersonaDireccion
 * @property int $idLocalidad
 * @property string $direccionUsuario
 *
 * @property Persona[] $personas
 * @property Localidad $localidad
 */
class Personadireccion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'personadireccion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idLocalidad'], 'integer'],
            [['direccionUsuario'], 'string', 'max' => 64],
            [['idLocalidad'], 'exist', 'skipOnError' => true, 'targetClass' => Localidad::className(), 'targetAttribute' => ['idLocalidad' => 'idLocalidad']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'idPersonaDireccion' => 'Id Persona Direccion',
            'idLocalidad' => 'Id Localidad',
            'direccionUsuario' => 'Direccion ',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPersonas()
    {
        return $this->hasMany(Persona::className(), ['idPersonaDireccion' => 'idPersonaDireccion']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLocalidad()
    {
        return $this->hasOne(Localidad::className(), ['idLocalidad' => 'idLocalidad']);
    }
}
