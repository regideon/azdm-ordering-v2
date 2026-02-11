<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Debug
    |--------------------------------------------------------------------------
    */
    'debugbar_enabled' => env('DEBUGBAR_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Filesystem (DigitalOcean Spaces)
    |--------------------------------------------------------------------------
    */
    'filesystem' => [
        'disk' => env('FILESYSTEM_DISK', 'local'),

        'spaces' => [
            'key'          => env('DO_SPACES_KEY'),
            'secret'       => env('DO_SPACES_SECRET'),
            'endpoint'     => env('DO_SPACES_ENDPOINT'),
            'region'       => env('DO_SPACES_REGION'),
            'bucket'       => env('DO_SPACES_BUCKET'),
            'cdn_endpoint' => env('DO_SPACES_CDN_ENDPOINT'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Document Totals
    |--------------------------------------------------------------------------
    */
    'document_total' => [

        'types' => [
            'invoice' => env('DOCUMENTTOTAL_TYPE_INVOICE'),
            'bill'    => env('DOCUMENTTOTAL_TYPE_BILL'),
        ],

        'codes' => [
            'sub_total' => env('DOCUMENTTOTAL_CODE_SUBTOTAL'),
            'discount'  => env('DOCUMENTTOTAL_CODE_DISCOUNT'),
            'tax'       => env('DOCUMENTTOTAL_CODE_TAX'),
            'total'     => env('DOCUMENTTOTAL_CODE_TOTAL'),
        ],

        'names' => [
            'sub_total' => env('DOCUMENTTOTAL_NAME_SUBTOTAL'),
            'discount'  => env('DOCUMENTTOTAL_NAME_DISCOUNT'),
            'tax'       => env('DOCUMENTTOTAL_NAME_TAX'),
            'total'     => env('DOCUMENTTOTAL_NAME_TOTAL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Item History
    |--------------------------------------------------------------------------
    */
    'item_history' => [
        'actions' => [
            'created'    => env('ITEM_HISTORY_ACTION_TYPE_CREATED'),
            'invoice'    => env('ITEM_HISTORY_ACTION_TYPE_INVOICE'),
            'adjustment' => env('ITEM_HISTORY_ACTION_TYPE_ADJUSTMENT'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Settings
    |--------------------------------------------------------------------------
    */
    'export' => [

        'excel_limit' => env('DOWNLOAD_EXCEL_LIMIT', 200),

        'time_ranges' => [
            'morning' => [
                'start' => env('EXPORT_TIME_MORNING_START'),
                'end'   => env('EXPORT_TIME_MORNING_END'),
            ],
            'afternoon' => [
                'start' => env('EXPORT_TIME_AFTERNOON_START'),
                'end'   => env('EXPORT_TIME_AFTERNOON_END'),
            ],
            'night' => [
                'start' => env('EXPORT_TIME_NIGHT_START'),
                'end'   => env('EXPORT_TIME_NIGHT_END'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Credit Settings
    |--------------------------------------------------------------------------
    */
    'credit' => [
        'single_transaction' => [
            'enabled' => env('CREDIT_PAYMENTYPE_SINGLE_TRANSACTION', 0),
            'title'   => env('CREDIT_PAYMENTYPE_SINGLE_TRANSACTION_TITLE'),
            'message' => env('CREDIT_PAYMENTYPE_SINGLE_TRANSACTION_MSG'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security & Controls
    |--------------------------------------------------------------------------
    */
    'security' => [

        'halt_save' => [
            'enabled' => env('HALT_SAVE_RECORD_FOR_TRANSACTIONS', 0),
            'message' => env('HALT_SAVE_RECORD_MSG'),
        ],

        'max_file_upload'    => env('MAX_FILE_UPLOAD', 5000),
        'file_upload_resize' => env('FILE_UPLOAD_RESIZE', 50),

        'turnstile' => [
            'site_key'   => env('TURNSTILE_SITE_KEY'),
            'secret_key' => env('TURNSTILE_SECRET_KEY'),
        ],

        'bugsnag_api_key' => env('BUGSNAG_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cookie Settings
    |--------------------------------------------------------------------------
    */
    'cookies' => [

        'names' => [
            'max_oos'     => env('COOKIE_MAX_OOS'),
            'max_returns' => env('COOKIE_MAX_RETURNS'),
            'max_refunds' => env('COOKIE_MAX_REFUNDS'),
            'max_offset'  => env('COOKIE_MAX_OFFSET'),
        ],

        'toggle_hours' => env('COOKIE_TOGGLE_MAX_HOURS', 525600),
    ],

    /*
    |--------------------------------------------------------------------------
    | Session
    |--------------------------------------------------------------------------
    */
    'session' => [
        'edit_document_key' => env('SESSION_NAME_EDIT_DOCUMENT'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoice Settings
    |--------------------------------------------------------------------------
    */
    'invoice' => [
        'str_pad_length' => env('INVOICE_STRPAD', 8),
    ],

    /*
    |--------------------------------------------------------------------------
    | App Timezone
    |--------------------------------------------------------------------------
    */
    'timezone' => env('APP_TIMEZONE', 'UTC'),

];

/** 
 * How To Use It
 * 
// Debug
config('azdm.debugbar_enabled');


// Filesystem (DigitalOcean Spaces)
config('azdm.filesystem.disk');

config('azdm.filesystem.spaces.key');
config('azdm.filesystem.spaces.secret');
config('azdm.filesystem.spaces.endpoint');
config('azdm.filesystem.spaces.region');
config('azdm.filesystem.spaces.bucket');
config('azdm.filesystem.spaces.cdn_endpoint');


// Document Totals
config('azdm.document_total.types.invoice');
config('azdm.document_total.types.bill');

config('azdm.document_total.codes.sub_total');
config('azdm.document_total.codes.discount');
config('azdm.document_total.codes.tax');
config('azdm.document_total.codes.total');

config('azdm.document_total.names.sub_total');
config('azdm.document_total.names.discount');
config('azdm.document_total.names.tax');
config('azdm.document_total.names.total');


// Item History
config('azdm.item_history.actions.created');
config('azdm.item_history.actions.invoice');
config('azdm.item_history.actions.adjustment');


// Export Settings
config('azdm.export.excel_limit');

config('azdm.export.time_ranges.morning.start');
config('azdm.export.time_ranges.morning.end');

config('azdm.export.time_ranges.afternoon.start');
config('azdm.export.time_ranges.afternoon.end');

config('azdm.export.time_ranges.night.start');
config('azdm.export.time_ranges.night.end');


// Credit Settings
config('azdm.credit.single_transaction.enabled');
config('azdm.credit.single_transaction.title');
config('azdm.credit.single_transaction.message');


// Security & Controls
config('azdm.security.halt_save.enabled');
config('azdm.security.halt_save.message');

config('azdm.security.max_file_upload');
config('azdm.security.file_upload_resize');

config('azdm.security.turnstile.site_key');
config('azdm.security.turnstile.secret_key');

config('azdm.security.bugsnag_api_key');


// Cookie Settings
config('azdm.cookies.names.max_oos');
config('azdm.cookies.names.max_returns');
config('azdm.cookies.names.max_refunds');
config('azdm.cookies.names.max_offset');

config('azdm.cookies.toggle_hours');


// Session
config('azdm.session.edit_document_key');


// Invoice Settings
config('azdm.invoice.str_pad_length');


// App Timezone
config('azdm.timezone');
 
  
*/