import { ClassNamesGenerator, FormatterInput, PluginDef } from '@fullcalendar/core';
import { Identity, CustomContentGenerator, DidMountHandler, WillUnmountHandler, DateFormatter } from '@fullcalendar/core/internal';
import { N as NoEventsContentArg, a as NoEventsMountArg } from './internal-common.js';
export { N as NoEventsContentArg, a as NoEventsMountArg } from './internal-common.js';
import '@fullcalendar/core/preact';

declare const OPTION_REFINERS: {
    listDayFormat: typeof createFalsableFormatter;
    listDaySideFormat: typeof createFalsableFormatter;
    noEventsClassNames: Identity<ClassNamesGenerator<NoEventsContentArg>>;
    noEventsContent: Identity<CustomContentGenerator<NoEventsContentArg>>;
    noEventsDidMount: Identity<DidMountHandler<NoEventsMountArg>>;
    noEventsWillUnmount: Identity<WillUnmountHandler<NoEventsMountArg>>;
};
declare function createFalsableFormatter(input: FormatterInput | false): DateFormatter;

type ExtraOptionRefiners = typeof OPTION_REFINERS;
declare module '@fullcalendar/core/internal' {
    interface BaseOptionRefiners extends ExtraOptionRefiners {
    }
}
//# sourceMappingURL=ambient.d.ts.map

declare const _default: PluginDef;
//# sourceMappingURL=index.d.ts.map

export { _default as default };
