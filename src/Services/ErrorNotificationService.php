<?php

namespace Cirelramos\ErrorNotification\Services;

class ErrorNotificationService
{
    /**
     * @param             $exception
     * @param string|null $channelSlack
     * @param bool        $mail
     * @param bool        $directNotification
     */
    public function sendErrorNotification(
        $exception,
        $channelSlack = null,
        bool $mail = true,
        bool $directNotification = true
    ): void {
    
        SendEmailNotificationService::execute($exception, $directNotification);
        SendSlackNotificationService::execute($exception, $directNotification, $channelSlack);
    }
}
