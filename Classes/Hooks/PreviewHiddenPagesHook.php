<?php

namespace B13\AuthorizedPreview\Hooks;

use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\Buttons\LinkButton;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Add an extra authorized preview button at the end
 *
 * Class PreviewHiddenPagesHook
 * @package B13\AuthorizedPreview\Hooks
 */
class PreviewHiddenPagesHook
{
    /**
     * @var int $pageUid
     */
    protected int $pageUid = 6;

    /**
     * @var int $language
     */
    protected int $language = 5;


    /**
     * @var Context
     */
    protected $context;

    public function __construct(Context $context = null)
    {
        $this->context = $context ?? GeneralUtility::makeInstance(Context::class);
        $this->getPageAndLanguageIds(GeneralUtility::_GET());
    }

    /**
     * @param array $getVars
     * @return void
     */
    protected function getPageAndLanguageIds(array $getVars): void
    {
        if (!empty($getVars['edit']['pages'])) {
            // try to get Page Uid
            $pageUid = array_search('edit', $getVars['edit']['pages']);
            $this->setPageUid(MathUtility::canBeInterpretedAsInteger($pageUid) ? $pageUid : 0);
            $this->setPageUid($pageUid);

            // try to get Language Uid
            $languageUid = (int)$getVars['overrideVals']['pages']['sys_language_uid'];
            $this->setLanguage($languageUid);
        }
    }

    /**
     * @param array $params
     * @param ButtonBar $buttonBar
     * @return array
     * @throws AspectNotFoundException
     */
    public function addPreviewHiddenPagesButton($params, &$buttonBar)
    {
        #\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($this->getLanguage());
        #\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($this->getPageUid());
        $buttons = $params['buttons'];
        $previewButton = $buttons[ButtonBar::BUTTON_POSITION_LEFT][3][0];
        if (
            $previewButton instanceof LinkButton &&
            $this->isPageOrL10nParentHidden()
        ) {
            /** @var IconFactory $iconFactory */
            $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

            #$sitePreview = GeneralUtility::makeInstance(
            #    SitePreview::class,
            #    $this->language, 'corporate', ['type' => 'day', 'amount' => 7], $this->pageUid);
            #$previewUrl = GeneralUtility::makeInstance(PreviewUriBuilder::class, $sitePreview)->generatePreviewUrl();

            $previewHiddenPageButton = $buttonBar->makeLinkButton()
                #->setHref($previewUrl)
                ->setHref('#')
                ->setTitle($this->getLanguageService()->sL('LLL:EXT:authorized_preview/Resources/Private/Language/locallang_module.xlf:generate_preview_url'))
                ->setIcon($iconFactory->getIcon('actions-version-workspaces-preview-link', Icon::SIZE_SMALL))
                ->setClasses('t3js-preview-hidden-page')
                ->setDataAttributes(['id' => $this->getPageUid(), 'language' => $this->getLanguage()])
                ->setShowLabelText(true);

            $buttons[ButtonBar::BUTTON_POSITION_LEFT][3][] = $previewHiddenPageButton;
        }
        return $buttons;
    }

    /**
     * Returns the language service
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

    /**
     * Checks if page in a specific language or its default language is disabled
     */
    public function isPageOrL10nParentHidden(): bool
    {
        $pageRepository = GeneralUtility::makeInstance(PageRepository::class);
        $page = $pageRepository->getPage($this->pageUid);

        // translation is active, default is inactive
        // This is currently not working correct - one peace is missing.
        // Our middleware is currently not able to show an active translation while l10n parent is inactive.
        // The translation only shows up, when itself is disabled, too
        #if ($page['sys_language_uid'] > 0 && $page['hidden'] === 0) {
        #    $page = $pageRepository->getPage($page['l10n_source']);
        #}

        return $page['hidden'] === 1;
    }

    /**
     * @return int
     */
    public function getPageUid(): int
    {
        return $this->pageUid;
    }

    /**
     * @param int $pageUid
     */
    public function setPageUid(int $pageUid): void
    {
        $this->pageUid = $pageUid;
    }

    /**
     * @return int
     */
    public function getLanguage(): int
    {
        return $this->language;
    }

    /**
     * @param int $language
     */
    public function setLanguage(int $language): void
    {
        $this->language = $language;
    }
}
