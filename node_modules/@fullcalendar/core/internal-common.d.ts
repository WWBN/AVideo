import * as preact from 'preact';
import { ComponentChildren, ComponentType, Context, Ref, Component, VNode, createElement, JSX, ComponentChild, FunctionalComponent, RefObject } from './preact.js';
import { ViewApi as ViewApi$1 } from './index.js';

type DurationInput = DurationObjectInput | string | number;
interface DurationObjectInput {
    years?: number;
    year?: number;
    months?: number;
    month?: number;
    weeks?: number;
    week?: number;
    days?: number;
    day?: number;
    hours?: number;
    hour?: number;
    minutes?: number;
    minute?: number;
    seconds?: number;
    second?: number;
    milliseconds?: number;
    millisecond?: number;
    ms?: number;
}
interface Duration {
    years: number;
    months: number;
    days: number;
    milliseconds: number;
    specifiedWeeks?: boolean;
}
declare function createDuration(input: DurationInput, unit?: string): Duration | null;
declare function asCleanDays(dur: Duration): number;
declare function addDurations(d0: Duration, d1: Duration): {
    years: number;
    months: number;
    days: number;
    milliseconds: number;
};
declare function multiplyDuration(d: Duration, n: number): {
    years: number;
    months: number;
    days: number;
    milliseconds: number;
};
declare function asRoughMinutes(dur: Duration): number;
declare function asRoughSeconds(dur: Duration): number;
declare function asRoughMs(dur: Duration): number;
declare function wholeDivideDurations(numerator: Duration, denominator: Duration): number;
declare function greatestDurationDenominator(dur: Duration): {
    unit: string;
    value: number;
};

type DateMarker = Date;
declare function addWeeks(m: DateMarker, n: number): Date;
declare function addDays(m: DateMarker, n: number): Date;
declare function addMs(m: DateMarker, n: number): Date;
declare function diffWeeks(m0: any, m1: any): number;
declare function diffDays(m0: any, m1: any): number;
declare function diffDayAndTime(m0: DateMarker, m1: DateMarker): Duration;
declare function diffWholeWeeks(m0: DateMarker, m1: DateMarker): number;
declare function diffWholeDays(m0: DateMarker, m1: DateMarker): number;
declare function startOfDay(m: DateMarker): DateMarker;
declare function isValidDate(m: DateMarker): boolean;

interface CalendarSystem {
    getMarkerYear(d: DateMarker): number;
    getMarkerMonth(d: DateMarker): number;
    getMarkerDay(d: DateMarker): number;
    arrayToMarker(arr: number[]): DateMarker;
    markerToArray(d: DateMarker): number[];
}

type LocaleCodeArg = string | string[];
type LocaleSingularArg = LocaleCodeArg | LocaleInput;
interface Locale {
    codeArg: LocaleCodeArg;
    codes: string[];
    week: {
        dow: number;
        doy: number;
    };
    simpleNumberFormat: Intl.NumberFormat;
    options: CalendarOptionsRefined;
}
interface LocaleInput extends CalendarOptions {
    code: string;
}
type LocaleInputMap = {
    [code: string]: LocaleInput;
};
interface RawLocaleInfo {
    map: LocaleInputMap;
    defaultCode: string;
}

interface ZonedMarker {
    marker: DateMarker;
    timeZoneOffset: number;
}
interface ExpandedZonedMarker extends ZonedMarker {
    array: number[];
    year: number;
    month: number;
    day: number;
    hour: number;
    minute: number;
    second: number;
    millisecond: number;
}

interface VerboseFormattingArg {
    date: ExpandedZonedMarker;
    start: ExpandedZonedMarker;
    end?: ExpandedZonedMarker;
    timeZone: string;
    localeCodes: string[];
    defaultSeparator: string;
}
type CmdFormatterFunc = (cmd: string, arg: VerboseFormattingArg) => string;
interface DateFormattingContext {
    timeZone: string;
    locale: Locale;
    calendarSystem: CalendarSystem;
    computeWeekNumber: (d: DateMarker) => number;
    weekText: string;
    weekTextLong: string;
    cmdFormatter?: CmdFormatterFunc;
    defaultSeparator: string;
}
interface DateFormatter {
    format(date: ZonedMarker, context: DateFormattingContext): string;
    formatRange(start: ZonedMarker, end: ZonedMarker, context: DateFormattingContext, betterDefaultSeparator?: string): string;
}

interface NativeFormatterOptions extends Intl.DateTimeFormatOptions {
    week?: 'long' | 'short' | 'narrow' | 'numeric';
    meridiem?: 'lowercase' | 'short' | 'narrow' | boolean;
    omitZeroMinute?: boolean;
    omitCommas?: boolean;
    separator?: string;
}

type FuncFormatterFunc = (arg: VerboseFormattingArg) => string;

type FormatterInput = NativeFormatterOptions | string | FuncFormatterFunc;
declare function createFormatter(input: FormatterInput): DateFormatter;

declare function guid(): string;
declare function disableCursor(): void;
declare function enableCursor(): void;
declare function preventSelection(el: HTMLElement): void;
declare function allowSelection(el: HTMLElement): void;
declare function preventContextMenu(el: HTMLElement): void;
declare function allowContextMenu(el: HTMLElement): void;
interface OrderSpec<Subject> {
    field?: string;
    order?: number;
    func?: FieldSpecInputFunc<Subject>;
}
type FieldSpecInput<Subject> = string | string[] | FieldSpecInputFunc<Subject> | FieldSpecInputFunc<Subject>[];
type FieldSpecInputFunc<Subject> = (a: Subject, b: Subject) => number;
declare function parseFieldSpecs<Subject>(input: FieldSpecInput<Subject>): OrderSpec<Subject>[];
declare function compareByFieldSpecs<Subject>(obj0: Subject, obj1: Subject, fieldSpecs: OrderSpec<Subject>[]): number;
declare function flexibleCompare(a: any, b: any): number;
declare function padStart(val: any, len: any): string;
declare function compareNumbers(a: any, b: any): number;
declare function isInt(n: any): boolean;

interface ViewApi {
    calendar: CalendarApi;
    type: string;
    title: string;
    activeStart: Date;
    activeEnd: Date;
    currentStart: Date;
    currentEnd: Date;
    getOption(name: string): any;
}

interface EventSourceApi {
    id: string;
    url: string;
    format: string;
    remove(): void;
    refetch(): void;
}

declare abstract class NamedTimeZoneImpl {
    timeZoneName: string;
    constructor(timeZoneName: string);
    abstract offsetForArray(a: number[]): number;
    abstract timestampToArray(ms: number): number[];
}
type NamedTimeZoneImplClass = {
    new (timeZoneName: string): NamedTimeZoneImpl;
};

type WeekNumberCalculation = 'local' | 'ISO' | ((m: Date) => number);
interface DateEnvSettings {
    timeZone: string;
    namedTimeZoneImpl?: NamedTimeZoneImplClass;
    calendarSystem: string;
    locale: Locale;
    weekNumberCalculation?: WeekNumberCalculation;
    firstDay?: number;
    weekText?: string;
    weekTextLong?: string;
    cmdFormatter?: CmdFormatterFunc;
    defaultSeparator?: string;
}
type DateInput = Date | string | number | number[];
interface DateMarkerMeta {
    marker: DateMarker;
    isTimeUnspecified: boolean;
    forcedTzo: number | null;
}
declare class DateEnv {
    timeZone: string;
    namedTimeZoneImpl: NamedTimeZoneImpl;
    canComputeOffset: boolean;
    calendarSystem: CalendarSystem;
    locale: Locale;
    weekDow: number;
    weekDoy: number;
    weekNumberFunc: any;
    weekText: string;
    weekTextLong: string;
    cmdFormatter?: CmdFormatterFunc;
    defaultSeparator: string;
    constructor(settings: DateEnvSettings);
    createMarker(input: DateInput): DateMarker;
    createNowMarker(): DateMarker;
    createMarkerMeta(input: DateInput): DateMarkerMeta;
    parse(s: string): {
        marker: Date;
        isTimeUnspecified: boolean;
        forcedTzo: any;
    };
    getYear(marker: DateMarker): number;
    getMonth(marker: DateMarker): number;
    getDay(marker: DateMarker): number;
    add(marker: DateMarker, dur: Duration): DateMarker;
    subtract(marker: DateMarker, dur: Duration): DateMarker;
    addYears(marker: DateMarker, n: number): Date;
    addMonths(marker: DateMarker, n: number): Date;
    diffWholeYears(m0: DateMarker, m1: DateMarker): number;
    diffWholeMonths(m0: DateMarker, m1: DateMarker): number;
    greatestWholeUnit(m0: DateMarker, m1: DateMarker): {
        unit: string;
        value: number;
    };
    countDurationsBetween(m0: DateMarker, m1: DateMarker, d: Duration): number;
    startOf(m: DateMarker, unit: string): Date;
    startOfYear(m: DateMarker): DateMarker;
    startOfMonth(m: DateMarker): DateMarker;
    startOfWeek(m: DateMarker): DateMarker;
    computeWeekNumber(marker: DateMarker): number;
    format(marker: DateMarker, formatter: DateFormatter, dateOptions?: {
        forcedTzo?: number;
    }): string;
    formatRange(start: DateMarker, end: DateMarker, formatter: DateFormatter, dateOptions?: {
        forcedStartTzo?: number;
        forcedEndTzo?: number;
        isEndExclusive?: boolean;
        defaultSeparator?: string;
    }): string;
    formatIso(marker: DateMarker, extraOptions?: any): string;
    timestampToMarker(ms: number): Date;
    offsetForMarker(m: DateMarker): number;
    toDate(m: DateMarker, forcedTzo?: number): Date;
}

interface DateRangeInput {
    start?: DateInput;
    end?: DateInput;
}
interface OpenDateRange {
    start: DateMarker | null;
    end: DateMarker | null;
}
interface DateRange {
    start: DateMarker;
    end: DateMarker;
}
declare function intersectRanges(range0: OpenDateRange, range1: OpenDateRange): OpenDateRange;
declare function rangesEqual(range0: OpenDateRange, range1: OpenDateRange): boolean;
declare function rangesIntersect(range0: OpenDateRange, range1: OpenDateRange): boolean;
declare function rangeContainsRange(outerRange: OpenDateRange, innerRange: OpenDateRange): boolean;
declare function rangeContainsMarker(range: OpenDateRange, date: DateMarker | number): boolean;

interface EventInstance {
    instanceId: string;
    defId: string;
    range: DateRange;
    forcedStartTzo: number | null;
    forcedEndTzo: number | null;
}
type EventInstanceHash = {
    [instanceId: string]: EventInstance;
};
declare function createEventInstance(defId: string, range: DateRange, forcedStartTzo?: number, forcedEndTzo?: number): EventInstance;

interface PointerDragEvent {
    origEvent: UIEvent;
    isTouch: boolean;
    subjectEl: EventTarget;
    pageX: number;
    pageY: number;
    deltaX: number;
    deltaY: number;
}

interface EventMutation {
    datesDelta?: Duration;
    startDelta?: Duration;
    endDelta?: Duration;
    standardProps?: any;
    extendedProps?: any;
}
declare function applyMutationToEventStore(eventStore: EventStore, eventConfigBase: EventUiHash, mutation: EventMutation, context: CalendarContext): EventStore;
type eventDefMutationApplier = (eventDef: EventDef, mutation: EventMutation, context: CalendarContext) => void;

declare class EventSourceImpl implements EventSourceApi {
    private context;
    internalEventSource: EventSource<any>;
    constructor(context: CalendarContext, internalEventSource: EventSource<any>);
    remove(): void;
    refetch(): void;
    get id(): string;
    get url(): string;
    get format(): string;
}

declare class EventImpl implements EventApi {
    _context: CalendarContext;
    _def: EventDef;
    _instance: EventInstance | null;
    constructor(context: CalendarContext, def: EventDef, instance?: EventInstance);
    setProp(name: string, val: any): void;
    setExtendedProp(name: string, val: any): void;
    setStart(startInput: DateInput, options?: {
        granularity?: string;
        maintainDuration?: boolean;
    }): void;
    setEnd(endInput: DateInput | null, options?: {
        granularity?: string;
    }): void;
    setDates(startInput: DateInput, endInput: DateInput | null, options?: {
        allDay?: boolean;
        granularity?: string;
    }): void;
    moveStart(deltaInput: DurationInput): void;
    moveEnd(deltaInput: DurationInput): void;
    moveDates(deltaInput: DurationInput): void;
    setAllDay(allDay: boolean, options?: {
        maintainDuration?: boolean;
    }): void;
    formatRange(formatInput: FormatterInput): string;
    mutate(mutation: EventMutation): void;
    remove(): void;
    get source(): EventSourceImpl | null;
    get start(): Date | null;
    get end(): Date | null;
    get startStr(): string;
    get endStr(): string;
    get id(): string;
    get groupId(): string;
    get allDay(): boolean;
    get title(): string;
    get url(): string;
    get display(): string;
    get startEditable(): boolean;
    get durationEditable(): boolean;
    get constraint(): string | EventStore;
    get overlap(): boolean;
    get allow(): AllowFunc;
    get backgroundColor(): string;
    get borderColor(): string;
    get textColor(): string;
    get classNames(): string[];
    get extendedProps(): Dictionary;
    toPlainObject(settings?: {
        collapseExtendedProps?: boolean;
        collapseColor?: boolean;
    }): Dictionary;
    toJSON(): Dictionary;
}
declare function buildEventApis(eventStore: EventStore, context: CalendarContext, excludeInstance?: EventInstance): EventImpl[];

declare class CalendarNowManager {
    private dateEnv?;
    private resetListeners;
    private nowAnchorDate?;
    private nowAnchorQueried?;
    private nowFn?;
    handleInput(dateEnv: DateEnv, // will change if timezone setup changed
    nowInput: DateInput | (() => DateInput)): void;
    getDateMarker(): DateMarker;
    addResetListener(handler: () => void): void;
    removeResetListener(handler: () => void): void;
}

interface DateProfile {
    currentDate: DateMarker;
    isValid: boolean;
    validRange: OpenDateRange;
    renderRange: DateRange;
    activeRange: DateRange | null;
    currentRange: DateRange;
    currentRangeUnit: string;
    isRangeAllDay: boolean;
    dateIncrement: Duration;
    slotMinTime: Duration;
    slotMaxTime: Duration;
}
interface DateProfileGeneratorProps extends DateProfileOptions {
    dateProfileGeneratorClass: DateProfileGeneratorClass;
    nowManager: CalendarNowManager;
    duration: Duration;
    durationUnit: string;
    usesMinMaxTime: boolean;
    dateEnv: DateEnv;
    calendarApi: CalendarImpl;
}
interface DateProfileOptions {
    slotMinTime: Duration;
    slotMaxTime: Duration;
    showNonCurrentDates?: boolean;
    dayCount?: number;
    dateAlignment?: string;
    dateIncrement?: Duration;
    hiddenDays?: number[];
    weekends?: boolean;
    validRangeInput?: DateRangeInput | ((this: CalendarImpl, nowDate: Date) => DateRangeInput);
    visibleRangeInput?: DateRangeInput | ((this: CalendarImpl, nowDate: Date) => DateRangeInput);
    fixedWeekCount?: boolean;
}
type DateProfileGeneratorClass = {
    new (props: DateProfileGeneratorProps): DateProfileGenerator;
};
declare class DateProfileGenerator {
    protected props: DateProfileGeneratorProps;
    isHiddenDayHash: boolean[];
    constructor(props: DateProfileGeneratorProps);
    buildPrev(currentDateProfile: DateProfile, currentDate: DateMarker, forceToValid?: boolean): DateProfile;
    buildNext(currentDateProfile: DateProfile, currentDate: DateMarker, forceToValid?: boolean): DateProfile;
    build(currentDate: DateMarker, direction?: any, forceToValid?: boolean): DateProfile;
    buildValidRange(): OpenDateRange;
    buildCurrentRangeInfo(date: DateMarker, direction: any): {
        duration: any;
        unit: any;
        range: any;
    };
    getFallbackDuration(): Duration;
    adjustActiveRange(range: DateRange): {
        start: Date;
        end: Date;
    };
    buildRangeFromDuration(date: DateMarker, direction: any, duration: Duration, unit: any): any;
    buildRangeFromDayCount(date: DateMarker, direction: any, dayCount: any): {
        start: Date;
        end: Date;
    };
    buildCustomVisibleRange(date: DateMarker): DateRange;
    buildRenderRange(currentRange: DateRange, currentRangeUnit: any, isRangeAllDay: any): DateRange;
    buildDateIncrement(fallback: any): Duration;
    refineRange(rangeInput: DateRangeInput | undefined): DateRange | null;
    initHiddenDays(): void;
    trimHiddenDays(range: DateRange): DateRange | null;
    isHiddenDay(day: any): boolean;
    skipHiddenDays(date: DateMarker, inc?: number, isExclusive?: boolean): Date;
}

interface EventInteractionState {
    affectedEvents: EventStore;
    mutatedEvents: EventStore;
    isEvent: boolean;
}

interface ViewProps {
    dateProfile: DateProfile;
    businessHours: EventStore;
    eventStore: EventStore;
    eventUiBases: EventUiHash;
    dateSelection: DateSpan | null;
    eventSelection: string;
    eventDrag: EventInteractionState | null;
    eventResize: EventInteractionState | null;
    isHeightAuto: boolean;
    forPrint: boolean;
}
declare function sliceEvents(props: ViewProps & {
    dateProfile: DateProfile;
    nextDayThreshold: Duration;
}, allDay?: boolean): EventRenderRange[];

type ClassNamesInput = string | string[];
declare function parseClassNames(raw: ClassNamesInput): string[];

type MountArg<ContentArg> = ContentArg & {
    el: HTMLElement;
};
type DidMountHandler<TheMountArg extends {
    el: HTMLElement;
}> = (mountArg: TheMountArg) => void;
type WillUnmountHandler<TheMountArg extends {
    el: HTMLElement;
}> = (mountArg: TheMountArg) => void;
interface ObjCustomContent {
    html: string;
    domNodes: any[];
}
type CustomContent = ComponentChildren | ObjCustomContent;
type CustomContentGenerator<RenderProps> = CustomContent | ((renderProps: RenderProps, createElement: any) => (CustomContent | true));
type ClassNamesGenerator<RenderProps> = ClassNamesInput | ((renderProps: RenderProps) => ClassNamesInput);

type ViewComponentType = ComponentType<ViewProps>;
type ViewConfigInput = ViewComponentType | ViewOptions;
type ViewConfigInputHash = {
    [viewType: string]: ViewConfigInput;
};
interface SpecificViewContentArg extends ViewProps {
    nextDayThreshold: Duration;
}
type SpecificViewMountArg = MountArg<SpecificViewContentArg>;

interface ViewSpec {
    type: string;
    component: ViewComponentType;
    duration: Duration;
    durationUnit: string;
    singleUnit: string;
    optionDefaults: ViewOptions;
    optionOverrides: ViewOptions;
    buttonTextOverride: string;
    buttonTextDefault: string;
    buttonTitleOverride: string | ((...args: any[]) => string);
    buttonTitleDefault: string | ((...args: any[]) => string);
}
type ViewSpecHash = {
    [viewType: string]: ViewSpec;
};

interface HandlerFuncTypeHash {
    [eventName: string]: (...args: any[]) => any;
}
declare class Emitter<HandlerFuncs extends HandlerFuncTypeHash> {
    private handlers;
    private options;
    private thisContext;
    setThisContext(thisContext: any): void;
    setOptions(options: Partial<HandlerFuncs>): void;
    on<Prop extends keyof HandlerFuncs>(type: Prop, handler: HandlerFuncs[Prop]): void;
    off<Prop extends keyof HandlerFuncs>(type: Prop, handler?: HandlerFuncs[Prop]): void;
    trigger<Prop extends keyof HandlerFuncs>(type: Prop, ...args: Parameters<HandlerFuncs[Prop]>): void;
    hasHandlers(type: keyof HandlerFuncs): boolean;
}

declare class ViewImpl implements ViewApi {
    type: string;
    private getCurrentData;
    private dateEnv;
    constructor(type: string, getCurrentData: () => CalendarData, dateEnv: DateEnv);
    get calendar(): CalendarApi;
    get title(): string;
    get activeStart(): Date;
    get activeEnd(): Date;
    get currentStart(): Date;
    get currentEnd(): Date;
    getOption(name: string): any;
}

declare class Theme {
    classes: any;
    iconClasses: any;
    rtlIconClasses: any;
    baseIconClass: string;
    iconOverrideOption: any;
    iconOverrideCustomButtonOption: any;
    iconOverridePrefix: string;
    constructor(calendarOptions: CalendarOptionsRefined);
    setIconOverride(iconOverrideHash: any): void;
    applyIconOverridePrefix(className: any): any;
    getClass(key: any): any;
    getIconClass(buttonName: any, isRtl?: boolean): string;
    getCustomButtonIconClass(customButtonProps: any): string;
}
type ThemeClass = {
    new (calendarOptions: any): Theme;
};

interface CalendarDataManagerState {
    dynamicOptionOverrides: CalendarOptions;
    currentViewType: string;
    currentDate: DateMarker;
    dateProfile: DateProfile;
    businessHours: EventStore;
    eventSources: EventSourceHash;
    eventUiBases: EventUiHash;
    eventStore: EventStore;
    renderableEventStore: EventStore;
    dateSelection: DateSpan | null;
    eventSelection: string;
    eventDrag: EventInteractionState | null;
    eventResize: EventInteractionState | null;
    selectionConfig: EventUi;
}
interface CalendarOptionsData {
    localeDefaults: CalendarOptions;
    calendarOptions: CalendarOptionsRefined;
    toolbarConfig: any;
    availableRawLocales: any;
    dateEnv: DateEnv;
    theme: Theme;
    pluginHooks: PluginHooks;
    viewSpecs: ViewSpecHash;
}
interface CalendarCurrentViewData {
    viewSpec: ViewSpec;
    options: ViewOptionsRefined;
    viewApi: ViewImpl;
    dateProfileGenerator: DateProfileGenerator;
}
type CalendarDataBase = CalendarOptionsData & CalendarCurrentViewData & CalendarDataManagerState;
interface CalendarData extends CalendarDataBase {
    nowManager: CalendarNowManager;
    viewTitle: string;
    calendarApi: CalendarImpl;
    dispatch: (action: Action) => void;
    emitter: Emitter<CalendarListeners>;
    getCurrentData(): CalendarData;
}

declare class CalendarImpl implements CalendarApi {
    currentDataManager?: CalendarDataManager;
    getCurrentData(): CalendarData;
    dispatch(action: Action): void;
    get view(): ViewImpl;
    batchRendering(callback: () => void): void;
    updateSize(): void;
    setOption<OptionName extends keyof CalendarOptions>(name: OptionName, val: CalendarOptions[OptionName]): void;
    getOption<OptionName extends keyof CalendarOptions>(name: OptionName): CalendarOptions[OptionName];
    getAvailableLocaleCodes(): string[];
    on<ListenerName extends keyof CalendarListeners>(handlerName: ListenerName, handler: CalendarListeners[ListenerName]): void;
    off<ListenerName extends keyof CalendarListeners>(handlerName: ListenerName, handler: CalendarListeners[ListenerName]): void;
    trigger<ListenerName extends keyof CalendarListeners>(handlerName: ListenerName, ...args: Parameters<CalendarListeners[ListenerName]>): void;
    changeView(viewType: string, dateOrRange?: DateRangeInput | DateInput): void;
    zoomTo(dateMarker: Date, viewType?: string): void;
    private getUnitViewSpec;
    prev(): void;
    next(): void;
    prevYear(): void;
    nextYear(): void;
    today(): void;
    gotoDate(zonedDateInput: DateInput): void;
    incrementDate(deltaInput: DurationInput): void;
    getDate(): Date;
    formatDate(d: DateInput, formatter: FormatterInput): string;
    formatRange(d0: DateInput, d1: DateInput, settings: any): string;
    formatIso(d: DateInput, omitTime?: boolean): string;
    select(dateOrObj: DateInput | any, endDate?: DateInput): void;
    unselect(pev?: PointerDragEvent): void;
    addEvent(eventInput: EventInput, sourceInput?: EventSourceImpl | string | boolean): EventImpl | null;
    private triggerEventAdd;
    getEventById(id: string): EventImpl | null;
    getEvents(): EventImpl[];
    removeAllEvents(): void;
    getEventSources(): EventSourceImpl[];
    getEventSourceById(id: string): EventSourceImpl | null;
    addEventSource(sourceInput: EventSourceInput): EventSourceImpl;
    removeAllEventSources(): void;
    refetchEvents(): void;
    scrollToTime(timeInput: DurationInput): void;
}

type EventSourceSuccessResponseHandler = (this: CalendarImpl, rawData: any, response: any) => EventInput[] | void;
type EventSourceErrorResponseHandler = (error: Error) => void;
interface EventSource<Meta> {
    _raw: any;
    sourceId: string;
    sourceDefId: number;
    meta: Meta;
    publicId: string;
    isFetching: boolean;
    latestFetchId: string;
    fetchRange: DateRange | null;
    defaultAllDay: boolean | null;
    eventDataTransform: EventInputTransformer;
    ui: EventUi;
    success: EventSourceSuccessResponseHandler | null;
    failure: EventSourceErrorResponseHandler | null;
    extendedProps: Dictionary;
}
type EventSourceHash = {
    [sourceId: string]: EventSource<any>;
};
interface EventSourceFetcherRes {
    rawEvents: EventInput[];
    response?: Response;
}
type EventSourceFetcher<Meta> = (arg: {
    eventSource: EventSource<Meta>;
    range: DateRange;
    isRefetch: boolean;
    context: CalendarContext;
}, successCallback: (res: EventSourceFetcherRes) => void, errorCallback: (error: Error) => void) => void;

type Action = {
    type: 'NOTHING';
} | // hack
{
    type: 'SET_OPTION';
    optionName: string;
    rawOptionValue: any;
} | // TODO: how to link this to CalendarOptions?
{
    type: 'PREV';
} | {
    type: 'NEXT';
} | {
    type: 'CHANGE_DATE';
    dateMarker: DateMarker;
} | {
    type: 'CHANGE_VIEW_TYPE';
    viewType: string;
    dateMarker?: DateMarker;
} | {
    type: 'SELECT_DATES';
    selection: DateSpan;
} | {
    type: 'UNSELECT_DATES';
} | {
    type: 'SELECT_EVENT';
    eventInstanceId: string;
} | {
    type: 'UNSELECT_EVENT';
} | {
    type: 'SET_EVENT_DRAG';
    state: EventInteractionState;
} | {
    type: 'UNSET_EVENT_DRAG';
} | {
    type: 'SET_EVENT_RESIZE';
    state: EventInteractionState;
} | {
    type: 'UNSET_EVENT_RESIZE';
} | {
    type: 'ADD_EVENT_SOURCES';
    sources: EventSource<any>[];
} | {
    type: 'REMOVE_EVENT_SOURCE';
    sourceId: string;
} | {
    type: 'REMOVE_ALL_EVENT_SOURCES';
} | {
    type: 'FETCH_EVENT_SOURCES';
    sourceIds?: string[];
    isRefetch?: boolean;
} | // if no sourceIds, fetch all
{
    type: 'RECEIVE_EVENTS';
    sourceId: string;
    fetchId: string;
    fetchRange: DateRange | null;
    rawEvents: EventInput[];
} | {
    type: 'RECEIVE_EVENT_ERROR';
    sourceId: string;
    fetchId: string;
    fetchRange: DateRange | null;
    error: Error;
} | // need all these?
{
    type: 'ADD_EVENTS';
    eventStore: EventStore;
} | {
    type: 'RESET_EVENTS';
    eventStore: EventStore;
} | {
    type: 'RESET_RAW_EVENTS';
    rawEvents: EventInput[];
    sourceId: string;
} | {
    type: 'MERGE_EVENTS';
    eventStore: EventStore;
} | {
    type: 'REMOVE_EVENTS';
    eventStore: EventStore;
} | {
    type: 'REMOVE_ALL_EVENTS';
};

interface CalendarDataManagerProps {
    optionOverrides: CalendarOptions;
    calendarApi: CalendarImpl;
    onAction?: (action: Action) => void;
    onData?: (data: CalendarData) => void;
}
type ReducerFunc = (// TODO: rename to CalendarDataInjector. move view-props-manip hook here as well?
currentState: Dictionary | null, action: Action | null, context: CalendarContext & CalendarDataManagerState) => Dictionary;
declare class CalendarDataManager {
    private computeCurrentViewData;
    private organizeRawLocales;
    private buildLocale;
    private buildPluginHooks;
    private buildDateEnv;
    private buildTheme;
    private parseToolbars;
    private buildViewSpecs;
    private buildDateProfileGenerator;
    private buildViewApi;
    private buildViewUiProps;
    private buildEventUiBySource;
    private buildEventUiBases;
    private parseContextBusinessHours;
    private buildTitle;
    private nowManager;
    emitter: Emitter<Required<RefinedOptionsFromRefiners<Required<CalendarListenerRefiners>>>>;
    private actionRunner;
    private props;
    private state;
    private data;
    currentCalendarOptionsInput: CalendarOptions;
    private currentCalendarOptionsRefined;
    private currentViewOptionsInput;
    private currentViewOptionsRefined;
    currentCalendarOptionsRefiners: any;
    private stableOptionOverrides;
    private stableDynamicOptionOverrides;
    private stableCalendarOptionsData;
    private optionsForRefining;
    private optionsForHandling;
    constructor(props: CalendarDataManagerProps);
    getCurrentData: () => CalendarData;
    dispatch: (action: Action) => void;
    resetOptions(optionOverrides: CalendarOptions, changedOptionNames?: string[]): void;
    _handleAction(action: Action): void;
    updateData(): void;
    computeOptionsData(optionOverrides: CalendarOptions, dynamicOptionOverrides: CalendarOptions, calendarApi: CalendarImpl): CalendarOptionsData;
    processRawCalendarOptions(optionOverrides: CalendarOptions, dynamicOptionOverrides: CalendarOptions): {
        rawOptions: CalendarOptions;
        refinedOptions: CalendarOptionsRefined;
        pluginHooks: PluginHooks;
        availableLocaleData: RawLocaleInfo;
        localeDefaults: CalendarOptionsRefined;
        extra: {};
    };
    _computeCurrentViewData(viewType: string, optionsData: CalendarOptionsData, optionOverrides: CalendarOptions, dynamicOptionOverrides: CalendarOptions): CalendarCurrentViewData;
    processRawViewOptions(viewSpec: ViewSpec, pluginHooks: PluginHooks, localeDefaults: CalendarOptions, optionOverrides: CalendarOptions, dynamicOptionOverrides: CalendarOptions): {
        rawOptions: ViewOptions;
        refinedOptions: ViewOptionsRefined;
        extra: {};
    };
}

interface DateSelectionApi extends DateSpanApi {
    jsEvent: UIEvent;
    view: ViewApi;
}
type DatePointTransform = (dateSpan: DateSpan, context: CalendarContext) => any;
type DateSpanTransform = (dateSpan: DateSpan, context: CalendarContext) => any;
type CalendarInteraction = {
    destroy: () => void;
};
type CalendarInteractionClass = {
    new (context: CalendarContext): CalendarInteraction;
};
type OptionChangeHandler = (propValue: any, context: CalendarContext) => void;
type OptionChangeHandlerMap = {
    [propName: string]: OptionChangeHandler;
};
interface DateSelectArg extends DateSpanApi {
    jsEvent: MouseEvent | null;
    view: ViewApi;
}
declare function triggerDateSelect(selection: DateSpan, pev: PointerDragEvent | null, context: CalendarContext & {
    viewApi?: ViewImpl;
}): void;
interface DateUnselectArg {
    jsEvent: MouseEvent;
    view: ViewApi;
}
declare function getDefaultEventEnd(allDay: boolean, marker: DateMarker, context: CalendarContext): DateMarker;

interface ScrollRequest {
    time?: Duration;
    [otherProp: string]: any;
}
type ScrollRequestHandler = (request: ScrollRequest) => boolean;
declare class ScrollResponder {
    private execFunc;
    private emitter;
    private scrollTime;
    private scrollTimeReset;
    queuedRequest: ScrollRequest;
    constructor(execFunc: ScrollRequestHandler, emitter: Emitter<CalendarListeners>, scrollTime: Duration, scrollTimeReset: boolean);
    detach(): void;
    update(isDatesNew: boolean): void;
    private fireInitialScroll;
    private handleScrollRequest;
    private drain;
}

declare const ViewContextType: Context<any>;
type ResizeHandler = (force: boolean) => void;
interface ViewContext extends CalendarContext {
    options: ViewOptionsRefined;
    theme: Theme;
    isRtl: boolean;
    dateProfileGenerator: DateProfileGenerator;
    viewSpec: ViewSpec;
    viewApi: ViewImpl;
    addResizeHandler: (handler: ResizeHandler) => void;
    removeResizeHandler: (handler: ResizeHandler) => void;
    createScrollResponder: (execFunc: ScrollRequestHandler) => ScrollResponder;
    registerInteractiveComponent: (component: DateComponent<any>, settingsInput: InteractionSettingsInput) => void;
    unregisterInteractiveComponent: (component: DateComponent<any>) => void;
}

declare function filterHash(hash: any, func: any): {};
declare function mapHash<InputItem, OutputItem>(hash: {
    [key: string]: InputItem;
}, func: (input: InputItem, key: string) => OutputItem): {
    [key: string]: OutputItem;
};
declare function isPropsEqual(obj0: any, obj1: any): boolean;
type EqualityFunc<T> = (a: T, b: T) => boolean;
type EqualityThing<T> = EqualityFunc<T> | true;
type EqualityFuncs<ObjType> = {
    [K in keyof ObjType]?: EqualityThing<ObjType[K]>;
};
declare function compareObjs(oldProps: any, newProps: any, equalityFuncs?: EqualityFuncs<any>): boolean;
declare function collectFromHash<Item>(hash: {
    [key: string]: Item;
}, startIndex?: number, endIndex?: number, step?: number): Item[];

declare abstract class PureComponent<Props = Dictionary, State = Dictionary> extends Component<Props, State> {
    static addPropsEquality: typeof addPropsEquality;
    static addStateEquality: typeof addStateEquality;
    static contextType: any;
    context: ViewContext;
    propEquality: EqualityFuncs<Props>;
    stateEquality: EqualityFuncs<State>;
    shouldComponentUpdate(nextProps: Props, nextState: State): boolean;
    safeSetState(newState: Partial<State>): void;
}
declare abstract class BaseComponent<Props = Dictionary, State = Dictionary> extends PureComponent<Props, State> {
    static contextType: any;
    context: ViewContext;
}
declare function addPropsEquality(this: {
    prototype: {
        propEquality: any;
    };
}, propEquality: any): void;
declare function addStateEquality(this: {
    prototype: {
        stateEquality: any;
    };
}, stateEquality: any): void;
declare function setRef<RefType>(ref: Ref<RefType> | void, current: RefType): void;

interface Point {
    left: number;
    top: number;
}
interface Rect {
    left: number;
    right: number;
    top: number;
    bottom: number;
}
declare function pointInsideRect(point: Point, rect: Rect): boolean;
declare function intersectRects(rect1: Rect, rect2: Rect): Rect | false;
declare function translateRect(rect: Rect, deltaX: number, deltaY: number): Rect;
declare function constrainPoint(point: Point, rect: Rect): Point;
declare function getRectCenter(rect: Rect): Point;
declare function diffPoints(point1: Point, point2: Point): Point;

interface Hit {
    componentId?: string;
    context?: ViewContext;
    dateProfile: DateProfile;
    dateSpan: DateSpan;
    dayEl: HTMLElement;
    rect: Rect;
    layer: number;
    largeUnit?: string;
}

interface Seg {
    component?: DateComponent<any, any>;
    isStart: boolean;
    isEnd: boolean;
    eventRange?: EventRenderRange;
    [otherProp: string]: any;
    el?: never;
}
interface EventSegUiInteractionState {
    affectedInstances: EventInstanceHash;
    segs: Seg[];
    isEvent: boolean;
}
declare abstract class DateComponent<Props = Dictionary, State = Dictionary> extends BaseComponent<Props, State> {
    uid: string;
    prepareHits(): void;
    queryHit(positionLeft: number, positionTop: number, elWidth: number, elHeight: number): Hit | null;
    isValidSegDownEl(el: HTMLElement): boolean;
    isValidDateDownEl(el: HTMLElement): boolean;
}

declare abstract class Interaction {
    component: DateComponent<any>;
    isHitComboAllowed: ((hit0: Hit, hit1: Hit) => boolean) | null;
    constructor(settings: InteractionSettings);
    destroy(): void;
}
type InteractionClass = {
    new (settings: InteractionSettings): Interaction;
};
interface InteractionSettingsInput {
    el: HTMLElement;
    useEventCenter?: boolean;
    isHitComboAllowed?: (hit0: Hit, hit1: Hit) => boolean;
}
interface InteractionSettings {
    component: DateComponent<any>;
    el: HTMLElement;
    useEventCenter: boolean;
    isHitComboAllowed: ((hit0: Hit, hit1: Hit) => boolean) | null;
}
type InteractionSettingsStore = {
    [componenUid: string]: InteractionSettings;
};
declare function interactionSettingsToStore(settings: InteractionSettings): {
    [x: string]: InteractionSettings;
};
declare const interactionSettingsStore: InteractionSettingsStore;

declare class DelayedRunner {
    private drainedOption?;
    private isRunning;
    private isDirty;
    private pauseDepths;
    private timeoutId;
    constructor(drainedOption?: () => void);
    request(delay?: number): void;
    pause(scope?: string): void;
    resume(scope?: string, force?: boolean): void;
    isPaused(): number;
    tryDrain(): void;
    clear(): void;
    private clearTimeout;
    protected drained(): void;
}

interface CalendarContentProps extends CalendarData {
    forPrint: boolean;
    isHeightAuto: boolean;
}

type eventDragMutationMassager = (mutation: EventMutation, hit0: Hit, hit1: Hit) => void;
type EventDropTransformers = (mutation: EventMutation, context: CalendarContext) => Dictionary;
type eventIsDraggableTransformer = (val: boolean, eventDef: EventDef, eventUi: EventUi, context: CalendarContext) => boolean;

type dateSelectionJoinTransformer = (hit0: Hit, hit1: Hit) => any;

declare const DRAG_META_REFINERS: {
    startTime: typeof createDuration;
    duration: typeof createDuration;
    create: BooleanConstructor;
    sourceId: StringConstructor;
};
type DragMetaInput = RawOptionsFromRefiners<typeof DRAG_META_REFINERS> & {
    [otherProp: string]: any;
};
interface DragMeta {
    startTime: Duration | null;
    duration: Duration | null;
    create: boolean;
    sourceId: string;
    leftoverProps: Dictionary;
}
declare function parseDragMeta(raw: DragMetaInput): DragMeta;

type ExternalDefTransform = (dateSpan: DateSpan, dragMeta: DragMeta) => any;

type EventSourceFuncArg = {
    start: Date;
    end: Date;
    startStr: string;
    endStr: string;
    timeZone: string;
};
type EventSourceFunc = ((arg: EventSourceFuncArg, successCallback: (eventInputs: EventInput[]) => void, failureCallback: (error: Error) => void) => void) | ((arg: EventSourceFuncArg) => Promise<EventInput[]>);

declare const JSON_FEED_EVENT_SOURCE_REFINERS: {
    method: StringConstructor;
    extraParams: Identity<Dictionary | (() => Dictionary)>;
    startParam: StringConstructor;
    endParam: StringConstructor;
    timeZoneParam: StringConstructor;
};

declare const EVENT_SOURCE_REFINERS: {
    id: StringConstructor;
    defaultAllDay: BooleanConstructor;
    url: StringConstructor;
    format: StringConstructor;
    events: Identity<EventInput[] | EventSourceFunc>;
    eventDataTransform: Identity<EventInputTransformer>;
    success: Identity<EventSourceSuccessResponseHandler>;
    failure: Identity<EventSourceErrorResponseHandler>;
};
type BuiltInEventSourceRefiners = typeof EVENT_SOURCE_REFINERS & typeof JSON_FEED_EVENT_SOURCE_REFINERS;
interface EventSourceRefiners extends BuiltInEventSourceRefiners {
}
type EventSourceInputObject = EventUiInput & RawOptionsFromRefiners<Required<EventSourceRefiners>>;
type EventSourceInput = EventSourceInputObject | // object in extended form
EventInput[] | EventSourceFunc | // just a function
string;
type EventSourceRefined = EventUiRefined & RefinedOptionsFromRefiners<Required<EventSourceRefiners>>;

interface EventSourceDef<Meta> {
    ignoreRange?: boolean;
    parseMeta: (refined: EventSourceRefined) => Meta | null;
    fetch: EventSourceFetcher<Meta>;
}

interface ParsedRecurring<RecurringData> {
    typeData: RecurringData;
    allDayGuess: boolean | null;
    duration: Duration | null;
}
interface RecurringType<RecurringData> {
    parse: (refined: EventRefined, dateEnv: DateEnv) => ParsedRecurring<RecurringData> | null;
    expand: (typeData: any, framingRange: DateRange, dateEnv: DateEnv) => DateMarker[];
}

declare abstract class ElementDragging {
    emitter: Emitter<any>;
    constructor(el: HTMLElement, selector?: string);
    destroy(): void;
    abstract setIgnoreMove(bool: boolean): void;
    setMirrorIsVisible(bool: boolean): void;
    setMirrorNeedsRevert(bool: boolean): void;
    setAutoScrollEnabled(bool: boolean): void;
}
type ElementDraggingClass = {
    new (el: HTMLElement, selector?: string): ElementDragging;
};

type CssDimValue = string | number;
interface ColProps {
    width?: CssDimValue;
    minWidth?: CssDimValue;
    span?: number;
}
interface SectionConfig {
    outerContent?: VNode;
    type: 'body' | 'header' | 'footer';
    className?: string;
    maxHeight?: number;
    liquid?: boolean;
    expandRows?: boolean;
    syncRowHeights?: boolean;
    isSticky?: boolean;
}
type ChunkConfigContent = (contentProps: ChunkContentCallbackArgs) => VNode;
type ChunkConfigRowContent = VNode | ChunkConfigContent;
interface ChunkConfig {
    elRef?: Ref<HTMLTableCellElement>;
    outerContent?: VNode;
    content?: ChunkConfigContent;
    rowContent?: ChunkConfigRowContent;
    scrollerElRef?: Ref<HTMLDivElement>;
    tableClassName?: string;
}
interface ChunkContentCallbackArgs {
    tableColGroupNode: VNode;
    tableMinWidth: CssDimValue;
    clientWidth: number | null;
    clientHeight: number | null;
    expandRows: boolean;
    syncRowHeights: boolean;
    rowSyncHeights: number[];
    reportRowHeightChange: (rowEl: HTMLElement, isStable: boolean) => void;
}
declare function computeShrinkWidth(chunkEls: HTMLElement[]): number;
interface ScrollerLike {
    needsYScrolling(): boolean;
    needsXScrolling(): boolean;
}
declare function getSectionHasLiquidHeight(props: {
    liquid: boolean;
}, sectionConfig: SectionConfig): boolean;
declare function getAllowYScrolling(props: {
    liquid: boolean;
}, sectionConfig: SectionConfig): boolean;
declare function renderChunkContent(sectionConfig: SectionConfig, chunkConfig: ChunkConfig, arg: ChunkContentCallbackArgs, isHeader: boolean): VNode<{}>;
declare function isColPropsEqual(cols0: ColProps[], cols1: ColProps[]): boolean;
declare function renderMicroColGroup(cols: ColProps[], shrinkWidth?: number): VNode;
declare function sanitizeShrinkWidth(shrinkWidth?: number): number;
declare function hasShrinkWidth(cols: ColProps[]): boolean;
declare function getScrollGridClassNames(liquid: boolean, context: ViewContext): any[];
declare function getSectionClassNames(sectionConfig: SectionConfig, wholeTableVGrow: boolean): string[];
declare function renderScrollShim(arg: ChunkContentCallbackArgs): createElement.JSX.Element;
declare function getStickyHeaderDates(options: BaseOptionsRefined): boolean;
declare function getStickyFooterScrollbar(options: BaseOptionsRefined): boolean;

interface ScrollGridProps {
    elRef?: Ref<any>;
    colGroups?: ColGroupConfig[];
    sections: ScrollGridSectionConfig[];
    liquid: boolean;
    forPrint: boolean;
    collapsibleWidth: boolean;
}
interface ScrollGridSectionConfig extends SectionConfig {
    key: string;
    chunks?: ScrollGridChunkConfig[];
}
interface ScrollGridChunkConfig extends ChunkConfig {
    key: string;
}
interface ColGroupConfig {
    width?: CssDimValue;
    cols: ColProps[];
}
type ScrollGridImpl = {
    new (props: ScrollGridProps, context: ViewContext): Component<ScrollGridProps>;
};

interface PluginDefInput {
    name: string;
    premiumReleaseDate?: string;
    deps?: PluginDef[];
    reducers?: ReducerFunc[];
    isLoadingFuncs?: ((state: Dictionary) => boolean)[];
    contextInit?: (context: CalendarContext) => void;
    eventRefiners?: GenericRefiners;
    eventDefMemberAdders?: EventDefMemberAdder[];
    eventSourceRefiners?: GenericRefiners;
    isDraggableTransformers?: eventIsDraggableTransformer[];
    eventDragMutationMassagers?: eventDragMutationMassager[];
    eventDefMutationAppliers?: eventDefMutationApplier[];
    dateSelectionTransformers?: dateSelectionJoinTransformer[];
    datePointTransforms?: DatePointTransform[];
    dateSpanTransforms?: DateSpanTransform[];
    views?: ViewConfigInputHash;
    viewPropsTransformers?: ViewPropsTransformerClass[];
    isPropsValid?: isPropsValidTester;
    externalDefTransforms?: ExternalDefTransform[];
    viewContainerAppends?: ViewContainerAppend[];
    eventDropTransformers?: EventDropTransformers[];
    componentInteractions?: InteractionClass[];
    calendarInteractions?: CalendarInteractionClass[];
    themeClasses?: {
        [themeSystemName: string]: ThemeClass;
    };
    eventSourceDefs?: EventSourceDef<any>[];
    cmdFormatter?: CmdFormatterFunc;
    recurringTypes?: RecurringType<any>[];
    namedTimeZonedImpl?: NamedTimeZoneImplClass;
    initialView?: string;
    elementDraggingImpl?: ElementDraggingClass;
    optionChangeHandlers?: OptionChangeHandlerMap;
    scrollGridImpl?: ScrollGridImpl;
    listenerRefiners?: GenericListenerRefiners;
    optionRefiners?: GenericRefiners;
    propSetHandlers?: {
        [propName: string]: (val: any, context: CalendarData) => void;
    };
}
interface PluginHooks {
    premiumReleaseDate: Date | undefined;
    reducers: ReducerFunc[];
    isLoadingFuncs: ((state: Dictionary) => boolean)[];
    contextInit: ((context: CalendarContext) => void)[];
    eventRefiners: GenericRefiners;
    eventDefMemberAdders: EventDefMemberAdder[];
    eventSourceRefiners: GenericRefiners;
    isDraggableTransformers: eventIsDraggableTransformer[];
    eventDragMutationMassagers: eventDragMutationMassager[];
    eventDefMutationAppliers: eventDefMutationApplier[];
    dateSelectionTransformers: dateSelectionJoinTransformer[];
    datePointTransforms: DatePointTransform[];
    dateSpanTransforms: DateSpanTransform[];
    views: ViewConfigInputHash;
    viewPropsTransformers: ViewPropsTransformerClass[];
    isPropsValid: isPropsValidTester | null;
    externalDefTransforms: ExternalDefTransform[];
    viewContainerAppends: ViewContainerAppend[];
    eventDropTransformers: EventDropTransformers[];
    componentInteractions: InteractionClass[];
    calendarInteractions: CalendarInteractionClass[];
    themeClasses: {
        [themeSystemName: string]: ThemeClass;
    };
    eventSourceDefs: EventSourceDef<any>[];
    cmdFormatter?: CmdFormatterFunc;
    recurringTypes: RecurringType<any>[];
    namedTimeZonedImpl?: NamedTimeZoneImplClass;
    initialView: string;
    elementDraggingImpl?: ElementDraggingClass;
    optionChangeHandlers: OptionChangeHandlerMap;
    scrollGridImpl: ScrollGridImpl | null;
    listenerRefiners: GenericListenerRefiners;
    optionRefiners: GenericRefiners;
    propSetHandlers: {
        [propName: string]: (val: any, context: CalendarData) => void;
    };
}
interface PluginDef extends PluginHooks {
    id: string;
    name: string;
    deps: PluginDef[];
}
type ViewPropsTransformerClass = new () => ViewPropsTransformer;
interface ViewPropsTransformer {
    transform(viewProps: ViewProps, calendarProps: CalendarContentProps): any;
}
type ViewContainerAppend = (context: CalendarContext) => ComponentChildren;

interface CalendarContext {
    nowManager: CalendarNowManager;
    dateEnv: DateEnv;
    options: BaseOptionsRefined;
    pluginHooks: PluginHooks;
    emitter: Emitter<CalendarListeners>;
    dispatch(action: Action): void;
    getCurrentData(): CalendarData;
    calendarApi: CalendarImpl;
}

type EventDefIdMap = {
    [publicId: string]: string;
};

declare const EVENT_REFINERS: {
    extendedProps: Identity<Dictionary>;
    start: Identity<DateInput>;
    end: Identity<DateInput>;
    date: Identity<DateInput>;
    allDay: BooleanConstructor;
    id: StringConstructor;
    groupId: StringConstructor;
    title: StringConstructor;
    url: StringConstructor;
    interactive: BooleanConstructor;
};
type BuiltInEventRefiners = typeof EVENT_REFINERS;
interface EventRefiners extends BuiltInEventRefiners {
}
type EventInput = EventUiInput & RawOptionsFromRefiners<Required<EventRefiners>> & // Required hack
{
    [extendedProp: string]: any;
};
type EventRefined = EventUiRefined & RefinedOptionsFromRefiners<Required<EventRefiners>>;
interface EventTuple {
    def: EventDef;
    instance: EventInstance | null;
}
type EventInputTransformer = (input: EventInput) => EventInput;
type EventDefMemberAdder = (refined: EventRefined) => Partial<EventDef>;
declare function refineEventDef(raw: EventInput, context: CalendarContext, refiners?: GenericRefiners): {
    refined: RefinedOptionsFromRefiners<GenericRefiners>;
    extra: Dictionary;
};
declare function parseEventDef(refined: EventRefined, extra: Dictionary, sourceId: string, allDay: boolean, hasEnd: boolean, context: CalendarContext, defIdMap?: EventDefIdMap): EventDef;

interface EventStore {
    defs: EventDefHash;
    instances: EventInstanceHash;
}
declare function eventTupleToStore(tuple: EventTuple, eventStore?: EventStore): EventStore;
declare function getRelevantEvents(eventStore: EventStore, instanceId: string): EventStore;
declare function createEmptyEventStore(): EventStore;
declare function mergeEventStores(store0: EventStore, store1: EventStore): EventStore;

interface SplittableProps {
    businessHours: EventStore | null;
    dateSelection: DateSpan | null;
    eventStore: EventStore;
    eventUiBases: EventUiHash;
    eventSelection: string;
    eventDrag: EventInteractionState | null;
    eventResize: EventInteractionState | null;
}
declare abstract class Splitter<PropsType extends SplittableProps = SplittableProps> {
    private getKeysForEventDefs;
    private splitDateSelection;
    private splitEventStore;
    private splitIndividualUi;
    private splitEventDrag;
    private splitEventResize;
    private eventUiBuilders;
    abstract getKeyInfo(props: PropsType): {
        [key: string]: {
            ui?: EventUi;
            businessHours?: EventStore;
        };
    };
    abstract getKeysForDateSpan(dateSpan: DateSpan): string[];
    abstract getKeysForEventDef(eventDef: EventDef): string[];
    splitProps(props: PropsType): {
        [key: string]: SplittableProps;
    };
    private _splitDateSpan;
    private _getKeysForEventDefs;
    private _splitEventStore;
    private _splitIndividualUi;
    private _splitInteraction;
}

type ConstraintInput = 'businessHours' | string | EventInput | EventInput[];
type Constraint = 'businessHours' | string | EventStore | false;
type OverlapFunc = ((stillEvent: EventImpl, movingEvent: EventImpl | null) => boolean);
type AllowFunc = (span: DateSpanApi, movingEvent: EventImpl | null) => boolean;
type isPropsValidTester = (props: SplittableProps, context: CalendarContext) => boolean;

declare const EVENT_UI_REFINERS: {
    display: StringConstructor;
    editable: BooleanConstructor;
    startEditable: BooleanConstructor;
    durationEditable: BooleanConstructor;
    constraint: Identity<any>;
    overlap: Identity<boolean>;
    allow: Identity<AllowFunc>;
    className: typeof parseClassNames;
    classNames: typeof parseClassNames;
    color: StringConstructor;
    backgroundColor: StringConstructor;
    borderColor: StringConstructor;
    textColor: StringConstructor;
};
type BuiltInEventUiRefiners = typeof EVENT_UI_REFINERS;
interface EventUiRefiners extends BuiltInEventUiRefiners {
}
type EventUiInput = RawOptionsFromRefiners<Required<EventUiRefiners>>;
type EventUiRefined = RefinedOptionsFromRefiners<Required<EventUiRefiners>>;
interface EventUi {
    display: string | null;
    startEditable: boolean | null;
    durationEditable: boolean | null;
    constraints: Constraint[];
    overlap: boolean | null;
    allows: AllowFunc[];
    backgroundColor: string;
    borderColor: string;
    textColor: string;
    classNames: string[];
}
type EventUiHash = {
    [defId: string]: EventUi;
};
declare function createEventUi(refined: EventUiRefined, context: CalendarContext): EventUi;
declare function combineEventUis(uis: EventUi[]): EventUi;

interface EventDef {
    defId: string;
    sourceId: string;
    publicId: string;
    groupId: string;
    allDay: boolean;
    hasEnd: boolean;
    recurringDef: {
        typeId: number;
        typeData: any;
        duration: Duration | null;
    } | null;
    title: string;
    url: string;
    ui: EventUi;
    interactive?: boolean;
    extendedProps: Dictionary;
}
type EventDefHash = {
    [defId: string]: EventDef;
};

interface EventRenderRange extends EventTuple {
    ui: EventUi;
    range: DateRange;
    isStart: boolean;
    isEnd: boolean;
}
declare function sliceEventStore(eventStore: EventStore, eventUiBases: EventUiHash, framingRange: DateRange, nextDayThreshold?: Duration): {
    bg: EventRenderRange[];
    fg: EventRenderRange[];
};
declare function hasBgRendering(def: EventDef): boolean;
declare function getElSeg(el: HTMLElement): Seg | null;
declare function sortEventSegs(segs: any, eventOrderSpecs: OrderSpec<EventImpl>[]): Seg[];
interface EventContentArg {
    event: EventImpl;
    timeText: string;
    backgroundColor: string;
    borderColor: string;
    textColor: string;
    isDraggable: boolean;
    isStartResizable: boolean;
    isEndResizable: boolean;
    isMirror: boolean;
    isStart: boolean;
    isEnd: boolean;
    isPast: boolean;
    isFuture: boolean;
    isToday: boolean;
    isSelected: boolean;
    isDragging: boolean;
    isResizing: boolean;
    view: ViewApi;
}
type EventMountArg = MountArg<EventContentArg>;
declare function buildSegTimeText(seg: Seg, timeFormat: DateFormatter, context: ViewContext, defaultDisplayEventTime?: boolean, // defaults to true
defaultDisplayEventEnd?: boolean, // defaults to true
startOverride?: DateMarker, endOverride?: DateMarker): string;
declare function getSegMeta(seg: Seg, todayRange: DateRange, nowDate?: DateMarker): {
    isPast: boolean;
    isFuture: boolean;
    isToday: boolean;
};
declare function buildEventRangeKey(eventRange: EventRenderRange): string;
declare function getSegAnchorAttrs(seg: Seg, context: ViewContext): {
    tabIndex: number;
    onKeyDown(ev: KeyboardEvent): void;
} | {
    href: string;
} | {
    href?: undefined;
};

interface OpenDateSpanInput {
    start?: DateInput;
    end?: DateInput;
    allDay?: boolean;
    [otherProp: string]: any;
}
interface DateSpanInput extends OpenDateSpanInput {
    start: DateInput;
    end: DateInput;
}
interface OpenDateSpan {
    range: OpenDateRange;
    allDay: boolean;
    [otherProp: string]: any;
}
interface DateSpan extends OpenDateSpan {
    range: DateRange;
}
interface RangeApi {
    start: Date;
    end: Date;
    startStr: string;
    endStr: string;
}
interface DateSpanApi extends RangeApi {
    allDay: boolean;
}
interface RangeApiWithTimeZone extends RangeApi {
    timeZone: string;
}
interface DatePointApi {
    date: Date;
    dateStr: string;
    allDay: boolean;
}
declare function isDateSpansEqual(span0: DateSpan, span1: DateSpan): boolean;

type BusinessHoursInput = boolean | EventInput | EventInput[];
declare function parseBusinessHours(input: BusinessHoursInput, context: CalendarContext): EventStore;

type ElRef = Ref<HTMLElement>;
type ElAttrs = JSX.HTMLAttributes & JSX.SVGAttributes & {
    ref?: ElRef;
} & Record<string, any>;
interface ElAttrsProps {
    elRef?: ElRef;
    elClasses?: string[];
    elStyle?: JSX.CSSProperties;
    elAttrs?: ElAttrs;
}
interface ElProps extends ElAttrsProps {
    elTag: string;
}
interface ContentGeneratorProps<RenderProps> {
    renderProps: RenderProps;
    generatorName: string | undefined;
    customGenerator?: CustomContentGenerator<RenderProps>;
    defaultGenerator?: (renderProps: RenderProps) => ComponentChild;
}
declare function buildElAttrs(props: ElAttrsProps, extraClassNames?: string[], elRef?: ElRef): ElAttrs;

type ContentContainerProps<RenderProps> = ElAttrsProps & ContentGeneratorProps<RenderProps> & {
    elTag?: string;
    classNameGenerator: ClassNamesGenerator<RenderProps> | undefined;
    didMount: ((renderProps: RenderProps & {
        el: HTMLElement;
    }) => void) | undefined;
    willUnmount: ((renderProps: RenderProps & {
        el: HTMLElement;
    }) => void) | undefined;
    children?: InnerContainerFunc<RenderProps>;
};
declare class ContentContainer<RenderProps> extends Component<ContentContainerProps<RenderProps>> {
    static contextType: preact.Context<number>;
    didMountMisfire?: boolean;
    context: number;
    el: HTMLElement;
    InnerContent: any;
    render(): ComponentChildren;
    handleEl: (el: HTMLElement) => void;
    componentDidMount(): void;
    componentWillUnmount(): void;
}
type InnerContainerComponent = FunctionalComponent<ElProps>;
type InnerContainerFunc<RenderProps> = (InnerContainer: InnerContainerComponent, renderProps: RenderProps, elAttrs: ElAttrs) => ComponentChildren;

interface NowIndicatorContainerProps extends Partial<ElProps> {
    isAxis: boolean;
    date: DateMarker;
    children?: InnerContainerFunc<NowIndicatorContentArg>;
}
interface NowIndicatorContentArg {
    isAxis: boolean;
    date: Date;
    view: ViewApi;
}
type NowIndicatorMountArg = MountArg<NowIndicatorContentArg>;
declare const NowIndicatorContainer: (props: NowIndicatorContainerProps) => createElement.JSX.Element;

interface WeekNumberContainerProps extends ElProps {
    date: DateMarker;
    defaultFormat: DateFormatter;
    children?: InnerContainerFunc<WeekNumberContentArg>;
}
interface WeekNumberContentArg {
    num: number;
    text: string;
    date: Date;
}
type WeekNumberMountArg = MountArg<WeekNumberContentArg>;
declare const WeekNumberContainer: (props: WeekNumberContainerProps) => createElement.JSX.Element;

interface MoreLinkContainerProps extends Partial<ElProps> {
    dateProfile: DateProfile;
    todayRange: DateRange;
    allDayDate: DateMarker | null;
    moreCnt: number;
    allSegs: Seg[];
    hiddenSegs: Seg[];
    extraDateSpan?: Dictionary;
    alignmentElRef?: RefObject<HTMLElement>;
    alignGridTop?: boolean;
    forceTimed?: boolean;
    popoverContent: () => ComponentChild;
    defaultGenerator?: (renderProps: MoreLinkContentArg) => ComponentChild;
    children?: InnerContainerFunc<MoreLinkContentArg>;
}
interface MoreLinkContentArg {
    num: number;
    text: string;
    shortText: string;
    view: ViewApi;
}
type MoreLinkMountArg = MountArg<MoreLinkContentArg>;
interface MoreLinkContainerState {
    isPopoverOpen: boolean;
    popoverId: string;
}
declare class MoreLinkContainer extends BaseComponent<MoreLinkContainerProps, MoreLinkContainerState> {
    private linkEl;
    private parentEl;
    state: {
        isPopoverOpen: boolean;
        popoverId: string;
    };
    render(): createElement.JSX.Element;
    componentDidMount(): void;
    componentDidUpdate(): void;
    handleLinkEl: (linkEl: HTMLElement | null) => void;
    updateParentEl(): void;
    handleClick: (ev: MouseEvent) => void;
    handlePopoverClose: () => void;
}
declare function computeEarliestSegStart(segs: Seg[]): DateMarker;

interface EventSegment {
    event: EventApi;
    start: Date;
    end: Date;
    isStart: boolean;
    isEnd: boolean;
}
type MoreLinkAction = MoreLinkSimpleAction | MoreLinkHandler;
type MoreLinkSimpleAction = 'popover' | 'week' | 'day' | 'timeGridWeek' | 'timeGridDay' | string;
interface MoreLinkArg {
    date: Date;
    allDay: boolean;
    allSegs: EventSegment[];
    hiddenSegs: EventSegment[];
    jsEvent: UIEvent;
    view: ViewApi;
}
type MoreLinkHandler = (arg: MoreLinkArg) => MoreLinkSimpleAction | void;

interface DateMeta {
    dow: number;
    isDisabled: boolean;
    isOther: boolean;
    isToday: boolean;
    isPast: boolean;
    isFuture: boolean;
}
declare function getDateMeta(date: DateMarker, todayRange?: DateRange, nowDate?: DateMarker, dateProfile?: DateProfile): DateMeta;
declare function getDayClassNames(meta: DateMeta, theme: Theme): string[];
declare function getSlotClassNames(meta: DateMeta, theme: Theme): string[];

interface SlotLaneContentArg extends Partial<DateMeta> {
    time?: Duration;
    date?: Date;
    view: ViewApi;
}
type SlotLaneMountArg = MountArg<SlotLaneContentArg>;
interface SlotLabelContentArg {
    level: number;
    time: Duration;
    date: Date;
    view: ViewApi;
    text: string;
}
type SlotLabelMountArg = MountArg<SlotLabelContentArg>;
interface AllDayContentArg {
    text: string;
    view: ViewApi;
}
type AllDayMountArg = MountArg<AllDayContentArg>;
interface DayHeaderContentArg extends DateMeta {
    date: Date;
    view: ViewApi;
    text: string;
    [otherProp: string]: any;
}
type DayHeaderMountArg = MountArg<DayHeaderContentArg>;

interface DayCellContentArg extends DateMeta {
    date: DateMarker;
    view: ViewApi;
    dayNumberText: string;
    [extraProp: string]: any;
}
type DayCellMountArg = MountArg<DayCellContentArg>;
interface DayCellContainerProps extends Partial<ElProps> {
    date: DateMarker;
    dateProfile: DateProfile;
    todayRange: DateRange;
    isMonthStart?: boolean;
    showDayNumber?: boolean;
    extraRenderProps?: Dictionary;
    defaultGenerator?: (renderProps: DayCellContentArg) => ComponentChild;
    children?: InnerContainerFunc<DayCellContentArg>;
}
declare class DayCellContainer extends BaseComponent<DayCellContainerProps> {
    refineRenderProps: (arg: DayCellRenderPropsInput) => DayCellContentArg;
    render(): createElement.JSX.Element;
}
declare function hasCustomDayCellContent(options: ViewOptions): boolean;
interface DayCellRenderPropsInput {
    date: DateMarker;
    dateProfile: DateProfile;
    todayRange: DateRange;
    dateEnv: DateEnv;
    viewApi: ViewApi;
    monthStartFormat: DateFormatter;
    isMonthStart: boolean;
    showDayNumber?: boolean;
    extraRenderProps?: Dictionary;
}

interface ViewContainerProps extends Partial<ElProps> {
    viewSpec: ViewSpec;
    children: ComponentChildren;
}
interface ViewContentArg {
    view: ViewApi;
}
type ViewMountArg = MountArg<ViewContentArg>;
declare class ViewContainer extends BaseComponent<ViewContainerProps> {
    render(): createElement.JSX.Element;
}

interface EventClickArg {
    el: HTMLElement;
    event: EventImpl;
    jsEvent: MouseEvent;
    view: ViewApi;
}

interface EventHoveringArg {
    el: HTMLElement;
    event: EventImpl;
    jsEvent: MouseEvent;
    view: ViewApi;
}

interface ToolbarInput {
    left?: string;
    center?: string;
    right?: string;
    start?: string;
    end?: string;
}
interface CustomButtonInput {
    text?: string;
    hint?: string;
    icon?: string;
    themeIcon?: string;
    bootstrapFontAwesome?: string;
    click?(ev: MouseEvent, element: HTMLElement): void;
}
interface ButtonIconsInput {
    prev?: string;
    next?: string;
    prevYear?: string;
    nextYear?: string;
    today?: string;
    [viewOrCustomButton: string]: string | undefined;
}
interface ButtonTextCompoundInput {
    prev?: string;
    next?: string;
    prevYear?: string;
    nextYear?: string;
    today?: string;
    month?: string;
    week?: string;
    day?: string;
    [viewOrCustomButton: string]: string | undefined;
}
interface ButtonHintCompoundInput {
    prev?: string | ((...args: any[]) => string);
    next?: string | ((...args: any[]) => string);
    prevYear?: string | ((...args: any[]) => string);
    nextYear?: string | ((...args: any[]) => string);
    today?: string | ((...args: any[]) => string);
    month?: string | ((...args: any[]) => string);
    week?: string | ((...args: any[]) => string);
    day?: string | ((...args: any[]) => string);
    [viewOrCustomButton: string]: string | ((...args: any[]) => string) | undefined;
}

type DatesSetArg = RangeApiWithTimeZone & {
    view: ViewApi;
};

interface EventAddArg {
    event: EventImpl;
    relatedEvents: EventImpl[];
    revert: () => void;
}
interface EventChangeArg {
    oldEvent: EventImpl;
    event: EventImpl;
    relatedEvents: EventImpl[];
    revert: () => void;
}
interface EventDropArg extends EventChangeArg {
    el: HTMLElement;
    delta: Duration;
    jsEvent: MouseEvent;
    view: ViewApi$1;
}
interface EventRemoveArg {
    event: EventImpl;
    relatedEvents: EventImpl[];
    revert: () => void;
}

declare class Store<Value> {
    private handlers;
    private currentValue;
    set(value: Value): void;
    subscribe(handler: (value: Value) => void): void;
}

type CustomRenderingHandler<RenderProps> = (customRender: CustomRendering<RenderProps>) => void;
interface CustomRendering<RenderProps> extends ElProps {
    id: string;
    isActive: boolean;
    containerEl: HTMLElement;
    reportNewContainerEl: (el: HTMLElement | null) => void;
    generatorName: string;
    generatorMeta: any;
    renderProps: RenderProps;
}
declare class CustomRenderingStore<RenderProps> extends Store<Map<string, CustomRendering<RenderProps>>> {
    private map;
    handle(customRendering: CustomRendering<RenderProps>): void;
}

interface EventApi {
    source: EventSourceApi | null;
    start: Date | null;
    end: Date | null;
    startStr: string;
    endStr: string;
    id: string;
    groupId: string;
    allDay: boolean;
    title: string;
    url: string;
    display: string;
    startEditable: boolean;
    durationEditable: boolean;
    constraint: any;
    overlap: boolean;
    allow: any;
    backgroundColor: string;
    borderColor: string;
    textColor: string;
    classNames: string[];
    extendedProps: Dictionary;
    setProp(name: string, val: any): void;
    setExtendedProp(name: string, val: any): void;
    setStart(startInput: DateInput, options?: {
        granularity?: string;
        maintainDuration?: boolean;
    }): void;
    setEnd(endInput: DateInput | null, options?: {
        granularity?: string;
    }): void;
    setDates(startInput: DateInput, endInput: DateInput | null, options?: {
        allDay?: boolean;
        granularity?: string;
    }): void;
    moveStart(deltaInput: DurationInput): void;
    moveEnd(deltaInput: DurationInput): void;
    moveDates(deltaInput: DurationInput): void;
    setAllDay(allDay: boolean, options?: {
        maintainDuration?: boolean;
    }): void;
    formatRange(formatInput: FormatterInput): any;
    remove(): void;
    toPlainObject(settings?: {
        collapseExtendedProps?: boolean;
        collapseColor?: boolean;
    }): Dictionary;
    toJSON(): Dictionary;
}

interface CalendarApi {
    view: ViewApi;
    updateSize(): void;
    setOption<OptionName extends keyof CalendarOptions>(name: OptionName, val: CalendarOptions[OptionName]): void;
    getOption<OptionName extends keyof CalendarOptions>(name: OptionName): CalendarOptions[OptionName];
    getAvailableLocaleCodes(): string[];
    on<ListenerName extends keyof CalendarListeners>(handlerName: ListenerName, handler: CalendarListeners[ListenerName]): void;
    off<ListenerName extends keyof CalendarListeners>(handlerName: ListenerName, handler: CalendarListeners[ListenerName]): void;
    trigger<ListenerName extends keyof CalendarListeners>(handlerName: ListenerName, ...args: Parameters<CalendarListeners[ListenerName]>): void;
    changeView(viewType: string, dateOrRange?: DateRangeInput | DateInput): void;
    zoomTo(dateMarker: Date, viewType?: string): void;
    prev(): void;
    next(): void;
    prevYear(): void;
    nextYear(): void;
    today(): void;
    gotoDate(zonedDateInput: DateInput): void;
    incrementDate(deltaInput: DurationInput): void;
    getDate(): Date;
    formatDate(d: DateInput, formatter: FormatterInput): string;
    formatRange(d0: DateInput, d1: DateInput, settings: any): string;
    formatIso(d: DateInput, omitTime?: boolean): string;
    select(dateOrObj: DateInput | any, endDate?: DateInput): void;
    unselect(): void;
    addEvent(eventInput: EventInput, sourceInput?: EventSourceApi | string | boolean): EventApi | null;
    getEventById(id: string): EventApi | null;
    getEvents(): EventApi[];
    removeAllEvents(): void;
    getEventSources(): EventSourceApi[];
    getEventSourceById(id: string): EventSourceApi | null;
    addEventSource(sourceInput: EventSourceInput): EventSourceApi;
    removeAllEventSources(): void;
    refetchEvents(): void;
    scrollToTime(timeInput: DurationInput): void;
}

declare const BASE_OPTION_REFINERS: {
    navLinkDayClick: Identity<string | ((this: CalendarApi, date: Date, jsEvent: UIEvent) => void)>;
    navLinkWeekClick: Identity<string | ((this: CalendarApi, weekStart: Date, jsEvent: UIEvent) => void)>;
    duration: typeof createDuration;
    bootstrapFontAwesome: Identity<false | ButtonIconsInput>;
    buttonIcons: Identity<false | ButtonIconsInput>;
    customButtons: Identity<{
        [name: string]: CustomButtonInput;
    }>;
    defaultAllDayEventDuration: typeof createDuration;
    defaultTimedEventDuration: typeof createDuration;
    nextDayThreshold: typeof createDuration;
    scrollTime: typeof createDuration;
    scrollTimeReset: BooleanConstructor;
    slotMinTime: typeof createDuration;
    slotMaxTime: typeof createDuration;
    dayPopoverFormat: typeof createFormatter;
    slotDuration: typeof createDuration;
    snapDuration: typeof createDuration;
    headerToolbar: Identity<false | ToolbarInput>;
    footerToolbar: Identity<false | ToolbarInput>;
    defaultRangeSeparator: StringConstructor;
    titleRangeSeparator: StringConstructor;
    forceEventDuration: BooleanConstructor;
    dayHeaders: BooleanConstructor;
    dayHeaderFormat: typeof createFormatter;
    dayHeaderClassNames: Identity<ClassNamesGenerator<DayHeaderContentArg>>;
    dayHeaderContent: Identity<CustomContentGenerator<DayHeaderContentArg>>;
    dayHeaderDidMount: Identity<DidMountHandler<DayHeaderMountArg>>;
    dayHeaderWillUnmount: Identity<WillUnmountHandler<DayHeaderMountArg>>;
    dayCellClassNames: Identity<ClassNamesGenerator<DayCellContentArg>>;
    dayCellContent: Identity<CustomContentGenerator<DayCellContentArg>>;
    dayCellDidMount: Identity<DidMountHandler<DayCellMountArg>>;
    dayCellWillUnmount: Identity<WillUnmountHandler<DayCellMountArg>>;
    initialView: StringConstructor;
    aspectRatio: NumberConstructor;
    weekends: BooleanConstructor;
    weekNumberCalculation: Identity<WeekNumberCalculation>;
    weekNumbers: BooleanConstructor;
    weekNumberClassNames: Identity<ClassNamesGenerator<WeekNumberContentArg>>;
    weekNumberContent: Identity<CustomContentGenerator<WeekNumberContentArg>>;
    weekNumberDidMount: Identity<DidMountHandler<WeekNumberMountArg>>;
    weekNumberWillUnmount: Identity<WillUnmountHandler<WeekNumberMountArg>>;
    editable: BooleanConstructor;
    viewClassNames: Identity<ClassNamesGenerator<ViewContentArg>>;
    viewDidMount: Identity<DidMountHandler<ViewMountArg>>;
    viewWillUnmount: Identity<WillUnmountHandler<ViewMountArg>>;
    nowIndicator: BooleanConstructor;
    nowIndicatorClassNames: Identity<ClassNamesGenerator<NowIndicatorContentArg>>;
    nowIndicatorContent: Identity<CustomContentGenerator<NowIndicatorContentArg>>;
    nowIndicatorDidMount: Identity<DidMountHandler<NowIndicatorMountArg>>;
    nowIndicatorWillUnmount: Identity<WillUnmountHandler<NowIndicatorMountArg>>;
    showNonCurrentDates: BooleanConstructor;
    lazyFetching: BooleanConstructor;
    startParam: StringConstructor;
    endParam: StringConstructor;
    timeZoneParam: StringConstructor;
    timeZone: StringConstructor;
    locales: Identity<LocaleInput[]>;
    locale: Identity<LocaleSingularArg>;
    themeSystem: Identity<string>;
    dragRevertDuration: NumberConstructor;
    dragScroll: BooleanConstructor;
    allDayMaintainDuration: BooleanConstructor;
    unselectAuto: BooleanConstructor;
    dropAccept: Identity<string | ((this: CalendarApi, draggable: any) => boolean)>;
    eventOrder: typeof parseFieldSpecs;
    eventOrderStrict: BooleanConstructor;
    handleWindowResize: BooleanConstructor;
    windowResizeDelay: NumberConstructor;
    longPressDelay: NumberConstructor;
    eventDragMinDistance: NumberConstructor;
    expandRows: BooleanConstructor;
    height: Identity<CssDimValue>;
    contentHeight: Identity<CssDimValue>;
    direction: Identity<"ltr" | "rtl">;
    weekNumberFormat: typeof createFormatter;
    eventResizableFromStart: BooleanConstructor;
    displayEventTime: BooleanConstructor;
    displayEventEnd: BooleanConstructor;
    weekText: StringConstructor;
    weekTextLong: StringConstructor;
    progressiveEventRendering: BooleanConstructor;
    businessHours: Identity<BusinessHoursInput>;
    initialDate: Identity<DateInput>;
    now: Identity<DateInput | ((this: CalendarApi) => DateInput)>;
    eventDataTransform: Identity<EventInputTransformer>;
    stickyHeaderDates: Identity<boolean | "auto">;
    stickyFooterScrollbar: Identity<boolean | "auto">;
    viewHeight: Identity<CssDimValue>;
    defaultAllDay: BooleanConstructor;
    eventSourceFailure: Identity<(this: CalendarApi, error: any) => void>;
    eventSourceSuccess: Identity<(this: CalendarApi, eventsInput: EventInput[], response?: Response) => EventInput[] | void>;
    eventDisplay: StringConstructor;
    eventStartEditable: BooleanConstructor;
    eventDurationEditable: BooleanConstructor;
    eventOverlap: Identity<boolean | OverlapFunc>;
    eventConstraint: Identity<ConstraintInput>;
    eventAllow: Identity<AllowFunc>;
    eventBackgroundColor: StringConstructor;
    eventBorderColor: StringConstructor;
    eventTextColor: StringConstructor;
    eventColor: StringConstructor;
    eventClassNames: Identity<ClassNamesGenerator<EventContentArg>>;
    eventContent: Identity<CustomContentGenerator<EventContentArg>>;
    eventDidMount: Identity<DidMountHandler<EventMountArg>>;
    eventWillUnmount: Identity<WillUnmountHandler<EventMountArg>>;
    selectConstraint: Identity<ConstraintInput>;
    selectOverlap: Identity<boolean | OverlapFunc>;
    selectAllow: Identity<AllowFunc>;
    droppable: BooleanConstructor;
    unselectCancel: StringConstructor;
    slotLabelFormat: Identity<FormatterInput | FormatterInput[]>;
    slotLaneClassNames: Identity<ClassNamesGenerator<SlotLaneContentArg>>;
    slotLaneContent: Identity<CustomContentGenerator<SlotLaneContentArg>>;
    slotLaneDidMount: Identity<DidMountHandler<SlotLaneMountArg>>;
    slotLaneWillUnmount: Identity<WillUnmountHandler<SlotLaneMountArg>>;
    slotLabelClassNames: Identity<ClassNamesGenerator<SlotLabelContentArg>>;
    slotLabelContent: Identity<CustomContentGenerator<SlotLabelContentArg>>;
    slotLabelDidMount: Identity<DidMountHandler<SlotLabelMountArg>>;
    slotLabelWillUnmount: Identity<WillUnmountHandler<SlotLabelMountArg>>;
    dayMaxEvents: Identity<number | boolean>;
    dayMaxEventRows: Identity<number | boolean>;
    dayMinWidth: NumberConstructor;
    slotLabelInterval: typeof createDuration;
    allDayText: StringConstructor;
    allDayClassNames: Identity<ClassNamesGenerator<AllDayContentArg>>;
    allDayContent: Identity<CustomContentGenerator<AllDayContentArg>>;
    allDayDidMount: Identity<DidMountHandler<AllDayMountArg>>;
    allDayWillUnmount: Identity<WillUnmountHandler<AllDayMountArg>>;
    slotMinWidth: NumberConstructor;
    navLinks: BooleanConstructor;
    eventTimeFormat: typeof createFormatter;
    rerenderDelay: NumberConstructor;
    moreLinkText: Identity<string | ((this: CalendarApi, num: number) => string)>;
    moreLinkHint: Identity<string | ((this: CalendarApi, num: number) => string)>;
    selectMinDistance: NumberConstructor;
    selectable: BooleanConstructor;
    selectLongPressDelay: NumberConstructor;
    eventLongPressDelay: NumberConstructor;
    selectMirror: BooleanConstructor;
    eventMaxStack: NumberConstructor;
    eventMinHeight: NumberConstructor;
    eventMinWidth: NumberConstructor;
    eventShortHeight: NumberConstructor;
    slotEventOverlap: BooleanConstructor;
    plugins: Identity<PluginDef[]>;
    firstDay: NumberConstructor;
    dayCount: NumberConstructor;
    dateAlignment: StringConstructor;
    dateIncrement: typeof createDuration;
    hiddenDays: Identity<number[]>;
    fixedWeekCount: BooleanConstructor;
    validRange: Identity<DateRangeInput | ((this: CalendarApi, nowDate: Date) => DateRangeInput)>;
    visibleRange: Identity<DateRangeInput | ((this: CalendarApi, currentDate: Date) => DateRangeInput)>;
    titleFormat: Identity<FormatterInput>;
    eventInteractive: BooleanConstructor;
    noEventsText: StringConstructor;
    viewHint: Identity<string | ((...args: any[]) => string)>;
    navLinkHint: Identity<string | ((...args: any[]) => string)>;
    closeHint: StringConstructor;
    timeHint: StringConstructor;
    eventHint: StringConstructor;
    moreLinkClick: Identity<MoreLinkAction>;
    moreLinkClassNames: Identity<ClassNamesGenerator<MoreLinkContentArg>>;
    moreLinkContent: Identity<CustomContentGenerator<MoreLinkContentArg>>;
    moreLinkDidMount: Identity<DidMountHandler<MoreLinkMountArg>>;
    moreLinkWillUnmount: Identity<WillUnmountHandler<MoreLinkMountArg>>;
    monthStartFormat: typeof createFormatter;
    handleCustomRendering: Identity<CustomRenderingHandler<any>>;
    customRenderingMetaMap: Identity<{
        [optionName: string]: any;
    }>;
    customRenderingReplaces: BooleanConstructor;
};
type BuiltInBaseOptionRefiners = typeof BASE_OPTION_REFINERS;
interface BaseOptionRefiners extends BuiltInBaseOptionRefiners {
}
type BaseOptions = RawOptionsFromRefiners<// as RawOptions
Required<BaseOptionRefiners>>;
declare const BASE_OPTION_DEFAULTS: {
    eventDisplay: string;
    defaultRangeSeparator: string;
    titleRangeSeparator: string;
    defaultTimedEventDuration: string;
    defaultAllDayEventDuration: {
        day: number;
    };
    forceEventDuration: boolean;
    nextDayThreshold: string;
    dayHeaders: boolean;
    initialView: string;
    aspectRatio: number;
    headerToolbar: {
        start: string;
        center: string;
        end: string;
    };
    weekends: boolean;
    weekNumbers: boolean;
    weekNumberCalculation: WeekNumberCalculation;
    editable: boolean;
    nowIndicator: boolean;
    scrollTime: string;
    scrollTimeReset: boolean;
    slotMinTime: string;
    slotMaxTime: string;
    showNonCurrentDates: boolean;
    lazyFetching: boolean;
    startParam: string;
    endParam: string;
    timeZoneParam: string;
    timeZone: string;
    locales: any[];
    locale: string;
    themeSystem: string;
    dragRevertDuration: number;
    dragScroll: boolean;
    allDayMaintainDuration: boolean;
    unselectAuto: boolean;
    dropAccept: string;
    eventOrder: string;
    dayPopoverFormat: {
        month: string;
        day: string;
        year: string;
    };
    handleWindowResize: boolean;
    windowResizeDelay: number;
    longPressDelay: number;
    eventDragMinDistance: number;
    expandRows: boolean;
    navLinks: boolean;
    selectable: boolean;
    eventMinHeight: number;
    eventMinWidth: number;
    eventShortHeight: number;
    monthStartFormat: {
        month: string;
        day: string;
    };
};
type BaseOptionsRefined = DefaultedRefinedOptions<RefinedOptionsFromRefiners<Required<BaseOptionRefiners>>, // Required is a hack for "Index signature is missing"
keyof typeof BASE_OPTION_DEFAULTS>;
declare const CALENDAR_LISTENER_REFINERS: {
    datesSet: Identity<(arg: DatesSetArg) => void>;
    eventsSet: Identity<(events: EventApi[]) => void>;
    eventAdd: Identity<(arg: EventAddArg) => void>;
    eventChange: Identity<(arg: EventChangeArg) => void>;
    eventRemove: Identity<(arg: EventRemoveArg) => void>;
    windowResize: Identity<(arg: {
        view: ViewApi;
    }) => void>;
    eventClick: Identity<(arg: EventClickArg) => void>;
    eventMouseEnter: Identity<(arg: EventHoveringArg) => void>;
    eventMouseLeave: Identity<(arg: EventHoveringArg) => void>;
    select: Identity<(arg: DateSelectArg) => void>;
    unselect: Identity<(arg: DateUnselectArg) => void>;
    loading: Identity<(isLoading: boolean) => void>;
    _unmount: Identity<() => void>;
    _beforeprint: Identity<() => void>;
    _afterprint: Identity<() => void>;
    _noEventDrop: Identity<() => void>;
    _noEventResize: Identity<() => void>;
    _resize: Identity<(forced: boolean) => void>;
    _scrollRequest: Identity<(arg: any) => void>;
};
type BuiltInCalendarListenerRefiners = typeof CALENDAR_LISTENER_REFINERS;
interface CalendarListenerRefiners extends BuiltInCalendarListenerRefiners {
}
type CalendarListenersLoose = RefinedOptionsFromRefiners<Required<CalendarListenerRefiners>>;
type CalendarListeners = Required<CalendarListenersLoose>;
declare const CALENDAR_OPTION_REFINERS: {
    buttonText: Identity<ButtonTextCompoundInput>;
    buttonHints: Identity<ButtonHintCompoundInput>;
    views: Identity<{
        [viewId: string]: ViewOptions;
    }>;
    plugins: Identity<PluginDef[]>;
    initialEvents: Identity<EventSourceInput>;
    events: Identity<EventSourceInput>;
    eventSources: Identity<EventSourceInput[]>;
};
type BuiltInCalendarOptionRefiners = typeof CALENDAR_OPTION_REFINERS;
interface CalendarOptionRefiners extends BuiltInCalendarOptionRefiners {
}
type CalendarOptions = BaseOptions & CalendarListenersLoose & RawOptionsFromRefiners<Required<CalendarOptionRefiners>>;
type CalendarOptionsRefined = BaseOptionsRefined & CalendarListenersLoose & RefinedOptionsFromRefiners<Required<CalendarOptionRefiners>>;
declare const VIEW_OPTION_REFINERS: {
    [name: string]: any;
};
type BuiltInViewOptionRefiners = typeof VIEW_OPTION_REFINERS;
interface ViewOptionRefiners extends BuiltInViewOptionRefiners {
}
type ViewOptions = BaseOptions & CalendarListenersLoose & RawOptionsFromRefiners<Required<ViewOptionRefiners>>;
type ViewOptionsRefined = BaseOptionsRefined & CalendarListenersLoose & RefinedOptionsFromRefiners<Required<ViewOptionRefiners>>;
declare function refineProps<Refiners extends GenericRefiners, Raw extends RawOptionsFromRefiners<Refiners>>(input: Raw, refiners: Refiners): {
    refined: RefinedOptionsFromRefiners<Refiners>;
    extra: Dictionary;
};
type GenericRefiners = {
    [propName: string]: (input: any) => any;
};
type GenericListenerRefiners = {
    [listenerName: string]: Identity<(this: CalendarApi, ...args: any[]) => void>;
};
type RawOptionsFromRefiners<Refiners extends GenericRefiners> = {
    [Prop in keyof Refiners]?: Refiners[Prop] extends ((input: infer RawType) => infer RefinedType) ? (any extends RawType ? RefinedType : RawType) : never;
};
type RefinedOptionsFromRefiners<Refiners extends GenericRefiners> = {
    [Prop in keyof Refiners]?: Refiners[Prop] extends ((input: any) => infer RefinedType) ? RefinedType : never;
};
type DefaultedRefinedOptions<RefinedOptions extends Dictionary, DefaultKey extends keyof RefinedOptions> = Required<Pick<RefinedOptions, DefaultKey>> & Partial<Omit<RefinedOptions, DefaultKey>>;
type Dictionary = Record<string, any>;
type Identity<T = any> = (raw: T) => T;
declare function identity<T>(raw: T): T;

declare class JsonRequestError extends Error {
    response: Response;
    constructor(message: string, response: Response);
}
declare function requestJson<ParsedResponse>(method: string, url: string, params: Dictionary): Promise<[ParsedResponse, Response]>;

declare function computeVisibleDayRange(timedRange: OpenDateRange, nextDayThreshold?: Duration): OpenDateRange;
declare function isMultiDayRange(range: DateRange): boolean;
declare function diffDates(date0: DateMarker, date1: DateMarker, dateEnv: DateEnv, largeUnit?: string): Duration;

declare function removeExact(array: any, exactVal: any): number;
declare function isArraysEqual(a0: any, a1: any, equalityFunc?: (v0: any, v1: any) => boolean): boolean;

declare function memoize<Args extends any[], Res>(workerFunc: (...args: Args) => Res, resEquality?: (res0: Res, res1: Res) => boolean, teardownFunc?: (res: Res) => void): (...args: Args) => Res;
declare function memoizeObjArg<Arg extends Dictionary, Res>(workerFunc: (arg: Arg) => Res, resEquality?: (res0: Res, res1: Res) => boolean, teardownFunc?: (res: Res) => void): (arg: Arg) => Res;
type MemoiseArrayFunc<Args extends any[], Res> = (argSets: Args[]) => Res[];
declare function memoizeArraylike<Args extends any[], Res>(// used at all?
workerFunc: (...args: Args) => Res, resEquality?: (res0: Res, res1: Res) => boolean, teardownFunc?: (res: Res) => void): MemoiseArrayFunc<Args, Res>;
type MemoizeHashFunc<Args extends any[], Res> = (argHash: {
    [key: string]: Args;
}) => {
    [key: string]: Res;
};
declare function memoizeHashlike<Args extends any[], Res>(workerFunc: (...args: Args) => Res, resEquality?: (res0: Res, res1: Res) => boolean, teardownFunc?: (res: Res) => void): MemoizeHashFunc<Args, Res>;

declare function removeElement(el: HTMLElement): void;
declare function elementClosest(el: HTMLElement, selector: string): HTMLElement;
declare function elementMatches(el: HTMLElement, selector: string): HTMLElement;
declare function findElements(container: HTMLElement[] | HTMLElement | NodeListOf<HTMLElement>, selector: string): HTMLElement[];
declare function findDirectChildren(parent: HTMLElement[] | HTMLElement, selector?: string): HTMLElement[];
declare function applyStyle(el: HTMLElement, props: Dictionary): void;
declare function getEventTargetViaRoot(ev: Event): EventTarget;
declare function getUniqueDomId(): string;

declare function getCanVGrowWithinCell(): boolean;

declare function buildNavLinkAttrs(context: ViewContext, dateMarker: DateMarker, viewType?: string, isTabbable?: boolean): {
    tabIndex: number;
    onKeyDown(ev: KeyboardEvent): void;
    onClick: (ev: UIEvent) => void;
    title: any;
    'data-navlink': string;
    'aria-label'?: undefined;
} | {
    onClick: (ev: UIEvent) => void;
    title: any;
    'data-navlink': string;
    'aria-label'?: undefined;
} | {
    'aria-label': string;
};

declare function preventDefault(ev: any): void;
declare function whenTransitionDone(el: HTMLElement, callback: (ev: Event) => void): void;

interface EdgeInfo {
    borderLeft: number;
    borderRight: number;
    borderTop: number;
    borderBottom: number;
    scrollbarLeft: number;
    scrollbarRight: number;
    scrollbarBottom: number;
    paddingLeft?: number;
    paddingRight?: number;
    paddingTop?: number;
    paddingBottom?: number;
}
declare function computeEdges(el: HTMLElement, getPadding?: boolean): EdgeInfo;
declare function computeInnerRect(el: any, goWithinPadding?: boolean, doFromWindowViewport?: boolean): {
    left: any;
    right: number;
    top: any;
    bottom: number;
};
declare function computeRect(el: any): Rect;
declare function getClippingParents(el: HTMLElement): HTMLElement[];

declare function unpromisify<Res>(func: (successCallback: (res: Res) => void, failureCallback: (error: Error) => void) => Promise<Res> | void, normalizedSuccessCallback: (res: Res) => void, normalizedFailureCallback: (error: Error) => void): void;

declare class PositionCache {
    els: HTMLElement[];
    originClientRect: ClientRect;
    lefts: any;
    rights: any;
    tops: any;
    bottoms: any;
    constructor(originEl: HTMLElement, els: HTMLElement[], isHorizontal: boolean, isVertical: boolean);
    buildElHorizontals(originClientLeft: number): void;
    buildElVerticals(originClientTop: number): void;
    leftToIndex(leftPosition: number): any;
    topToIndex(topPosition: number): any;
    getWidth(leftIndex: number): number;
    getHeight(topIndex: number): number;
    similarTo(otherCache: PositionCache): boolean;
}

declare abstract class ScrollController {
    abstract getScrollTop(): number;
    abstract getScrollLeft(): number;
    abstract setScrollTop(top: number): void;
    abstract setScrollLeft(left: number): void;
    abstract getClientWidth(): number;
    abstract getClientHeight(): number;
    abstract getScrollWidth(): number;
    abstract getScrollHeight(): number;
    getMaxScrollTop(): number;
    getMaxScrollLeft(): number;
    canScrollVertically(): boolean;
    canScrollHorizontally(): boolean;
    canScrollUp(): boolean;
    canScrollDown(): boolean;
    canScrollLeft(): boolean;
    canScrollRight(): boolean;
}
declare class ElementScrollController extends ScrollController {
    el: HTMLElement;
    constructor(el: HTMLElement);
    getScrollTop(): number;
    getScrollLeft(): number;
    setScrollTop(top: number): void;
    setScrollLeft(left: number): void;
    getScrollWidth(): number;
    getScrollHeight(): number;
    getClientHeight(): number;
    getClientWidth(): number;
}
declare class WindowScrollController extends ScrollController {
    getScrollTop(): number;
    getScrollLeft(): number;
    setScrollTop(n: number): void;
    setScrollLeft(n: number): void;
    getScrollWidth(): number;
    getScrollHeight(): number;
    getClientHeight(): number;
    getClientWidth(): number;
}

declare function buildIsoString(marker: DateMarker, timeZoneOffset?: number, stripZeroTime?: boolean): string;
declare function formatDayString(marker: DateMarker): string;
declare function formatIsoMonthStr(marker: DateMarker): string;
declare function formatIsoTimeString(marker: DateMarker): string;

declare function parse(str: any): {
    marker: Date;
    isTimeUnspecified: boolean;
    timeZoneOffset: any;
};

interface SegSpan {
    start: number;
    end: number;
}
interface SegEntry {
    index: number;
    thickness?: number;
    span: SegSpan;
}
interface SegInsertion {
    level: number;
    levelCoord: number;
    lateral: number;
    touchingLevel: number;
    touchingLateral: number;
    touchingEntry: SegEntry;
    stackCnt: number;
}
interface SegRect extends SegEntry {
    thickness: number;
    levelCoord: number;
}
interface SegEntryGroup {
    entries: SegEntry[];
    span: SegSpan;
}
declare class SegHierarchy {
    private getEntryThickness;
    strictOrder: boolean;
    allowReslicing: boolean;
    maxCoord: number;
    maxStackCnt: number;
    levelCoords: number[];
    entriesByLevel: SegEntry[][];
    stackCnts: {
        [entryId: string]: number;
    };
    constructor(getEntryThickness?: (entry: SegEntry) => number);
    addSegs(inputs: SegEntry[]): SegEntry[];
    insertEntry(entry: SegEntry, hiddenEntries: SegEntry[]): void;
    isInsertionValid(insertion: SegInsertion, entry: SegEntry): boolean;
    handleInvalidInsertion(insertion: SegInsertion, entry: SegEntry, hiddenEntries: SegEntry[]): void;
    splitEntry(entry: SegEntry, barrier: SegEntry, hiddenEntries: SegEntry[]): void;
    insertEntryAt(entry: SegEntry, insertion: SegInsertion): void;
    findInsertion(newEntry: SegEntry): SegInsertion;
    toRects(): SegRect[];
}
declare function getEntrySpanEnd(entry: SegEntry): number;
declare function buildEntryKey(entry: SegEntry): string;
declare function groupIntersectingEntries(entries: SegEntry[]): SegEntryGroup[];
declare function intersectSpans(span0: SegSpan, span1: SegSpan): SegSpan | null;
declare function binarySearch<Item>(a: Item[], searchVal: number, getItemVal: (item: Item) => number): [number, number];

declare const config: any;

interface CalendarRootProps {
    options: CalendarOptions;
    theme: Theme;
    emitter: Emitter<CalendarListeners>;
    children: (classNames: string[], height: CssDimValue, isHeightAuto: boolean, forPrint: boolean) => ComponentChildren;
}
interface CalendarRootState {
    forPrint: boolean;
}
declare class CalendarRoot extends BaseComponent<CalendarRootProps, CalendarRootState> {
    state: {
        forPrint: boolean;
    };
    render(): ComponentChildren;
    componentDidMount(): void;
    componentWillUnmount(): void;
    handleBeforePrint: () => void;
    handleAfterPrint: () => void;
}

interface DayHeaderProps {
    dateProfile: DateProfile;
    dates: DateMarker[];
    datesRepDistinctDays: boolean;
    renderIntro?: (rowKey: string) => VNode;
}
declare class DayHeader extends BaseComponent<DayHeaderProps> {
    createDayHeaderFormatter: (explicitFormat: DateFormatter, datesRepDistinctDays: any, dateCnt: any) => DateFormatter;
    render(): createElement.JSX.Element;
}

declare function computeFallbackHeaderFormat(datesRepDistinctDays: boolean, dayCnt: number): DateFormatter;

interface TableDateCellProps {
    date: DateMarker;
    dateProfile: DateProfile;
    todayRange: DateRange;
    colCnt: number;
    dayHeaderFormat: DateFormatter;
    colSpan?: number;
    isSticky?: boolean;
    extraDataAttrs?: Dictionary;
    extraRenderProps?: Dictionary;
}
declare class TableDateCell extends BaseComponent<TableDateCellProps> {
    render(): createElement.JSX.Element;
}

interface TableDowCellProps {
    dow: number;
    dayHeaderFormat: DateFormatter;
    colSpan?: number;
    isSticky?: boolean;
    extraRenderProps?: Dictionary;
    extraDataAttrs?: Dictionary;
    extraClassNames?: string[];
}
declare class TableDowCell extends BaseComponent<TableDowCellProps> {
    render(): createElement.JSX.Element;
}

interface DaySeriesSeg {
    firstIndex: number;
    lastIndex: number;
    isStart: boolean;
    isEnd: boolean;
}
declare class DaySeriesModel {
    cnt: number;
    dates: DateMarker[];
    indices: number[];
    constructor(range: DateRange, dateProfileGenerator: DateProfileGenerator);
    sliceRange(range: DateRange): DaySeriesSeg | null;
    private getDateDayIndex;
}

interface DayTableSeg extends Seg {
    row: number;
    firstCol: number;
    lastCol: number;
}
interface DayTableCell {
    key: string;
    date: DateMarker;
    extraRenderProps?: Dictionary;
    extraDataAttrs?: Dictionary;
    extraClassNames?: string[];
    extraDateSpan?: Dictionary;
}
declare class DayTableModel {
    rowCnt: number;
    colCnt: number;
    cells: DayTableCell[][];
    headerDates: DateMarker[];
    private daySeries;
    constructor(daySeries: DaySeriesModel, breakOnWeeks: boolean);
    private buildCells;
    private buildCell;
    private buildHeaderDates;
    sliceRange(range: DateRange): DayTableSeg[];
}

interface SliceableProps {
    dateSelection: DateSpan;
    businessHours: EventStore;
    eventStore: EventStore;
    eventDrag: EventInteractionState | null;
    eventResize: EventInteractionState | null;
    eventSelection: string;
    eventUiBases: EventUiHash;
}
interface SlicedProps<SegType extends Seg> {
    dateSelectionSegs: SegType[];
    businessHourSegs: SegType[];
    fgEventSegs: SegType[];
    bgEventSegs: SegType[];
    eventDrag: EventSegUiInteractionState | null;
    eventResize: EventSegUiInteractionState | null;
    eventSelection: string;
}
declare abstract class Slicer<SegType extends Seg, ExtraArgs extends any[] = []> {
    private sliceBusinessHours;
    private sliceDateSelection;
    private sliceEventStore;
    private sliceEventDrag;
    private sliceEventResize;
    abstract sliceRange(dateRange: DateRange, ...extraArgs: ExtraArgs): SegType[];
    protected forceDayIfListItem: boolean;
    sliceProps(props: SliceableProps, dateProfile: DateProfile, nextDayThreshold: Duration | null, context: CalendarContext, ...extraArgs: ExtraArgs): SlicedProps<SegType>;
    sliceNowDate(// does not memoize
    date: DateMarker, dateProfile: DateProfile, nextDayThreshold: Duration | null, context: CalendarContext, ...extraArgs: ExtraArgs): SegType[];
    private _sliceBusinessHours;
    private _sliceEventStore;
    private _sliceInteraction;
    private _sliceDateSpan;
    private sliceEventRanges;
    private sliceEventRange;
}

declare function isInteractionValid(interaction: EventInteractionState, dateProfile: DateProfile, context: CalendarContext): boolean;
declare function isDateSelectionValid(dateSelection: DateSpan, dateProfile: DateProfile, context: CalendarContext): boolean;
declare function isPropsValid(state: SplittableProps, context: CalendarContext, dateSpanMeta?: {}, filterConfig?: any): boolean;

type OverflowValue = 'auto' | 'hidden' | 'scroll' | 'visible';
interface ScrollerProps {
    elRef?: Ref<HTMLElement>;
    overflowX: OverflowValue;
    overflowY: OverflowValue;
    overcomeLeft?: number;
    overcomeRight?: number;
    overcomeBottom?: number;
    maxHeight?: CssDimValue;
    liquid?: boolean;
    liquidIsAbsolute?: boolean;
    children?: ComponentChildren;
}
declare class Scroller extends BaseComponent<ScrollerProps> implements ScrollerLike {
    el: HTMLElement;
    render(): createElement.JSX.Element;
    handleEl: (el: HTMLElement) => void;
    needsXScrolling(): boolean;
    needsYScrolling(): boolean;
    getXScrollbarWidth(): number;
    getYScrollbarWidth(): number;
}

declare class RefMap<RefType> {
    masterCallback?: (val: RefType | null, key: string) => void;
    currentMap: {
        [key: string]: RefType;
    };
    private depths;
    private callbackMap;
    constructor(masterCallback?: (val: RefType | null, key: string) => void);
    createRef(key: string | number): (val: RefType) => void;
    handleValue: (val: RefType | null, key: string) => void;
    collect(startIndex?: number, endIndex?: number, step?: number): RefType[];
    getAll(): RefType[];
}

interface SimpleScrollGridProps {
    cols: ColProps[];
    sections: SimpleScrollGridSection[];
    liquid: boolean;
    collapsibleWidth: boolean;
    height?: CssDimValue;
}
interface SimpleScrollGridSection extends SectionConfig {
    key: string;
    chunk?: ChunkConfig;
}
interface SimpleScrollGridState {
    shrinkWidth: number | null;
    forceYScrollbars: boolean;
    scrollerClientWidths: {
        [key: string]: number;
    };
    scrollerClientHeights: {
        [key: string]: number;
    };
}
declare class SimpleScrollGrid extends BaseComponent<SimpleScrollGridProps, SimpleScrollGridState> {
    processCols: (a: any) => any;
    renderMicroColGroup: typeof renderMicroColGroup;
    scrollerRefs: RefMap<Scroller>;
    scrollerElRefs: RefMap<HTMLElement>;
    state: SimpleScrollGridState;
    render(): VNode;
    renderSection(sectionConfig: SimpleScrollGridSection, microColGroupNode: VNode, isHeader: boolean): createElement.JSX.Element;
    renderChunkTd(sectionConfig: SimpleScrollGridSection, microColGroupNode: VNode, chunkConfig: ChunkConfig, isHeader: boolean): createElement.JSX.Element;
    _handleScrollerEl(scrollerEl: HTMLElement | null, key: string): void;
    handleSizing: () => void;
    componentDidMount(): void;
    componentDidUpdate(): void;
    componentWillUnmount(): void;
    computeShrinkWidth(): number;
    computeScrollerDims(): {
        forceYScrollbars: boolean;
        scrollerClientWidths: {
            [index: string]: number;
        };
        scrollerClientHeights: {
            [index: string]: number;
        };
    };
}

interface ScrollbarWidths {
    x: number;
    y: number;
}
declare function getScrollbarWidths(): ScrollbarWidths;

declare function getIsRtlScrollbarOnLeft(): boolean;

interface NowTimerProps {
    unit: string;
    children: (now: DateMarker, todayRange: DateRange) => ComponentChildren;
}
interface NowTimerState {
    nowDate: DateMarker;
    todayRange: DateRange;
}
declare class NowTimer extends Component<NowTimerProps, NowTimerState> {
    static contextType: any;
    context: ViewContext;
    timeoutId: any;
    constructor(props: NowTimerProps, context: ViewContext);
    render(): ComponentChildren;
    componentDidMount(): void;
    componentDidUpdate(prevProps: NowTimerProps): void;
    componentWillUnmount(): void;
    private computeTiming;
    private setTimeout;
    private clearTimeout;
    private handleRefresh;
    private handleVisibilityChange;
}

interface StandardEventProps {
    elRef?: ElRef;
    elClasses?: string[];
    seg: Seg;
    isDragging: boolean;
    isResizing: boolean;
    isDateSelecting: boolean;
    isSelected: boolean;
    isPast: boolean;
    isFuture: boolean;
    isToday: boolean;
    disableDragging?: boolean;
    disableResizing?: boolean;
    defaultTimeFormat: DateFormatter;
    defaultDisplayEventTime?: boolean;
    defaultDisplayEventEnd?: boolean;
}
declare class StandardEvent extends BaseComponent<StandardEventProps> {
    render(): createElement.JSX.Element;
}

interface MinimalEventProps {
    seg: Seg;
    isDragging: boolean;
    isResizing: boolean;
    isDateSelecting: boolean;
    isSelected: boolean;
    isPast: boolean;
    isFuture: boolean;
    isToday: boolean;
}
type EventContainerProps = ElProps & MinimalEventProps & {
    defaultGenerator: (renderProps: EventContentArg) => ComponentChild;
    disableDragging?: boolean;
    disableResizing?: boolean;
    timeText: string;
    children?: InnerContainerFunc<EventContentArg>;
};
declare class EventContainer extends BaseComponent<EventContainerProps> {
    private buildPublicEvent;
    el: HTMLElement;
    render(): createElement.JSX.Element;
    handleEl: (el: HTMLElement | null) => void;
    componentDidUpdate(prevProps: EventContainerProps): void;
}

interface BgEventProps {
    seg: Seg;
    isPast: boolean;
    isFuture: boolean;
    isToday: boolean;
}
declare class BgEvent extends BaseComponent<BgEventProps> {
    render(): createElement.JSX.Element;
}
declare function renderFill(fillType: string): createElement.JSX.Element;

declare function injectStyles(styleText: string): void;

export { ViewContentArg as $, AllowFunc as A, BusinessHoursInput as B, CalendarImpl as C, DateInput as D, EventSourceApi as E, FormatterInput as F, WeekNumberMountArg as G, MoreLinkMountArg as H, SlotLaneContentArg as I, JsonRequestError as J, SlotLaneMountArg as K, LocaleInput as L, MoreLinkContentArg as M, NativeFormatterOptions as N, OverlapFunc as O, PluginDefInput as P, SlotLabelContentArg as Q, SlotLabelMountArg as R, SpecificViewContentArg as S, AllDayContentArg as T, AllDayMountArg as U, ViewApi as V, WillUnmountHandler as W, DayHeaderContentArg as X, DayHeaderMountArg as Y, DayCellContentArg as Z, DayCellMountArg as _, CalendarOptions as a, disableCursor as a$, ViewMountArg as a0, EventClickArg as a1, EventHoveringArg as a2, DateSelectArg as a3, DateUnselectArg as a4, WeekNumberCalculation as a5, ToolbarInput as a6, CustomButtonInput as a7, ButtonIconsInput as a8, ButtonTextCompoundInput as a9, CalendarListenerRefiners as aA, BASE_OPTION_DEFAULTS as aB, identity as aC, refineProps as aD, EventDef as aE, EventDefHash as aF, EventInstance as aG, EventInstanceHash as aH, createEventInstance as aI, EventRefined as aJ, EventTuple as aK, EventRefiners as aL, parseEventDef as aM, refineEventDef as aN, parseBusinessHours as aO, OrderSpec as aP, padStart as aQ, isInt as aR, parseFieldSpecs as aS, compareByFieldSpecs as aT, flexibleCompare as aU, preventSelection as aV, allowSelection as aW, preventContextMenu as aX, allowContextMenu as aY, compareNumbers as aZ, enableCursor as a_, EventContentArg as aa, EventMountArg as ab, DatesSetArg as ac, EventAddArg as ad, EventChangeArg as ae, EventDropArg as af, EventRemoveArg as ag, ButtonHintCompoundInput as ah, CustomRenderingHandler as ai, CustomRenderingStore as aj, DateSpanApi as ak, DatePointApi as al, DateSelectionApi as am, Duration as an, EventSegment as ao, MoreLinkAction as ap, MoreLinkSimpleAction as aq, MoreLinkArg as ar, MoreLinkHandler as as, Identity as at, Dictionary as au, BaseOptionRefiners as av, BaseOptionsRefined as aw, ViewOptionsRefined as ax, RawOptionsFromRefiners as ay, RefinedOptionsFromRefiners as az, PluginDef as b, rangeContainsRange as b$, guid as b0, computeVisibleDayRange as b1, isMultiDayRange as b2, diffDates as b3, removeExact as b4, isArraysEqual as b5, MemoizeHashFunc as b6, MemoiseArrayFunc as b7, memoize as b8, memoizeObjArg as b9, createEmptyEventStore as bA, mergeEventStores as bB, getRelevantEvents as bC, eventTupleToStore as bD, EventUiHash as bE, EventUi as bF, combineEventUis as bG, createEventUi as bH, SplittableProps as bI, Splitter as bJ, getDayClassNames as bK, getDateMeta as bL, getSlotClassNames as bM, buildNavLinkAttrs as bN, preventDefault as bO, whenTransitionDone as bP, computeInnerRect as bQ, computeEdges as bR, getClippingParents as bS, computeRect as bT, unpromisify as bU, Emitter as bV, DateRange as bW, rangeContainsMarker as bX, intersectRanges as bY, rangesEqual as bZ, rangesIntersect as b_, memoizeArraylike as ba, memoizeHashlike as bb, Rect as bc, Point as bd, intersectRects as be, pointInsideRect as bf, constrainPoint as bg, getRectCenter as bh, diffPoints as bi, translateRect as bj, mapHash as bk, filterHash as bl, isPropsEqual as bm, compareObjs as bn, collectFromHash as bo, findElements as bp, findDirectChildren as bq, removeElement as br, applyStyle as bs, elementMatches as bt, elementClosest as bu, getEventTargetViaRoot as bv, getUniqueDomId as bw, parseClassNames as bx, getCanVGrowWithinCell as by, EventStore as bz, CalendarApi as c, Interaction as c$, PositionCache as c0, ScrollController as c1, ElementScrollController as c2, WindowScrollController as c3, Theme as c4, ViewContext as c5, ViewContextType as c6, Seg as c7, EventSegUiInteractionState as c8, DateComponent as c9, greatestDurationDenominator as cA, DateEnv as cB, createFormatter as cC, DateFormatter as cD, VerboseFormattingArg as cE, formatIsoTimeString as cF, formatDayString as cG, buildIsoString as cH, formatIsoMonthStr as cI, NamedTimeZoneImpl as cJ, parse as cK, EventSourceDef as cL, EventSourceRefined as cM, EventSourceRefiners as cN, SegSpan as cO, SegRect as cP, SegEntry as cQ, SegInsertion as cR, SegEntryGroup as cS, SegHierarchy as cT, buildEntryKey as cU, getEntrySpanEnd as cV, binarySearch as cW, groupIntersectingEntries as cX, intersectSpans as cY, InteractionSettings as cZ, InteractionSettingsStore as c_, CalendarData as ca, ViewProps as cb, DateProfile as cc, DateProfileGenerator as cd, ViewSpec as ce, DateSpan as cf, isDateSpansEqual as cg, DateMarker as ch, addDays as ci, startOfDay as cj, addMs as ck, addWeeks as cl, diffWeeks as cm, diffWholeWeeks as cn, diffWholeDays as co, diffDayAndTime as cp, diffDays as cq, isValidDate as cr, createDuration as cs, asCleanDays as ct, multiplyDuration as cu, addDurations as cv, asRoughMinutes as cw, asRoughSeconds as cx, asRoughMs as cy, wholeDivideDurations as cz, EventApi as d, getAllowYScrolling as d$, interactionSettingsToStore as d0, interactionSettingsStore as d1, PointerDragEvent as d2, Hit as d3, dateSelectionJoinTransformer as d4, eventDragMutationMassager as d5, EventDropTransformers as d6, ElementDragging as d7, config as d8, RecurringType as d9, Slicer as dA, EventMutation as dB, applyMutationToEventStore as dC, Constraint as dD, isPropsValid as dE, isInteractionValid as dF, isDateSelectionValid as dG, requestJson as dH, BaseComponent as dI, setRef as dJ, DelayedRunner as dK, ScrollGridProps as dL, ScrollGridSectionConfig as dM, ColGroupConfig as dN, ScrollGridChunkConfig as dO, SimpleScrollGridSection as dP, SimpleScrollGrid as dQ, ScrollerLike as dR, ColProps as dS, ChunkContentCallbackArgs as dT, ChunkConfigRowContent as dU, ChunkConfigContent as dV, hasShrinkWidth as dW, renderMicroColGroup as dX, getScrollGridClassNames as dY, getSectionClassNames as dZ, getSectionHasLiquidHeight as d_, DragMetaInput as da, DragMeta as db, parseDragMeta as dc, ViewPropsTransformer as dd, Action as de, CalendarContext as df, CalendarContentProps as dg, CalendarRoot as dh, DayHeader as di, computeFallbackHeaderFormat as dj, TableDateCell as dk, TableDowCell as dl, DaySeriesModel as dm, EventInteractionState as dn, sliceEventStore as dp, hasBgRendering as dq, getElSeg as dr, buildSegTimeText as ds, sortEventSegs as dt, getSegMeta as du, buildEventRangeKey as dv, getSegAnchorAttrs as dw, DayTableCell as dx, DayTableModel as dy, SlicedProps as dz, EventRenderRange as e, renderChunkContent as e0, computeShrinkWidth as e1, sanitizeShrinkWidth as e2, isColPropsEqual as e3, renderScrollShim as e4, getStickyFooterScrollbar as e5, getStickyHeaderDates as e6, OverflowValue as e7, Scroller as e8, getScrollbarWidths as e9, buildEventApis as eA, ElProps as eB, buildElAttrs as eC, InnerContainerFunc as eD, ContentContainer as eE, CustomRendering as eF, RefMap as ea, getIsRtlScrollbarOnLeft as eb, NowTimer as ec, ScrollRequest as ed, ScrollResponder as ee, MountArg as ef, StandardEvent as eg, NowIndicatorContainer as eh, DayCellContainer as ei, hasCustomDayCellContent as ej, MinimalEventProps as ek, EventContainer as el, renderFill as em, BgEvent as en, WeekNumberContainerProps as eo, WeekNumberContainer as ep, MoreLinkContainer as eq, computeEarliestSegStart as er, ViewContainerProps as es, ViewContainer as et, DatePointTransform as eu, DateSpanTransform as ev, triggerDateSelect as ew, getDefaultEventEnd as ex, injectStyles as ey, EventImpl as ez, CalendarListeners as f, DurationInput as g, DateSpanInput as h, DateRangeInput as i, EventSourceInput as j, EventSourceFunc as k, EventSourceFuncArg as l, EventInput as m, EventInputTransformer as n, CssDimValue as o, LocaleSingularArg as p, ConstraintInput as q, ViewComponentType as r, sliceEvents as s, SpecificViewMountArg as t, ClassNamesGenerator as u, CustomContentGenerator as v, DidMountHandler as w, NowIndicatorContentArg as x, NowIndicatorMountArg as y, WeekNumberContentArg as z };
