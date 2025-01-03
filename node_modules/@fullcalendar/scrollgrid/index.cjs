'use strict';

Object.defineProperty(exports, '__esModule', { value: true });

var index_cjs = require('@fullcalendar/core/index.cjs');
var premiumCommonPlugin = require('@fullcalendar/premium-common/index.cjs');
var internalCommon = require('./internal.cjs');
require('@fullcalendar/core/internal.cjs');
require('@fullcalendar/core/preact.cjs');

function _interopDefaultLegacy (e) { return e && typeof e === 'object' && 'default' in e ? e : { 'default': e }; }

var premiumCommonPlugin__default = /*#__PURE__*/_interopDefaultLegacy(premiumCommonPlugin);

var index = index_cjs.createPlugin({
    name: '@fullcalendar/scrollgrid',
    premiumReleaseDate: '2024-07-12',
    deps: [premiumCommonPlugin__default["default"]],
    scrollGridImpl: internalCommon.ScrollGrid,
});

exports["default"] = index;
