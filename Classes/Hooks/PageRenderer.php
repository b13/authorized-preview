<?php

namespace B13\AuthorizedPreview\Hooks;

use TYPO3\CMS\Backend\Controller\EditDocumentController;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Add JavaScript to PageRender
 *
 * Class PreviewHiddenPagesHook
 * @package B13\AuthorizedPreview\Hooks
 */
class PageRenderer
{
    /**
     * wrapper function called by hook (\TYPO3\CMS\Core\Page\PageRenderer->render-preProcess)
     *
     * @param array $parameters An array of available parameters
     * @param \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer The parent object that triggered this hook
     */
    public function addJSCSS(array $parameters, \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer)
    {
        // Page Module would be "PageLayoutController" instead of EditDocumentController
        if (!empty($GLOBALS['SOBE']) && (get_class($GLOBALS['SOBE']) === EditDocumentController::class || is_subclass_of(
                    $GLOBALS['SOBE'],
                    EditDocumentController::class
                ))) {
            //only include JS, when editing _hidden_ pages
            $previewHiddenPagesHook = GeneralUtility::makeInstance(PreviewHiddenPagesHook::class);
            if ($previewHiddenPagesHook->isPageOrL10nParentHidden()) {
                $pageRenderer->loadRequireJsModule(
                    'TYPO3/CMS/AuthorizedPreview/PreviewHiddenPages',
                    'function() { console.log("Loaded own module."); }'
                );
            }

        }
    }
}