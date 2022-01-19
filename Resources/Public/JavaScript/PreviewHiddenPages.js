define(
    ["require", "exports", "TYPO3/CMS/Backend/Notification", "TYPO3/CMS/Backend/Icons", "TYPO3/CMS/Core/Event/RegularEvent", "TYPO3/CMS/Core/Ajax/AjaxRequest"],
    (function (e, s, t, c, a, r)
    {
        "use strict";
        var n;
        !function (e) {
            e.clearCache = ".t3js-preview-hidden-page", e.icon = ".t3js-icon"
        }(n || (n = {}));

        class i {
            static setDisabled(e, s) {
                e.disabled = s, e.classList.toggle("disabled", s)
            }
            static sendHiddenPagePreviewRequest(pageUid, languageUid) {
                const s = new r(TYPO3.settings.ajaxUrls.web_list_authorizedhiddenpagespreview).withQueryArguments({id: pageUid, language: languageUid}).get({cache: "no-cache"});
                return s.then(async e => {
                    const s = await e.resolve();
                    !0 === s.success ? t.info(s.title, s.message, 20) : t.error(s.title, s.message, 1)
                }, () => {
                    t.error("Generating an authorized preview link went wrong on the server side.")
                }), s
            }

            constructor() {
                this.registerClickHandler()
            }

            registerClickHandler() {
                const e = document.querySelector(n.clearCache);
                null !== e && new a("click", e => {
                    e.preventDefault();
                    const s = e.currentTarget, pageUid = parseInt(s.dataset.id, 10), languageUid = parseInt(s.dataset.language, 10);
                    console.log(s);
                    i.setDisabled(s, !0), c.getIcon("spinner-circle-dark", c.sizes.small, null, "disabled").then(e => {
                        s.querySelector(n.icon).outerHTML = e
                    }), i.sendHiddenPagePreviewRequest(pageUid, languageUid).finally(() => {
                        c.getIcon("actions-version-workspaces-preview-link", c.sizes.small).then(e => {
                            s.querySelector(n.icon).outerHTML = e
                        }), i.setDisabled(s, !1)
                    })
                }).bindTo(e)
            }
        }

        return new i
    }));