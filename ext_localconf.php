<?php
defined('TYPO3_MODE') or die('Access denied!');

$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = \B13\AuthorizedPreview\Preview\PreviewUriBuilder::PARAMETER_NAME;
