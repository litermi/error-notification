<?php

namespace Litermi\ErrorNotification\Services;

use Litermi\ErrorNotification\Notifications\ErrorSlackNotification;
use Litermi\Logs\Services\SendLogConsoleService;
use Exception;
use Illuminate\Support\Facades\Notification;

/**
 *
 */
class SendSlackNotificationService
{
    /**
     * @param      $exception
     * @param bool $directNotification
     * @param null $channelSlack
     * @return bool|null
     */
    public static function execute(
        $exception,
        bool $directNotification = false,
        $channelSlack = null,
        $isArrayException = false
    ): bool {
        if ($isArrayException == false) {
            $infoEndpoint = GetInfoFromExceptionService::execute($exception);
        }
        if ($isArrayException == true) {
            $infoEndpoint = $exception;
        }
        $channelSlack                  = $channelSlack ?? config('error-notification.default-channel-slack');
        $infoEndpoint['send_slack']    = true;
        $infoEndpoint['channel_slack'] = $channelSlack;
        if ($directNotification === false) {
            GroupNotificationService::execute($infoEndpoint, "slack");
            return false;
        }
        try {
            var_dump("SEEEEND");
            Notification::route('slack', $channelSlack)
                ->notify(new ErrorSlackNotification($infoEndpoint));
        } catch (Exception $exception) {
            var_dump("SEEEEND 1.2");
            $infoEndpoint          = GetInfoFromExceptionService::execute($exception);
            $sendLogConsoleService = new SendLogConsoleService();
            $sendLogConsoleService->execute(
                'error:' . $exception->getMessage(),
                $infoEndpoint
            );
        }

        return false;
    }
}
