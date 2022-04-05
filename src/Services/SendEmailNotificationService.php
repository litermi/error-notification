<?php

namespace Cirelramos\ErrorNotification\Services;

use Cirelramos\Logs\Services\SendLogConsoleService;
use Exception;

/**
 *
 */
class SendEmailNotificationService
{
    /**
     * @param bool $directNotification
     * @return false
     */
    public static function execute(
        $exception,
        bool $directNotification = false
    ): bool {
        $infoEndpoint = GetInfoFromExceptionService::execute($exception);
        $infoEndpoint['send_mail'] = true;
        if ($directNotification === false) {
            GroupNotificationService::execute($infoEndpoint);
            return false;
        }
        
        try {
            TrySendMailService::execute($infoEndpoint);
        }
        catch(Exception $exception) {
            $infoEndpoint          = GetInfoFromExceptionService::execute($exception);
            $sendLogConsoleService = new SendLogConsoleService();
            $sendLogConsoleService->execute('error:' . $exception->getMessage(), $infoEndpoint);
        }
        
        return false;
    }
}