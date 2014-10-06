<?php

/**
 * @author Alex Phillips <exonintrendo@gmail.com>
 * Date: 8/26/14
 * Time: 8:38 PM
 */

return array(
    /**
     * Configuration for: Email server credentials
     *
     * Here you can define how you want to send emails.
     * If you have successfully set up a mail server on your linux server and you know
     * what you do, then you can skip this section. Otherwise please set EMAIL_USE_SMTP to true
     * and fill in your SMTP provider account data.
     *
     */
    'use_smtp'        => false,
    'smtp_host'       => 'smtp.gmail.com',
    'smtp_auth'       => true,
    'smtp_username'   => 'email@example.com',
    'smtp_password'   => 'password',
    'smtp_encryption' => 'tls',
);
