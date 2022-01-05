<?php
/**
 * Definitions of routes
 */
return [
    'web_list_authorizedhiddenpagespreview' => [
        'path' => '/web/list/authorizedhiddenpagespreview',
        'target' => \B13\AuthorizedPreview\Controller\PreviewHiddenPagesController::class . '::mainAction'
    ],
];
