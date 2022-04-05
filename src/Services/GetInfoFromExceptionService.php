<?php

namespace Cirelramos\ErrorNotification\Services;

use Cirelramos\ErrorNotification\Services\GetAllValuesFromHeaderService;
use Illuminate\Support\Facades\Auth;

/**
 *
 */
class GetInfoFromExceptionService
{
    /**
     * @param       $exception
     * @param array $extraValues
     * @return array
     */
    public static function execute($exception, $extraValues = []): array
    {
        $request      = request();
        $infoEndpoint = [
            'message_error' => $exception->getMessage(),
            'uri'           => $request->getRequestUri(),
            'method'        => $request->method(),
            'user'          => Auth::id(),
        ];
    
        foreach (config('error-notification.get_special_values_from_header') as $key => $item) {
            if ($request->$item) {
                $infoEndpoint[ $key ] = $request->header($item);
            }
        }
        foreach (config('error-notification.get_special_values_from_request') as $key => $item) {
            if ($request->$item) {
                $infoEndpoint[ $key ] = $request->$item;
            }
        }
        
        $infoEndpoint[ 'environment' ]    = env('APP_ENV');
        $infoEndpoint[ 'code' ]           = $exception->getCode();
        $infoEndpoint[ 'line' ]           = $exception->getLine();
        $infoEndpoint[ 'file' ]           = $exception->getFile();
        $infoEndpoint[ 'name_exception' ] = get_class($exception);
        $infoEndpoint[ 'count_errors' ]   = 1;
        
        $headers = GetAllValuesFromHeaderService::execute($request);
        
        $infoEndpoint = array_merge($infoEndpoint, $headers->toArray());
        
        $infoEndpoint = array_merge($infoEndpoint, $extraValues);
        
        if (array_key_exists('authorization', $infoEndpoint)) {
            $authorization = $infoEndpoint[ 'authorization' ];
            unset($infoEndpoint[ 'authorization' ]);
            $infoEndpoint[ 'authorization' ] = $authorization;
        }
        
        return $infoEndpoint;
    }
}
