<?php
defined('TYPO3') or die();

call_user_func(function()
{
   // setting a preview_message here is necessary to prevent a fatal error lately in RequestHandler
   // There would be a `Call to a member function sL() on null` Error
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
        'authorized_preview',
        'setup',
        'config.message_preview = <div id="typo3-preview-info" style="position: fixed;top: 15px;right: 15px;padding: 8px 18px;background: #fff3cd;border: 1px solid #ffeeba;font-family: sans-serif;font-size: 14px;font-weight: bold;color: #856404;z-index: 20000;user-select: none;pointer-events: none;text-align: center;border-radius: 2px">Preview</div>'
    );
});