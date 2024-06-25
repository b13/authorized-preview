<?php

/**
 * Definitions for modules provided by EXT:authorized_preview
 */
return [
    'site_previews' => [
        'parent' => 'site',
        'position' => ['before' => '*'],
        'access' => 'group,user',
        'iconIdentifier' => 'b13-preview',
        'labels' => 'LLL:EXT:authorized_preview/Resources/Private/Language/locallang_module.xlf',
        'navigationComponent' => '@typo3/backend/page-tree/page-tree-element',
        'extensionName' => 'authorized_preview',
        'controllerActions' => [
            \B13\AuthorizedPreview\Controller\PreviewController::class => [
                'index',
            ],
        ],
    ],
];
