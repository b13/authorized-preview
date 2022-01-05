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
        'labels' => 'LLL:EXT:authorized_preview/Resources/Private/Language/locallang_module.xlf'
    ]
);
if (TYPO3_MODE === 'BE') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][] = 'B13\AuthorizedPreview\Hooks\PageRenderer->addJSCSS';
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['Backend\Template\Components\ButtonBar']['getButtonsHook'][] = 'B13\AuthorizedPreview\Hooks\PreviewHiddenPagesHook->addPreviewHiddenPagesButton';
}