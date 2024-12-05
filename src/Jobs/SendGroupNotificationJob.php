<?php

namespace Litermi\ErrorNotification\Jobs;

use Litermi\ErrorNotification\Notifications\ErrorSlackNotification;
use Litermi\ErrorNotification\Services\CatchNotificationService;
use Litermi\ErrorNotification\Services\SendEmailNotificationService;
use Litermi\ErrorNotification\Services\SendSlackNotificationService;
use Litermi\ErrorNotification\Services\TrySendMailService;
use Litermi\Logs\Services\SendLogConsoleService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

/**
 *
 */
class SendGroupNotificationJob implements ShouldQueue, ShouldBeUnique
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct()
    {
    }

    /**
     *
     */
    public function handle(): void
    {
        try {
            $array                 = [ 'message' => "start " . __CLASS__ . " " ];
            $sendLogConsoleService = new SendLogConsoleService();
            $sendLogConsoleService->execute(
                'job',
                $array
            );


            $nameCache     = "list_exception_" . config('error-notification.cache-name');
            $tag           = "list_exception_" . config('error-notification.cache-tag-name');
            $dataFromCache = Cache::tags($tag)->get($nameCache);
            if ($dataFromCache === null) {
                return;
            }

            $tagItem = config('error-notification.cache-tag-name');
            foreach ($dataFromCache as $nameException) {
                $nameCacheItem = env('APP_NAME');
                $nameCacheItem .= "_" . config('error-notification.cache-name');
                $nameCacheItem .= "_" . $nameException;

                $dataFromCacheItem = Cache::tags($tagItem)->get($nameCacheItem."_slack");
                if ($dataFromCacheItem === null) {
                    continue;
                }

                $sendSlack    = $dataFromCacheItem[ 'send_slack' ] ?? null;
                $channelSlack = $dataFromCacheItem[ 'channel_slack' ] ?? null;
                if ($sendSlack === true) {
                    SendSlackNotificationService::execute(
                                            $dataFromCacheItem,
                        directNotification: true,
                        channelSlack      : $channelSlack,
                        isArrayException  : true
                    );
                }

                $dataFromCacheItem = Cache::tags($tagItem)->get($nameCacheItem."_email");
                $sendMail = $dataFromCacheItem[ 'send_mail' ] ?? null;
                if ($sendMail === true) {
                    SendEmailNotificationService::execute(
                                            $dataFromCacheItem,
                        directNotification: true,
                        isArrayException  : true
                    );
                }
            }
            Cache::tags($tagItem)->flush();
            Cache::tags($tag)->flush();

            $array = [ 'message' => "finish " . __CLASS__ . " " ];
            $sendLogConsoleService->execute(
                'job',
                $array
            );
        }
        catch(Exception $exception) {
            $context = [
                'url'       => '',
                'exception' => $exception,
            ];
            CatchNotificationService::error($context);
        }
    }

    /**
     * The job failed to process.
     *
     * @param Exception $exception
     */
    public function failed($exception)
    {
        $context = [
            'url'       => '',
            'exception' => $exception,
        ];
        CatchNotificationService::error($context);
    }
}
