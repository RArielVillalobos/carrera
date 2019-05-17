<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sexo".
 *
 * @property int $idSexo
 * @property string $descripcionSexo
 *
 * @property Persona[] $personas
 */
class Sexo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sexo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['descripcionSexo'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'idSexo' => 'Id Sexo',
            'descripcionSexo' => 'Descripcion Sexo',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPersonas()
    {
        return $this->hasMany(Persona::className(), ['idSexoPersona' => 'idSexo']);
    }
}
