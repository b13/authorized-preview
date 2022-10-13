# EXT: authorized_preview

authorized_preview is an extension for TYPO3. It adds the functionality to preview disabled languages
through generated preview URLs without the need for a backend login.

## Requirements

* TYPO3 v10 LTS or TYPO3 v11 LTS

## Installation and Setup
Install the extension via your preferred way. The extension will add one database table to the database.
No further setup is required. The extension works out of the box.

## What is does
The extension adds a backend module called "Preview". The module lists all disabled languages
("Show in frontend" = false) for each Site. For each disabled language a lifetime can be configured and a
preview URL can be generated, that can then be sent to colleagues (e.g. for proof reading).

Within their lifetime the preview URLs enable access to a disabled language without a backend login.
For any other website visitor the disabled languages is still not accessible.

## How it works
The Preview URLs are pointing to the configured bases of the languages. They also contain a
specific GET parameter.

This parameter is stored in the Database alongside the configured lifetime for the URL. As long
as this lifetime is not expired a PSR-15 middleware recognizes the GET parameter and
grants access to the language. The parameter is stored in a Cookie so that the GET parameter is
not needed on every request.

---


_Made by [b13](https://b13.com) with ♥_

[Find more TYPO3 extensions we have developed](https://b13.com/useful-typo3-extensions-from-b13-to-you) that help us deliver value in client projects. As part of the way we work, we focus on testing and best practices to ensure long-term performance, reliability, and results in all our code.
