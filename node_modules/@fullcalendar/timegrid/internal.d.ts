import { Duration, CssDimValue } from '@fullcalendar/core';
import { Splitter, DateSpan, EventDef, DateMarker, DateEnv, PositionCache, DateProfile, DateComponent, ViewProps, ChunkContentCallbackArgs, DateProfileGenerator, DayTableModel, Seg, DateRange, EventStore, EventUiHash, EventInteractionState, Slicer, ScrollRequest, Hit, DayTableCell, EventSegUiInteractionState } from '@fullcalendar/core/internal';
import { RefObject, VNode, createElement } from '@fullcalendar/core/preact';

declare class AllDaySplitter extends Splitter {
    getKeyInfo(): {
        allDay: {};
        timed: {};
    };
    getKeysForDateSpan(dateSpan: DateSpan): string[];
    getKeysForEventDef(eventDef: EventDef): string[];
}

interface TimeSlatMeta {
    date: DateMarker;
    time: Duration;
    key: string;
    isoTimeStr: string;
    isLabeled: boolean;
}
declare function buildSlatMetas(slotMinTime: Duration, slotMaxTime: Duration, explicitLabelInterval: Duration | null, slotDuration: Duration, dateEnv: DateEnv): TimeSlatMeta[];

declare class TimeColsSlatsCoords {
    positions: PositionCache;
    private dateProfile;
    private slotDuration;
    constructor(positions: PositionCache, dateProfile: DateProfile, slotDuration: Duration);
    safeComputeTop(date: DateMarker): number;
    computeDateTop(when: DateMarker, startOfDayDate?: DateMarker): number;
    computeTimeTop(duration: Duration): number;
}

interface TimeColsViewState {
    slatCoords: TimeColsSlatsCoords | null;
}
declare abstract class TimeColsView extends DateComponent<ViewProps, TimeColsViewState> {
    protected allDaySplitter: AllDaySplitter;
    protected headerElRef: RefObject<HTMLTableCellElement>;
    private rootElRef;
    private scrollerElRef;
    state: {
        slatCoords: any;
    };
    renderSimpleLayout(headerRowContent: VNode | null, allDayContent: ((contentArg: ChunkContentCallbackArgs) => VNode) | null, timeContent: ((contentArg: ChunkContentCallbackArgs) => VNode) | null): createElement.JSX.Element;
    renderHScrollLayout(headerRowContent: VNode | null, allDayContent: ((contentArg: ChunkContentCallbackArgs) => VNode) | null, timeContent: ((contentArg: ChunkContentCallbackArgs) => VNode) | null, colCnt: number, dayMinWidth: number, slatMetas: TimeSlatMeta[], slatCoords: TimeColsSlatsCoords | null): createElement.JSX.Element;
    handleScrollTopRequest: (scrollTop: number) => void;
    getAllDayMaxEventProps(): {
        dayMaxEvents: number | boolean;
        dayMaxEventRows: number | false;
    };
    renderHeadAxis: (rowKey: 'day' | string, frameHeight?: CssDimValue) => createElement.JSX.Element;
    renderTableRowAxis: (rowHeight?: number) => createElement.JSX.Element;
    handleSlatCoords: (slatCoords: TimeColsSlatsCoords) => void;
}

declare class DayTimeColsView extends TimeColsView {
    private buildTimeColsModel;
    private buildSlatMetas;
    render(): createElement.JSX.Element;
}
declare function buildTimeColsModel(dateProfile: DateProfile, dateProfileGenerator: DateProfileGenerator): DayTableModel;

interface TimeColsSeg extends Seg {
    col: number;
    start: DateMarker;
    end: DateMarker;
}

interface DayTimeColsProps {
    dateProfile: DateProfile;
    dayTableModel: DayTableModel;
    axis: boolean;
    slotDuration: Duration;
    slatMetas: TimeSlatMeta[];
    businessHours: EventStore;
    eventStore: EventStore;
    eventUiBases: EventUiHash;
    dateSelection: DateSpan | null;
    eventSelection: string;
    eventDrag: EventInteractionState | null;
    eventResize: EventInteractionState | null;
    tableColGroupNode: VNode;
    tableMinWidth: CssDimValue;
    clientWidth: number | null;
    clientHeight: number | null;
    expandRows: boolean;
    onScrollTopRequest?: (scrollTop: number) => void;
    forPrint: boolean;
    onSlatCoords?: (slatCoords: TimeColsSlatsCoords) => void;
}
declare class DayTimeCols extends DateComponent<DayTimeColsProps> {
    private buildDayRanges;
    private slicer;
    private timeColsRef;
    render(): createElement.JSX.Element;
}
declare function buildDayRanges(dayTableModel: DayTableModel, dateProfile: DateProfile, dateEnv: DateEnv): DateRange[];

declare class DayTimeColsSlicer extends Slicer<TimeColsSeg, [DateRange[]]> {
    sliceRange(range: DateRange, dayRanges: DateRange[]): TimeColsSeg[];
}

interface TimeColsProps {
    cells: DayTableCell[];
    dateProfile: DateProfile;
    slotDuration: Duration;
    nowDate: DateMarker;
    todayRange: DateRange;
    businessHourSegs: TimeColsSeg[];
    bgEventSegs: TimeColsSeg[];
    fgEventSegs: TimeColsSeg[];
    dateSelectionSegs: TimeColsSeg[];
    eventSelection: string;
    eventDrag: EventSegUiInteractionState | null;
    eventResize: EventSegUiInteractionState | null;
    tableColGroupNode: VNode;
    tableMinWidth: CssDimValue;
    clientWidth: number | null;
    clientHeight: number | null;
    expandRows: boolean;
    nowIndicatorSegs: TimeColsSeg[];
    onScrollTopRequest?: (scrollTop: number) => void;
    forPrint: boolean;
    axis: boolean;
    slatMetas: TimeSlatMeta[];
    onSlatCoords?: (slatCoords: TimeColsSlatsCoords) => void;
    isHitComboAllowed?: (hit0: Hit, hit1: Hit) => boolean;
}
interface TimeColsState {
    slatCoords: TimeColsSlatsCoords | null;
}
declare class TimeCols extends DateComponent<TimeColsProps, TimeColsState> {
    private processSlotOptions;
    private scrollResponder;
    private colCoords;
    state: {
        slatCoords: any;
    };
    render(): createElement.JSX.Element;
    handleRootEl: (el: HTMLElement | null) => void;
    componentDidMount(): void;
    componentDidUpdate(prevProps: TimeColsProps): void;
    componentWillUnmount(): void;
    handleScrollRequest: (request: ScrollRequest) => boolean;
    handleColCoords: (colCoords: PositionCache | null) => void;
    handleSlatCoords: (slatCoords: TimeColsSlatsCoords | null) => void;
    queryHit(positionLeft: number, positionTop: number): Hit;
}

export { DayTimeCols, DayTimeColsSlicer, DayTimeColsView, TimeCols, TimeColsSeg, TimeColsSlatsCoords, TimeColsView, TimeSlatMeta, buildDayRanges, buildSlatMetas, buildTimeColsModel };
