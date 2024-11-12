<?php

namespace Litermi\ErrorNotification\Services;

use Exception;
use Illuminate\Support\Facades\Notification;
use Litermi\ErrorNotification\Notifications\ErrorSlackNotification;
use Litermi\Logs\Services\SendLogConsoleService;

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

        if (is_array($infoEndpoint) && array_key_exists('tracker', $infoEndpoint) == true) {
            $infoEndpoint['tracker'] = self::printArrayRecursive($infoEndpoint['tracker']);
        }

        $channelSlack                  = $channelSlack ?? config('error-notification.default-channel-slack');
        $infoEndpoint['send_slack']    = true;
        $infoEndpoint['channel_slack'] = $channelSlack;
        if ($directNotification === false) {
            if (config('error-notification.direct-notification', false) === false) {
                GroupNotificationService::execute($infoEndpoint, "slack");
                return false;
            }
        }
        try {
            Notification::route('slack', $channelSlack)
                ->notify(new ErrorSlackNotification($infoEndpoint));
        } catch (Exception $exception) {
            $infoEndpoint          = GetInfoFromExceptionService::execute($exception);
            $sendLogConsoleService = new SendLogConsoleService();
            $sendLogConsoleService->execute(
                'error:' . $exception->getMessage(),
                $infoEndpoint
            );
        }

        return false;
    }

    public static function printArrayRecursive($data, $indent = 0)
    {
        $stringToReturn = "";
        if (is_array($data) == false) {
            return $data;
        }

        foreach ($data as $key => $value) {
            // Indent the output for readability
            $indentStr = "" . str_repeat('  ', $indent);

            // If the value is an array, recurse
            if (is_array($value)) {
                $textKey = "$indentStr$key:";
                if (is_int($key) == true) {
                    $textKey = "";
                }
                if ($indent == 0) {
                    $stringToReturn .= "\n\n\n";
                }
                if ($indent == 1) {
                    $stringToReturn .= "\n";
                }

                if ($indent == 0) {
                    $stringToReturn .= "$textKey";
                } else {
                    $stringToReturn .= "$textKey";
                }
                $stringToReturn .= self::printArrayRecursive($value, $indent + 1);
            } else {
                // Otherwise, print the key-value pair
                $stringToReturn .= "$indentStr$key: $value \n";
            }
        }

        return $stringToReturn;
    }

}
