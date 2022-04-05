<?php

namespace Cirelramos\ErrorNotification\Schedulers;

use Cirelramos\ErrorNotification\Notifications\ErrorSlackNotification;
use Cirelramos\ErrorNotification\Services\GetInfoFromExceptionService;
use Cirelramos\ErrorNotification\Services\TrySendMailService;
use Cirelramos\Logs\Services\SendLogConsoleService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

/**
 *
 */
class SendGroupNotificationScheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'error-notification:send-group-notification';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process to send group notification';
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
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
                
                $dataFromCache = Cache::tags($tag)->get($nameCache);
                if ($dataFromCache === null) {
                    continue;
                }
                
                Cache::tags($tag)->flush();
                
                $sendSlack    = $dataFromCache[ 'send_slack' ];
                $channelSlack = $dataFromCache[ 'channel_slack' ];
                if ($sendSlack === true) {
                    Notification::route('slack', $channelSlack)
                        ->notify(new ErrorSlackNotification($dataFromCache));
                }
                
                $sendMail = $dataFromCache[ 'send_mail' ];
                if ($sendMail === true) {
                    TrySendMailService::execute($dataFromCache);
                }
            }
        }
        catch(Exception $exception) {
            $infoEndpoint          = GetInfoFromExceptionService::execute($exception);
            $sendLogConsoleService = new SendLogConsoleService();
            $sendLogConsoleService->execute(
                'error:' . $exception->getMessage(),
                $infoEndpoint
            );
        }
        
    }
}
