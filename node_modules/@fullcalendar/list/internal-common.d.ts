import { ViewApi, EventRenderRange } from '@fullcalendar/core';
import { MountArg, DateComponent, ViewProps, Seg, DateMarker, EventStore, EventUiHash, DateRange } from '@fullcalendar/core/internal';
import { createElement } from '@fullcalendar/core/preact';

interface NoEventsContentArg {
    text: string;
    view: ViewApi;
}
type NoEventsMountArg = MountArg<NoEventsContentArg>;
declare class ListView extends DateComponent<ViewProps> {
    private computeDateVars;
    private eventStoreToSegs;
    state: {
        timeHeaderId: string;
        eventHeaderId: string;
        dateHeaderIdRoot: string;
    };
    render(): createElement.JSX.Element;
    setRootEl: (rootEl: HTMLElement | null) => void;
    renderEmptyMessage(): createElement.JSX.Element;
    renderSegList(allSegs: Seg[], dayDates: DateMarker[]): createElement.JSX.Element;
    _eventStoreToSegs(eventStore: EventStore, eventUiBases: EventUiHash, dayRanges: DateRange[]): Seg[];
    eventRangesToSegs(eventRanges: EventRenderRange[], dayRanges: DateRange[]): any[];
    eventRangeToSegs(eventRange: EventRenderRange, dayRanges: DateRange[]): any[];
}

export { ListView as L, NoEventsContentArg as N, NoEventsMountArg as a };
