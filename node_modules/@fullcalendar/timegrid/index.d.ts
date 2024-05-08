import { PluginDef } from '@fullcalendar/core';
import '@fullcalendar/daygrid';

declare const OPTION_REFINERS: {
    allDaySlot: BooleanConstructor;
};

type ExtraOptionRefiners = typeof OPTION_REFINERS;
declare module '@fullcalendar/core/internal' {
    interface BaseOptionRefiners extends ExtraOptionRefiners {
    }
}
//# sourceMappingURL=ambient.d.ts.map

declare const _default: PluginDef;
//# sourceMappingURL=index.d.ts.map

export { _default as default };
