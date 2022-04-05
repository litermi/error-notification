<?php

Namespace Cirelramos\ErrorNotification\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;

/**
 *
 */
class ExternalServiceRequestService
{
    /**
     * @param $baseUri
     * @param $method
     * @param $requestPath
     * @param $formParams
     * @param $headers
     * @param $modeParams
     * @return mixed
     * @throws GuzzleException
     */
    public static function execute(
        $baseUri,
        $method,
        $requestPath = '',
        $formParams = [],
        $headers = [],
        $modeParams = 'form_params',
    ) {
        $client = new Client(
            [
                'base_uri' => $baseUri,
                'curl'     => [
                    CURLOPT_SSL_VERIFYPEER => false,
                ],
            ]
        );
        
        /** @var Request $request */
        $request = request();
        foreach (config('error-notification.default_parameters_to_header') as $key => $item) {
            if ($request->$item) {
                $headers[ $key ] = $item;
            }
        }
        foreach (config('error-notification.get_special_values_from_header') as $key => $item) {
            if ($request->$item) {
                $headers[ $key ] = $request->header($item);
            }
        }
        foreach (config('error-notification.get_special_values_from_request') as $key => $item) {
            if ($request->$item) {
                $formParams[ $key ] = $request->$item;
            }
        }
        
        $formAndHeader = [
            $modeParams => $formParams,
            'headers'   => $headers,
        ];
        
        $response = $client->request($method, $requestPath, $formAndHeader);
        
        $content = $response->getBody()
            ->getContents();
        
        return json_decode($content, true);
        
    }
}