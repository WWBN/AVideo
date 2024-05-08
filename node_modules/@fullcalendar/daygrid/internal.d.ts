import { Duration, CssDimValue } from '@fullcalendar/core';
import { DateComponent, ViewContext, DateProfile, DayTableModel, EventStore, EventUiHash, DateSpan, EventInteractionState, Seg, Slicer, DateRange, DateProfileGenerator, DateEnv, Hit, DayTableCell, EventSegUiInteractionState, Dictionary, ViewProps, ChunkConfigRowContent, ChunkContentCallbackArgs } from '@fullcalendar/core/internal';
import { createElement, VNode, RefObject } from '@fullcalendar/core/preact';

interface DayTableProps {
    dateProfile: DateProfile;
    dayTableModel: DayTableModel;
    nextDayThreshold: Duration;
    businessHours: EventStore;
    eventStore: EventStore;
    eventUiBases: EventUiHash;
    dateSelection: DateSpan | null;
    eventSelection: string;
    eventDrag: EventInteractionState | null;
    eventResize: EventInteractionState | null;
    colGroupNode: VNode;
    tableMinWidth: CssDimValue;
    renderRowIntro?: () => VNode;
    dayMaxEvents: boolean | number;
    dayMaxEventRows: boolean | number;
    expandRows: boolean;
    showWeekNumbers: boolean;
    headerAlignElRef?: RefObject<HTMLElement>;
    clientWidth: number | null;
    clientHeight: number | null;
    forPrint: boolean;
}
declare class DayTable extends DateComponent<DayTableProps, ViewContext> {
    private slicer;
    private tableRef;
    render(): createElement.JSX.Element;
}

interface TableSeg extends Seg {
    row: number;
    firstCol: number;
    lastCol: number;
}

declare class DayTableSlicer extends Slicer<TableSeg, [DayTableModel]> {
    forceDayIfListItem: boolean;
    sliceRange(dateRange: DateRange, dayTableModel: DayTableModel): TableSeg[];
}

declare class TableDateProfileGenerator extends DateProfileGenerator {
    buildRenderRange(currentRange: any, currentRangeUnit: any, isRangeAllDay: any): DateRange;
}
declare function buildDayTableRenderRange(props: {
    currentRange: DateRange;
    snapToWeek: boolean;
    fixedWeekCount: boolean;
    dateEnv: DateEnv;
}): DateRange;

interface TableRowsProps {
    dateProfile: DateProfile;
    cells: DayTableCell[][];
    renderRowIntro?: () => VNode;
    showWeekNumbers: boolean;
    clientWidth: number | null;
    clientHeight: number | null;
    businessHourSegs: TableSeg[];
    bgEventSegs: TableSeg[];
    fgEventSegs: TableSeg[];
    dateSelectionSegs: TableSeg[];
    eventSelection: string;
    eventDrag: EventSegUiInteractionState | null;
    eventResize: EventSegUiInteractionState | null;
    dayMaxEvents: boolean | number;
    dayMaxEventRows: boolean | number;
    forPrint: boolean;
    isHitComboAllowed?: (hit0: Hit, hit1: Hit) => boolean;
}
declare class TableRows extends DateComponent<TableRowsProps> {
    private splitBusinessHourSegs;
    private splitBgEventSegs;
    private splitFgEventSegs;
    private splitDateSelectionSegs;
    private splitEventDrag;
    private splitEventResize;
    private rootEl;
    private rowRefs;
    private rowPositions;
    private colPositions;
    render(): createElement.JSX.Element;
    componentDidMount(): void;
    componentDidUpdate(): void;
    registerInteractiveComponent(): void;
    componentWillUnmount(): void;
    prepareHits(): void;
    queryHit(positionLeft: number, positionTop: number): Hit;
    private getCellEl;
    private getCellRange;
}

interface TableProps extends TableRowsProps {
    colGroupNode: VNode;
    tableMinWidth: CssDimValue;
    expandRows: boolean;
    headerAlignElRef?: RefObject<HTMLElement>;
}
declare class Table extends DateComponent<TableProps> {
    private elRef;
    private needsScrollReset;
    render(): createElement.JSX.Element;
    componentDidMount(): void;
    componentDidUpdate(prevProps: TableProps): void;
    requestScrollReset(): void;
    flushScrollReset(): void;
}

declare abstract class TableView<State = Dictionary> extends DateComponent<ViewProps, State> {
    protected headerElRef: RefObject<HTMLTableCellElement>;
    renderSimpleLayout(headerRowContent: ChunkConfigRowContent, bodyContent: (contentArg: ChunkContentCallbackArgs) => VNode): createElement.JSX.Element;
    renderHScrollLayout(headerRowContent: ChunkConfigRowContent, bodyContent: (contentArg: ChunkContentCallbackArgs) => VNode, colCnt: number, dayMinWidth: number): createElement.JSX.Element;
}

declare class DayTableView extends TableView {
    private buildDayTableModel;
    private headerRef;
    private tableRef;
    render(): createElement.JSX.Element;
}
declare function buildDayTableModel(dateProfile: DateProfile, dateProfileGenerator: DateProfileGenerator): DayTableModel;

export { DayTableView as DayGridView, DayTable, DayTableSlicer, Table, TableDateProfileGenerator, TableRows, TableSeg, TableView, buildDayTableModel, buildDayTableRenderRange };
