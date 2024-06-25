<?php

declare(strict_types=1);

namespace B13\AuthorizedPreview\Controller;

/*
 * This file is part of TYPO3 CMS extension authorized_preview by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\AuthorizedPreview\Preview\Exception\SiteMismatchException;
use B13\AuthorizedPreview\Preview\SitePreview;
use B13\AuthorizedPreview\SiteWrapper;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class PreviewController extends ActionController
{
    protected ViewInterface $moduleView;
    protected ModuleTemplate $moduleTemplate;

    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly SiteFinder $siteFinder
    ) {
    }

    protected function initializeIndexAction(): void
    {
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->moduleView = $this->moduleTemplate;
    }

    public function indexAction(): ResponseInterface
    {
        $this->moduleView->assignMultiple(
            [
                'sites' => $this->getAllSites(),
                'pageId' => $this->request->getQueryParams()['id'] ?? 0,
            ]
        );

        try {
            $sitePreview = SitePreview::createFromRequest($this->request);
            if ($sitePreview->isValid()) {
                $this->moduleView->assign('sitePreview', $sitePreview);
            }
        } catch (SiteMismatchException $exception) {
            $this->moduleView->assign('error', $exception->getMessage());
        }

        return new HtmlResponse($this->moduleView->render());
    }

    /** @return SiteWrapper[] */
    protected function getAllSites(): array
    {
        $sites = [];
        foreach ($this->siteFinder->getAllSites() as $site) {
            if (!($site instanceof Site)) {
                continue;
            }
            $sites[] = GeneralUtility::makeInstance(SiteWrapper::class, $site);
        }
        usort($sites, function (SiteWrapper $siteA, SiteWrapper $siteB) {
            return $siteA->getCountDisabledLanguages() <=> $siteB->getCountDisabledLanguages();
        });
        return $sites;
    }
}
