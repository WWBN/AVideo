'use strict';

Object.defineProperty(exports, '__esModule', { value: true });

var index_cjs = require('@fullcalendar/core/index.cjs');
var internalCommon = require('./internal.cjs');
var internal_cjs = require('@fullcalendar/core/internal.cjs');
require('@fullcalendar/core/preact.cjs');

const OPTION_REFINERS = {
    listDayFormat: createFalsableFormatter,
    listDaySideFormat: createFalsableFormatter,
    noEventsClassNames: internal_cjs.identity,
    noEventsContent: internal_cjs.identity,
    noEventsDidMount: internal_cjs.identity,
    noEventsWillUnmount: internal_cjs.identity,
    // noEventsText is defined in base options
};
function createFalsableFormatter(input) {
    return input === false ? null : internal_cjs.createFormatter(input);
}

var index = index_cjs.createPlugin({
    name: '@fullcalendar/list',
    optionRefiners: OPTION_REFINERS,
    views: {
        list: {
            component: internalCommon.ListView,
            buttonTextKey: 'list',
            listDayFormat: { month: 'long', day: 'numeric', year: 'numeric' }, // like "January 1, 2016"
        },
        listDay: {
            type: 'list',
            duration: { days: 1 },
            listDayFormat: { weekday: 'long' }, // day-of-week is all we need. full date is probably in headerToolbar
        },
        listWeek: {
            type: 'list',
            duration: { weeks: 1 },
            listDayFormat: { weekday: 'long' },
            listDaySideFormat: { month: 'long', day: 'numeric', year: 'numeric' },
        },
        listMonth: {
            type: 'list',
            duration: { month: 1 },
            listDaySideFormat: { weekday: 'long' }, // day-of-week is nice-to-have
        },
        listYear: {
            type: 'list',
            duration: { year: 1 },
            listDaySideFormat: { weekday: 'long' }, // day-of-week is nice-to-have
        },
    },
});

exports["default"] = index;
