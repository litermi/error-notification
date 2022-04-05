<?php

namespace Cirelramos\ErrorNotification\Services;

use Cirelramos\Logs\Services\SendLogConsoleService;
use Exception;
use Illuminate\Http\Response;

/**
 *
 */
class CatchNotificationService
{
    /**
     * @param array $context
     * @return void
     */
    public static function error(array $context = []): void
    {
        /** @var Exception $exception */
        $exception = $context[ 'exception' ];
        $sendLogConsoleService = new SendLogConsoleService();
        $sendLogConsoleService->execute('critical-errors:' . $exception->getMessage());
        if ($exception->getCode() !== Response::HTTP_UNPROCESSABLE_ENTITY) {
            SendEmailNotificationService::execute($exception);
            SendSlackNotificationService::execute($exception);
        }
    }
}
