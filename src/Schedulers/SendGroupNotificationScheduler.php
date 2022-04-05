<?php

namespace Cirelramos\ErrorNotification\Schedulers;

use Cirelramos\ErrorNotification\Jobs\SendGroupNotificationJob;
use Cirelramos\ErrorNotification\Services\GetInfoFromExceptionService;
use Cirelramos\Logs\Services\SendLogConsoleService;
use Exception;
use Illuminate\Console\Command;

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
            $job = new SendGroupNotificationJob();
            dispatch($job)->onQueue(config('queue-names.general'));
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
