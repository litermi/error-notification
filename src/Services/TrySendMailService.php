<?php

namespace Cirelramos\ErrorNotification\Services;

use Cirelramos\ErrorNotification\Notifications\ExceptionEmailNotification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;

/**
 *
 */
class TrySendMailService
{
    /**
     * @param $infoEndpoint
     * @return void
     * @throws \Exception
     */
    public static function execute($infoEndpoint): void
    {
        if (config('app.debug') === false) {
            Config::set('mail.from.address', "example@mail.com");
            Config::set('mail.from.name', ' ERROR NOTIFICATION ' . strtoupper(config('app.env')));
        }
    
        if (config('app.debug') === true) {
            Config::set('mail.from.name', 'ERROR NOTIFICATION ' . strtoupper(config('app.env')));
        }
        retry(
            5,
            static function () use ($infoEndpoint) {
                $users = config('error-notification.mail-recipient');
                
                $data               = [];
                $data[ 'ip' ]       = $infoEndpoint[ 'from' ] ?? null;
                $data[ 'endpoint' ] = $infoEndpoint;
                $data[ 'alert' ]    = '';
                
                Notification::route('mail', $users)
                    ->notify(new ExceptionEmailNotification ([ 'mail' ], $data));
                
            },
            100
        );
    }
}