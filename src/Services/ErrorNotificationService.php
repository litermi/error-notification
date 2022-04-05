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

    }
}
