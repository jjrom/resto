<?php
/*
 * Copyright 2018 Jérôme Gasperi
 *
 * Licensed under the Apache License, version 2.0 (the "License");
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at:
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

require_once(realpath(dirname(__FILE__)) . '/../../vendor/PHPMailer/src/Exception.php');
require_once(realpath(dirname(__FILE__)) . '/../../vendor/PHPMailer/src/PHPMailer.php');
require_once(realpath(dirname(__FILE__)) . '/../../vendor/PHPMailer/src/SMTP.php');

/**
 * resto mail functions
 */
class RestoNotifier
{
    
    /*
     * Services information
     */
    public $serviceInfos = array(
        'activateUser' => array(
            'endpoint' => 'http://localhost/rocket/#/user/activate',
            'message' => array(
                'title' => '[resto] Activation code',
                'body' => 'Hi,<br>You have registered an account to resto application<br><br>To validate this account, <a href="{:url:}">click this link</a> <br><br>If it does not work, you can also copy the link below and paste it within the address bar of your Web browser<br><br>{:url:}<br><br>Regards<br><br>resto team'
                
            )
        ),
        'resetPassword' => array(
            'endPoint' => 'http://localhost/rocket/#/resetPassword',
            'message' => array(
                'title' => '[resto] Reset password',
                'body' => 'Hi,<br><br>You ask to reset your password for the resto application<br><br>To reset your password, <a href="{:url:}">click this link</a> <br><br>If it does not work, you can also copy the link below and paste it within the address bar of your Web browser<br><br>{:url:}<br><br>Regards<br><br>resto team'
            )
        )
    );

    /**
     * Constructor
     *
     * @throws Exception
     */
    public function __construct($serviceInfos = array())
    {
        foreach (array_keys($serviceInfos) as $key) {
            $this->serviceInfos[$key] = $serviceInfos[$key];
        }
    }

    /**
     * Send user activation email
     *
     * @param string $receiver
     * @param array $mailConfig
     * @param array $options
     */
    public function sendMailForUserActivation($receiver, $mailConfig, $options)
    {
        return $this->sendMailForService('activateUser', $receiver, $mailConfig, $options);
    }
    
    /**
     * Send reset password link email
     *
     * @param string $receiver
     * @param array $mailConfig
     * @param array $options
     */
    public function sendMailForResetPassword($receiver, $mailConfig, $options)
    {
        return $this->sendMailForService('resetPassword', $receiver, $mailConfig, $options);
    }

    /**
     * Send mail
     *
     * @param array $params
     * @param array $smtp
     */
    public function sendMail($params, $smtp = array())
    {
        if (! isset($params) || ! is_array($params)) {
            return false;
        }

        foreach (array('senderEmail', 'senderName', 'to', 'subject', 'message') as $key) {
            if (! isset($params[$key])) {
                error_log('[ERROR] Cannot send email - missing mandatory ' . $key);
                return false;
            }
        }
        
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            if (isset($smtp) && $smtp['activate']) {
                if (isset($smtp['debug']) && $smtp['debug'] > 0) {
                    $mail->SMTPDebug  = $smtp['debug'];               // Debug mode
                }
                $mail->isSMTP();                                      // Set mailer to use SMTP
                $mail->Host = $smtp['host'];                          // Specify main and backup SMTP servers
                if (isset($smtp['secure'])) {
                    $mail->SMTPSecure = $smtp['secure'];              // Enable TLS encryption, `ssl` also accepted
                }
                $mail->Port = $smtp['port'];                          // TCP port to connect to
                if (isset($smtp['auth'])) {
                    $mail->SMTPAuth = true;                           // Enable SMTP authentication
                    $mail->Username = $smtp['auth']['user'];          // SMTP username
                    $mail->Password = $smtp['auth']['password'];      // SMTP password
                }
            }

            $mail->setFrom($params['senderEmail'], $params['senderName']);
            $mail->addAddress($params['to']);
            $mail->isHTML(true);
            $mail->Subject = $params['subject'];
            $mail->Body = $params['message'];
            $mail->Send();
        } catch (PHPMailer\PHPMailer\Exception $e) {
            error_log($e->errorMessage());
            return false;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
        
        return true;
    }
 
    /**
     * Send preformated mail using mailConfig
     *
     * @param string $serviceName
     * @param string $receiver
     * @param array $mailConfig
     * @param array $options
     */
    private function sendMailForService($serviceName, $receiver, $mailConfig, $options)
    {
        return $this->sendMail(
            array(
                'to' => $receiver,
                'senderName' => $mailConfig['senderName'],
                'senderEmail' => $mailConfig['senderEmail'],
                'subject' => $this->serviceInfos[$serviceName]['message']['title'],
                'message' => RestoUtil::replaceInTemplate($this->serviceInfos[$serviceName]['message']['body'], array(
                    'url' => $this->serviceInfos[$serviceName]['endpoint'] . '/' . $options['token']
                    )
                )
            ),
            $mailConfig['smtp']
        );
    }
}
