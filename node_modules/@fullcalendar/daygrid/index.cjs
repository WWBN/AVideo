'use strict';

Object.defineProperty(exports, '__esModule', { value: true });

var index_cjs = require('@fullcalendar/core/index.cjs');
var internalCommon = require('./internal.cjs');
require('@fullcalendar/core/internal.cjs');
require('@fullcalendar/core/preact.cjs');

var index = index_cjs.createPlugin({
    name: '@fullcalendar/daygrid',
    initialView: 'dayGridMonth',
    views: {
        dayGrid: {
            component: internalCommon.DayGridView,
            dateProfileGeneratorClass: internalCommon.TableDateProfileGenerator,
        },
        dayGridDay: {
            type: 'dayGrid',
            duration: { days: 1 },
        },
        dayGridWeek: {
            type: 'dayGrid',
            duration: { weeks: 1 },
        },
        dayGridMonth: {
            type: 'dayGrid',
            duration: { months: 1 },
            fixedWeekCount: true,
        },
        dayGridYear: {
            type: 'dayGrid',
            duration: { years: 1 },
        },
    },
});

exports["default"] = index;
