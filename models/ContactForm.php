<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class ContactForm extends Model
{
    public $email;
    public $subject;
    public $body;
    
    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            //  email, subject and body are required
            [['email', 'subject', 'body'], 'required','message'=>'Campos requeridos'],
            // email has to be a valid email address
            ['email', 'email', 'message' => 'Formato no válido'],
            
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'email' => 'Tu email',
            'subject' => 'Asunto',
            'body' => 'Contenido',
        ];
    }

    /**
     * Sends an email to the specified email address using the information collected by this model.
     * @param string $email the target email address
     * @return bool whether the model passes validation
     */
    public function contact($email)
    {
        if ($this->validate()) {
            Yii::$app->mailer->compose()
                ->setTo($email)
                ->setFrom([Yii::$app->params['adminEmail'] => Yii::$app->params['title']])
                ->setReplyTo($this->email)
                ->setSubject($this->subject)
                ->setTextBody($this->body)
                ->send();

            return true;
        }
        return false;
    }
}