<?php

namespace Cirelramos\ErrorNotification\Services;

use Cirelramos\Logs\Services\SendLogConsoleService;
use Exception;
use Illuminate\Support\Facades\Cache;

/**
 *
 */
class GroupNotificationService
{
    /**
     * @param $infoEndpoint
     * @return void
     */
    public static function execute($infoEndpoint, $type): void
    {
        try {

            $nameException = $infoEndpoint[ 'name_exception' ];
            self::saveListExceptions($nameException);
            $nameCache     = env('APP_NAME');
            $nameCache     .= "_" . config('error-notification.cache-name');
            $nameCache     .= "_" . $nameException;
            $nameCache     .= "_" . $type;
            $tag           = config('error-notification.cache-tag-name');
            $dataFromCache = Cache::tags($tag)->get($nameCache);
            if ($dataFromCache !== null) {
                $messageException          = $infoEndpoint[ 'message_error' ] ?? null;
                $messageExceptionFromCache = $dataFromCache[ 'message_error' ] ?? null;
                if ($messageException && $messageExceptionFromCache && $messageException ===
                    $messageExceptionFromCache) {
                    $countErrors                    = $dataFromCache[ 'count_errors' ] ?? null;
                    $countErrors                    = $countErrors ? $countErrors : 0;
                    $infoEndpoint[ 'count_errors' ] = $countErrors + 1;
                }
            }

            $time = config('error-notification.cache-tag-time');
            Cache::tags($tag)->put($nameCache, $infoEndpoint, $time);
        }
        catch(Exception $exception) {
            $sendLogConsoleService = new SendLogConsoleService();
            $sendLogConsoleService->execute( 'error:' . $exception->getMessage(), $infoEndpoint );
        }
    }

    /**
     * @param $nameException
     * @return void
     */
    private static function saveListExceptions($nameException): void
    {
        $nameCache     = "list_exception_" . config('error-notification.cache-name');
        $tag           = "list_exception_" .config('error-notification.cache-tag-name');
        $array = [];
        $dataFromCache = Cache::tags($tag)->get($nameCache);
        if($dataFromCache === null){
            $array[] = $nameException;
        }
        if($dataFromCache !== null){
            $array = $dataFromCache;
        }

        $check = in_array($nameException, $array, true);
        if($check === false){
            $array[] = $nameException;
        }

        $time = config('error-notification.cache-tag-time');
        Cache::tags($tag)->put($nameCache, $array, $time);
    }
}
