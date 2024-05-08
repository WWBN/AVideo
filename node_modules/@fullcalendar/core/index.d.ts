import { C as CalendarImpl, a as CalendarOptions, N as NativeFormatterOptions, D as DateInput, P as PluginDefInput, b as PluginDef, L as LocaleInput } from './internal-common.js';
export { T as AllDayContentArg, U as AllDayMountArg, A as AllowFunc, B as BusinessHoursInput, ah as ButtonHintCompoundInput, a8 as ButtonIconsInput, a9 as ButtonTextCompoundInput, c as CalendarApi, f as CalendarListeners, a as CalendarOptions, u as ClassNamesGenerator, q as ConstraintInput, o as CssDimValue, a7 as CustomButtonInput, v as CustomContentGenerator, ai as CustomRenderingHandler, aj as CustomRenderingStore, D as DateInput, al as DatePointApi, i as DateRangeInput, a3 as DateSelectArg, am as DateSelectionApi, ak as DateSpanApi, h as DateSpanInput, a4 as DateUnselectArg, ac as DatesSetArg, Z as DayCellContentArg, _ as DayCellMountArg, X as DayHeaderContentArg, Y as DayHeaderMountArg, w as DidMountHandler, an as Duration, g as DurationInput, ad as EventAddArg, d as EventApi, ae as EventChangeArg, a1 as EventClickArg, aa as EventContentArg, af as EventDropArg, a2 as EventHoveringArg, m as EventInput, n as EventInputTransformer, ab as EventMountArg, ag as EventRemoveArg, e as EventRenderRange, ao as EventSegment, E as EventSourceApi, k as EventSourceFunc, l as EventSourceFuncArg, j as EventSourceInput, F as FormatterInput, J as JsonRequestError, L as LocaleInput, p as LocaleSingularArg, ap as MoreLinkAction, ar as MoreLinkArg, M as MoreLinkContentArg, as as MoreLinkHandler, H as MoreLinkMountArg, aq as MoreLinkSimpleAction, x as NowIndicatorContentArg, y as NowIndicatorMountArg, O as OverlapFunc, b as PluginDef, P as PluginDefInput, Q as SlotLabelContentArg, R as SlotLabelMountArg, I as SlotLaneContentArg, K as SlotLaneMountArg, S as SpecificViewContentArg, t as SpecificViewMountArg, a6 as ToolbarInput, V as ViewApi, r as ViewComponentType, $ as ViewContentArg, a0 as ViewMountArg, a5 as WeekNumberCalculation, z as WeekNumberContentArg, G as WeekNumberMountArg, W as WillUnmountHandler, s as sliceEvents } from './internal-common.js';
import 'preact';
import './preact.js';
import './index.js';

declare class Calendar extends CalendarImpl {
    el: HTMLElement;
    private currentData;
    private renderRunner;
    private isRendering;
    private isRendered;
    private currentClassNames;
    private customContentRenderId;
    constructor(el: HTMLElement, optionOverrides?: CalendarOptions);
    private handleAction;
    private handleData;
    private handleRenderRequest;
    render(): void;
    destroy(): void;
    updateSize(): void;
    batchRendering(func: any): void;
    pauseRendering(): void;
    resumeRendering(): void;
    resetOptions(optionOverrides: any, changedOptionNames?: string[]): void;
    private setClassNames;
    private setHeight;
}

interface FormatDateOptions extends NativeFormatterOptions {
    locale?: string;
}
interface FormatRangeOptions extends FormatDateOptions {
    separator?: string;
    isEndExclusive?: boolean;
}
declare function formatDate(dateInput: DateInput, options?: FormatDateOptions): string;
declare function formatRange(startInput: DateInput, endInput: DateInput, options: FormatRangeOptions): string;

declare function createPlugin(input: PluginDefInput): PluginDef;

declare const globalLocales: LocaleInput[];

declare const globalPlugins: PluginDef[];

declare const version: string;

export { Calendar, FormatDateOptions, FormatRangeOptions, createPlugin, formatDate, formatRange, globalLocales, globalPlugins, version };
