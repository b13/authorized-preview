<?php
defined('TYPO3_MODE') or die('Access denied!');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
    'site',
    'previews',
    '',
    '',
    [
        'routeTarget' => \B13\AuthorizedPreview\Controller\PreviewController::class . '::indexAction',
        'access' => 'group,user',
        'name' => 'site_previews',
        'icon' => 'EXT:authorized_preview/Resources/Public/Icons/Extension.svg',
        'labels' => 'LLL:EXT:authorized_preview/Resources/Private/Language/locallang_module.xlf',
        'navigationComponentId' => 'TYPO3/CMS/Backend/PageTree/PageTreeElement'
    ]
);
