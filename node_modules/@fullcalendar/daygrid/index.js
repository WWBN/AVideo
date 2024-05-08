import { createPlugin } from '@fullcalendar/core/index.js';
import { DayGridView as DayTableView, TableDateProfileGenerator } from './internal.js';
import '@fullcalendar/core/internal.js';
import '@fullcalendar/core/preact.js';

var index = createPlugin({
    name: '@fullcalendar/daygrid',
    initialView: 'dayGridMonth',
    views: {
        dayGrid: {
            component: DayTableView,
            dateProfileGeneratorClass: TableDateProfileGenerator,
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

export { index as default };
