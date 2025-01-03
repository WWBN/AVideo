'use strict';

Object.defineProperty(exports, '__esModule', { value: true });

var index_cjs = require('@fullcalendar/core/index.cjs');
var internal_cjs = require('@fullcalendar/core/internal.cjs');
var preact_cjs = require('@fullcalendar/core/preact.cjs');

const UPGRADE_WINDOW = 365 + 7; // days. 1 week leeway, for tz shift reasons too
const INVALID_LICENSE_URL = 'https://fullcalendar.io/docs/schedulerLicenseKey#invalid';
const OUTDATED_LICENSE_URL = 'https://fullcalendar.io/docs/schedulerLicenseKey#outdated';
const PRESET_LICENSE_KEYS = [
    'GPL-My-Project-Is-Open-Source',
    'CC-Attribution-NonCommercial-NoDerivatives',
];
const CSS = {
    position: 'absolute',
    zIndex: 99999,
    bottom: '1px',
    left: '1px',
    background: '#eee',
    borderColor: '#ddd',
    borderStyle: 'solid',
    borderWidth: '1px 1px 0 0',
    padding: '2px 4px',
    fontSize: '12px',
    borderTopRightRadius: '3px',
};
function buildLicenseWarning(context) {
    let key = context.options.schedulerLicenseKey;
    let currentUrl = typeof window !== 'undefined' ? window.location.href : '';
    if (!isImmuneUrl(currentUrl)) {
        let status = processLicenseKey(key, context.pluginHooks.premiumReleaseDate);
        if (status !== 'valid') {
            return (preact_cjs.createElement("div", { className: "fc-license-message", style: CSS }, (status === 'outdated') ? (preact_cjs.createElement(preact_cjs.Fragment, null,
                'Your license key is too old to work with this version. ',
                preact_cjs.createElement("a", { href: OUTDATED_LICENSE_URL }, "More Info"))) : (preact_cjs.createElement(preact_cjs.Fragment, null,
                'Your license key is invalid. ',
                preact_cjs.createElement("a", { href: INVALID_LICENSE_URL }, "More Info")))));
        }
    }
    return null;
}
/*
This decryption is not meant to be bulletproof. Just a way to remind about an upgrade.
*/
function processLicenseKey(key, premiumReleaseDate) {
    if (PRESET_LICENSE_KEYS.indexOf(key) !== -1) {
        return 'valid';
    }
    const parts = (key || '').match(/^(\d+)-fcs-(\d+)$/);
    if (parts && (parts[1].length === 10)) {
        const purchaseDate = new Date(parseInt(parts[2], 10) * 1000);
        const releaseDate = internal_cjs.config.mockSchedulerReleaseDate || premiumReleaseDate;
        if (internal_cjs.isValidDate(releaseDate)) { // token won't be replaced in dev mode
            const minPurchaseDate = internal_cjs.addDays(releaseDate, -UPGRADE_WINDOW);
            if (minPurchaseDate < purchaseDate) {
                return 'valid';
            }
            return 'outdated';
        }
    }
    return 'invalid';
}
function isImmuneUrl(url) {
    return /\w+:\/\/fullcalendar\.io\/|\/examples\/[\w-]+\.html$/.test(url);
}

const OPTION_REFINERS = {
    schedulerLicenseKey: String,
};

var index = index_cjs.createPlugin({
    name: '@fullcalendar/premium-common',
    premiumReleaseDate: '2024-07-12',
    optionRefiners: OPTION_REFINERS,
    viewContainerAppends: [buildLicenseWarning],
});

exports["default"] = index;
