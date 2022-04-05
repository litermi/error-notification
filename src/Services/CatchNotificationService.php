<?php

namespace App\Core\Base\Services;

use Cirelramos\ErrorNotification\Services\SendEmailNotificationService;
use Cirelramos\ErrorNotification\Services\SendSlackNotificationService;
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
    public static function execute(array $context = []): void
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
