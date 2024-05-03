'use strict';

Object.defineProperty(exports, '__esModule', { value: true });

var internal_cjs = require('@fullcalendar/core/internal.cjs');
var preact_cjs = require('@fullcalendar/core/preact.cjs');

class ListViewHeaderRow extends internal_cjs.BaseComponent {
    constructor() {
        super(...arguments);
        this.state = {
            textId: internal_cjs.getUniqueDomId(),
        };
    }
    render() {
        let { theme, dateEnv, options, viewApi } = this.context;
        let { cellId, dayDate, todayRange } = this.props;
        let { textId } = this.state;
        let dayMeta = internal_cjs.getDateMeta(dayDate, todayRange);
        // will ever be falsy?
        let text = options.listDayFormat ? dateEnv.format(dayDate, options.listDayFormat) : '';
        // will ever be falsy? also, BAD NAME "alt"
        let sideText = options.listDaySideFormat ? dateEnv.format(dayDate, options.listDaySideFormat) : '';
        let renderProps = Object.assign({ date: dateEnv.toDate(dayDate), view: viewApi, textId,
            text,
            sideText, navLinkAttrs: internal_cjs.buildNavLinkAttrs(this.context, dayDate), sideNavLinkAttrs: internal_cjs.buildNavLinkAttrs(this.context, dayDate, 'day', false) }, dayMeta);
        // TODO: make a reusable HOC for dayHeader (used in daygrid/timegrid too)
        return (preact_cjs.createElement(internal_cjs.ContentContainer, { elTag: "tr", elClasses: [
                'fc-list-day',
                ...internal_cjs.getDayClassNames(dayMeta, theme),
            ], elAttrs: {
                'data-date': internal_cjs.formatDayString(dayDate),
            }, renderProps: renderProps, generatorName: "dayHeaderContent", customGenerator: options.dayHeaderContent, defaultGenerator: renderInnerContent, classNameGenerator: options.dayHeaderClassNames, didMount: options.dayHeaderDidMount, willUnmount: options.dayHeaderWillUnmount }, (InnerContent) => ( // TODO: force-hide top border based on :first-child
        preact_cjs.createElement("th", { scope: "colgroup", colSpan: 3, id: cellId, "aria-labelledby": textId },
            preact_cjs.createElement(InnerContent, { elTag: "div", elClasses: [
                    'fc-list-day-cushion',
                    theme.getClass('tableCellShaded'),
                ] })))));
    }
}
function renderInnerContent(props) {
    return (preact_cjs.createElement(preact_cjs.Fragment, null,
        props.text && (preact_cjs.createElement("a", Object.assign({ id: props.textId, className: "fc-list-day-text" }, props.navLinkAttrs), props.text)),
        props.sideText && ( /* not keyboard tabbable */preact_cjs.createElement("a", Object.assign({ "aria-hidden": true, className: "fc-list-day-side-text" }, props.sideNavLinkAttrs), props.sideText))));
}

const DEFAULT_TIME_FORMAT = internal_cjs.createFormatter({
    hour: 'numeric',
    minute: '2-digit',
    meridiem: 'short',
});
class ListViewEventRow extends internal_cjs.BaseComponent {
    render() {
        let { props, context } = this;
        let { options } = context;
        let { seg, timeHeaderId, eventHeaderId, dateHeaderId } = props;
        let timeFormat = options.eventTimeFormat || DEFAULT_TIME_FORMAT;
        return (preact_cjs.createElement(internal_cjs.EventContainer, Object.assign({}, props, { elTag: "tr", elClasses: [
                'fc-list-event',
                seg.eventRange.def.url && 'fc-event-forced-url',
            ], defaultGenerator: () => renderEventInnerContent(seg, context) /* weird */, seg: seg, timeText: "", disableDragging: true, disableResizing: true }), (InnerContent, eventContentArg) => (preact_cjs.createElement(preact_cjs.Fragment, null,
            buildTimeContent(seg, timeFormat, context, timeHeaderId, dateHeaderId),
            preact_cjs.createElement("td", { "aria-hidden": true, className: "fc-list-event-graphic" },
                preact_cjs.createElement("span", { className: "fc-list-event-dot", style: {
                        borderColor: eventContentArg.borderColor || eventContentArg.backgroundColor,
                    } })),
            preact_cjs.createElement(InnerContent, { elTag: "td", elClasses: ['fc-list-event-title'], elAttrs: { headers: `${eventHeaderId} ${dateHeaderId}` } })))));
    }
}
function renderEventInnerContent(seg, context) {
    let interactiveAttrs = internal_cjs.getSegAnchorAttrs(seg, context);
    return (preact_cjs.createElement("a", Object.assign({}, interactiveAttrs), seg.eventRange.def.title));
}
function buildTimeContent(seg, timeFormat, context, timeHeaderId, dateHeaderId) {
    let { options } = context;
    if (options.displayEventTime !== false) {
        let eventDef = seg.eventRange.def;
        let eventInstance = seg.eventRange.instance;
        let doAllDay = false;
        let timeText;
        if (eventDef.allDay) {
            doAllDay = true;
        }
        else if (internal_cjs.isMultiDayRange(seg.eventRange.range)) { // TODO: use (!isStart || !isEnd) instead?
            if (seg.isStart) {
                timeText = internal_cjs.buildSegTimeText(seg, timeFormat, context, null, null, eventInstance.range.start, seg.end);
            }
            else if (seg.isEnd) {
                timeText = internal_cjs.buildSegTimeText(seg, timeFormat, context, null, null, seg.start, eventInstance.range.end);
            }
            else {
                doAllDay = true;
            }
        }
        else {
            timeText = internal_cjs.buildSegTimeText(seg, timeFormat, context);
        }
        if (doAllDay) {
            let renderProps = {
                text: context.options.allDayText,
                view: context.viewApi,
            };
            return (preact_cjs.createElement(internal_cjs.ContentContainer, { elTag: "td", elClasses: ['fc-list-event-time'], elAttrs: {
                    headers: `${timeHeaderId} ${dateHeaderId}`,
                }, renderProps: renderProps, generatorName: "allDayContent", customGenerator: options.allDayContent, defaultGenerator: renderAllDayInner, classNameGenerator: options.allDayClassNames, didMount: options.allDayDidMount, willUnmount: options.allDayWillUnmount }));
        }
        return (preact_cjs.createElement("td", { className: "fc-list-event-time" }, timeText));
    }
    return null;
}
function renderAllDayInner(renderProps) {
    return renderProps.text;
}

/*
Responsible for the scroller, and forwarding event-related actions into the "grid".
*/
class ListView extends internal_cjs.DateComponent {
    constructor() {
        super(...arguments);
        this.computeDateVars = internal_cjs.memoize(computeDateVars);
        this.eventStoreToSegs = internal_cjs.memoize(this._eventStoreToSegs);
        this.state = {
            timeHeaderId: internal_cjs.getUniqueDomId(),
            eventHeaderId: internal_cjs.getUniqueDomId(),
            dateHeaderIdRoot: internal_cjs.getUniqueDomId(),
        };
        this.setRootEl = (rootEl) => {
            if (rootEl) {
                this.context.registerInteractiveComponent(this, {
                    el: rootEl,
                });
            }
            else {
                this.context.unregisterInteractiveComponent(this);
            }
        };
    }
    render() {
        let { props, context } = this;
        let { dayDates, dayRanges } = this.computeDateVars(props.dateProfile);
        let eventSegs = this.eventStoreToSegs(props.eventStore, props.eventUiBases, dayRanges);
        return (preact_cjs.createElement(internal_cjs.ViewContainer, { elRef: this.setRootEl, elClasses: [
                'fc-list',
                context.theme.getClass('table'),
                context.options.stickyHeaderDates !== false ?
                    'fc-list-sticky' :
                    '',
            ], viewSpec: context.viewSpec },
            preact_cjs.createElement(internal_cjs.Scroller, { liquid: !props.isHeightAuto, overflowX: props.isHeightAuto ? 'visible' : 'hidden', overflowY: props.isHeightAuto ? 'visible' : 'auto' }, eventSegs.length > 0 ?
                this.renderSegList(eventSegs, dayDates) :
                this.renderEmptyMessage())));
    }
    renderEmptyMessage() {
        let { options, viewApi } = this.context;
        let renderProps = {
            text: options.noEventsText,
            view: viewApi,
        };
        return (preact_cjs.createElement(internal_cjs.ContentContainer, { elTag: "div", elClasses: ['fc-list-empty'], renderProps: renderProps, generatorName: "noEventsContent", customGenerator: options.noEventsContent, defaultGenerator: renderNoEventsInner, classNameGenerator: options.noEventsClassNames, didMount: options.noEventsDidMount, willUnmount: options.noEventsWillUnmount }, (InnerContent) => (preact_cjs.createElement(InnerContent, { elTag: "div", elClasses: ['fc-list-empty-cushion'] }))));
    }
    renderSegList(allSegs, dayDates) {
        let { theme, options } = this.context;
        let { timeHeaderId, eventHeaderId, dateHeaderIdRoot } = this.state;
        let segsByDay = groupSegsByDay(allSegs); // sparse array
        return (preact_cjs.createElement(internal_cjs.NowTimer, { unit: "day" }, (nowDate, todayRange) => {
            let innerNodes = [];
            for (let dayIndex = 0; dayIndex < segsByDay.length; dayIndex += 1) {
                let daySegs = segsByDay[dayIndex];
                if (daySegs) { // sparse array, so might be undefined
                    let dayStr = internal_cjs.formatDayString(dayDates[dayIndex]);
                    let dateHeaderId = dateHeaderIdRoot + '-' + dayStr;
                    // append a day header
                    innerNodes.push(preact_cjs.createElement(ListViewHeaderRow, { key: dayStr, cellId: dateHeaderId, dayDate: dayDates[dayIndex], todayRange: todayRange }));
                    daySegs = internal_cjs.sortEventSegs(daySegs, options.eventOrder);
                    for (let seg of daySegs) {
                        innerNodes.push(preact_cjs.createElement(ListViewEventRow, Object.assign({ key: dayStr + ':' + seg.eventRange.instance.instanceId /* are multiple segs for an instanceId */, seg: seg, isDragging: false, isResizing: false, isDateSelecting: false, isSelected: false, timeHeaderId: timeHeaderId, eventHeaderId: eventHeaderId, dateHeaderId: dateHeaderId }, internal_cjs.getSegMeta(seg, todayRange, nowDate))));
                    }
                }
            }
            return (preact_cjs.createElement("table", { className: 'fc-list-table ' + theme.getClass('table') },
                preact_cjs.createElement("thead", null,
                    preact_cjs.createElement("tr", null,
                        preact_cjs.createElement("th", { scope: "col", id: timeHeaderId }, options.timeHint),
                        preact_cjs.createElement("th", { scope: "col", "aria-hidden": true }),
                        preact_cjs.createElement("th", { scope: "col", id: eventHeaderId }, options.eventHint))),
                preact_cjs.createElement("tbody", null, innerNodes)));
        }));
    }
    _eventStoreToSegs(eventStore, eventUiBases, dayRanges) {
        return this.eventRangesToSegs(internal_cjs.sliceEventStore(eventStore, eventUiBases, this.props.dateProfile.activeRange, this.context.options.nextDayThreshold).fg, dayRanges);
    }
    eventRangesToSegs(eventRanges, dayRanges) {
        let segs = [];
        for (let eventRange of eventRanges) {
            segs.push(...this.eventRangeToSegs(eventRange, dayRanges));
        }
        return segs;
    }
    eventRangeToSegs(eventRange, dayRanges) {
        let { dateEnv } = this.context;
        let { nextDayThreshold } = this.context.options;
        let range = eventRange.range;
        let allDay = eventRange.def.allDay;
        let dayIndex;
        let segRange;
        let seg;
        let segs = [];
        for (dayIndex = 0; dayIndex < dayRanges.length; dayIndex += 1) {
            segRange = internal_cjs.intersectRanges(range, dayRanges[dayIndex]);
            if (segRange) {
                seg = {
                    component: this,
                    eventRange,
                    start: segRange.start,
                    end: segRange.end,
                    isStart: eventRange.isStart && segRange.start.valueOf() === range.start.valueOf(),
                    isEnd: eventRange.isEnd && segRange.end.valueOf() === range.end.valueOf(),
                    dayIndex,
                };
                segs.push(seg);
                // detect when range won't go fully into the next day,
                // and mutate the latest seg to the be the end.
                if (!seg.isEnd && !allDay &&
                    dayIndex + 1 < dayRanges.length &&
                    range.end <
                        dateEnv.add(dayRanges[dayIndex + 1].start, nextDayThreshold)) {
                    seg.end = range.end;
                    seg.isEnd = true;
                    break;
                }
            }
        }
        return segs;
    }
}
function renderNoEventsInner(renderProps) {
    return renderProps.text;
}
function computeDateVars(dateProfile) {
    let dayStart = internal_cjs.startOfDay(dateProfile.renderRange.start);
    let viewEnd = dateProfile.renderRange.end;
    let dayDates = [];
    let dayRanges = [];
    while (dayStart < viewEnd) {
        dayDates.push(dayStart);
        dayRanges.push({
            start: dayStart,
            end: internal_cjs.addDays(dayStart, 1),
        });
        dayStart = internal_cjs.addDays(dayStart, 1);
    }
    return { dayDates, dayRanges };
}
// Returns a sparse array of arrays, segs grouped by their dayIndex
function groupSegsByDay(segs) {
    let segsByDay = []; // sparse array
    let i;
    let seg;
    for (i = 0; i < segs.length; i += 1) {
        seg = segs[i];
        (segsByDay[seg.dayIndex] || (segsByDay[seg.dayIndex] = []))
            .push(seg);
    }
    return segsByDay;
}

var css_248z = ":root{--fc-list-event-dot-width:10px;--fc-list-event-hover-bg-color:#f5f5f5}.fc-theme-standard .fc-list{border:1px solid var(--fc-border-color)}.fc .fc-list-empty{align-items:center;background-color:var(--fc-neutral-bg-color);display:flex;height:100%;justify-content:center}.fc .fc-list-empty-cushion{margin:5em 0}.fc .fc-list-table{border-style:hidden;width:100%}.fc .fc-list-table tr>*{border-left:0;border-right:0}.fc .fc-list-sticky .fc-list-day>*{background:var(--fc-page-bg-color);position:sticky;top:0}.fc .fc-list-table thead{left:-10000px;position:absolute}.fc .fc-list-table tbody>tr:first-child th{border-top:0}.fc .fc-list-table th{padding:0}.fc .fc-list-day-cushion,.fc .fc-list-table td{padding:8px 14px}.fc .fc-list-day-cushion:after{clear:both;content:\"\";display:table}.fc-theme-standard .fc-list-day-cushion{background-color:var(--fc-neutral-bg-color)}.fc-direction-ltr .fc-list-day-text,.fc-direction-rtl .fc-list-day-side-text{float:left}.fc-direction-ltr .fc-list-day-side-text,.fc-direction-rtl .fc-list-day-text{float:right}.fc-direction-ltr .fc-list-table .fc-list-event-graphic{padding-right:0}.fc-direction-rtl .fc-list-table .fc-list-event-graphic{padding-left:0}.fc .fc-list-event.fc-event-forced-url{cursor:pointer}.fc .fc-list-event:hover td{background-color:var(--fc-list-event-hover-bg-color)}.fc .fc-list-event-graphic,.fc .fc-list-event-time{white-space:nowrap;width:1px}.fc .fc-list-event-dot{border:calc(var(--fc-list-event-dot-width)/2) solid var(--fc-event-border-color);border-radius:calc(var(--fc-list-event-dot-width)/2);box-sizing:content-box;display:inline-block;height:0;width:0}.fc .fc-list-event-title a{color:inherit;text-decoration:none}.fc .fc-list-event.fc-event-forced-url:hover a{text-decoration:underline}";
internal_cjs.injectStyles(css_248z);

exports.ListView = ListView;
