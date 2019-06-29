<?php
return [
    'frontend' => [
        'tx_authorized_preview/preview' => [
            'target' => B13\AuthorizedPreview\Http\Middleware\Preview::class,
            'after' => [
                'typo3/cms-frontend/site'
            ],
            'before' => [
                'typo3/cms-frontend/page-resolver',
                'typo3/cms-frontend/static-route-resolver',
                'typo3/cms-redirects/redirecthandler'
            ]
        ]
    ]
];
