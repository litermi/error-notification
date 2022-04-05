<?php

namespace Cirelramos\ErrorNotification\Jobs;

use Cirelramos\ErrorNotification\Notifications\ErrorSlackNotification;
use Cirelramos\ErrorNotification\Services\CatchNotificationService;
use Cirelramos\ErrorNotification\Services\TrySendMailService;
use Cirelramos\Logs\Services\SendLogConsoleService;
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
            $dataFromCache = Cache::tags($tag)
                ->get($nameCache);
            if ($dataFromCache === null) {
                return;
            }
            foreach ($dataFromCache as $nameException) {
                $nameCache = env('APP_NAME');
                $nameCache .= "_" . config('error-notification.cache-name');
                $nameCache .= "_" . $nameException;
                $tag       = config('error-notification.cache-tag-name');

                $dataFromCache = Cache::tags($tag)->get($nameCache."_slack");
                if ($dataFromCache === null) {
                    continue;
                }

                Cache::tags($tag)->flush();

                $sendSlack    = $dataFromCache[ 'send_slack' ] ?? null;
                $channelSlack = $dataFromCache[ 'channel_slack' ] ?? null;
                if ($sendSlack === true) {
                    Notification::route('slack', $channelSlack)
                        ->notify(new ErrorSlackNotification($dataFromCache));
                }

                $dataFromCache = Cache::tags($tag)->get($nameCache."_email");
                $sendMail = $dataFromCache[ 'send_mail' ] ?? null;
                if ($sendMail === true) {
                    TrySendMailService::execute($dataFromCache);
                }
            }

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
