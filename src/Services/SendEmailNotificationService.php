<?php

namespace Litermi\ErrorNotification\Services;

use Exception;
use Litermi\Logs\Services\SendLogConsoleService;

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
        bool $directNotification = false,
        $isArrayException = false
    ): bool {
        if ($isArrayException == false) {
            $infoEndpoint = GetInfoFromExceptionService::execute($exception);
        }
        if ($isArrayException == true) {
            $infoEndpoint = $exception;
        }
        $infoEndpoint['send_mail'] = true;
        if (is_array($infoEndpoint) && array_key_exists('tracker', $infoEndpoint) == true) {
            $infoEndpoint['tracker'] = "<br>" . self::printArrayRecursive($infoEndpoint['tracker']);
        }
        if ($directNotification === false) {
            if (config('error-notification.direct-notification', false) === false) {
                GroupNotificationService::execute($infoEndpoint, "email");
                return false;
            }
        }

        try {
            TrySendMailService::execute($infoEndpoint);
        } catch (Exception $exception) {
            $infoEndpoint          = GetInfoFromExceptionService::execute($exception);
            $sendLogConsoleService = new SendLogConsoleService();
            $sendLogConsoleService->execute('error:' . $exception->getMessage(), $infoEndpoint);
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
                    $stringToReturn .= "<b>$textKey</b>";
                } else {
                    $stringToReturn .= "$textKey";
                }
                $stringToReturn .= self::printArrayRecursive($value, $indent + 1);
            } else {
                // Otherwise, print the key-value pair
                $stringToReturn .= "$indentStr$key: $value <br>";
            }
        }

        return $stringToReturn;
    }

}
