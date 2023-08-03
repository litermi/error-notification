<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel error-notification
    |--------------------------------------------------------------------------
    |
    |
    */


    /*
     * get special values from header
     * example:
     */
    'get_special_values_from_header' => [
        'special' => 'value',
    ],


    /*
     * get special values from request
     * example:
     */
    'get_special_values_from_request' => [
        'ip' => 'ip',
    ],


    /*
     * view/template alert email
     * example:
     */
    'view-alert-email' => "templates.emails",

    /*
     * cache tag name
     * example:
     */
    'cache-tag-name' => "CACHE_GROUP_NOTIFICATION",


    /*
     * cache tag time
     * example:
     */
    'cache-tag-time' => 3600,

    /*
     * cache name
     * example:
     */
    'cache-name' => "default_name_cache",

    /*
     * mail recipient notification error
     * example:
     */
    'mail-recipient' => "yourmail@gmail.com",

    /*
     * default channel slack
     * example:
     */
    'default-channel-slack' => "",

    'direct-notification' => false,
];

