/*!
FullCalendar Timeline Plugin v6.1.15
Docs & License: https://fullcalendar.io/docs/timeline-view-no-resources
(c) 2024 Adam Shaw
*/
FullCalendar.Timeline = (function (exports, core, premiumCommonPlugin, internal$1, preact, internal$2) {
    'use strict';

    function _interopDefault (e) { return e && e.__esModule ? e : { 'default': e }; }

    var premiumCommonPlugin__default = /*#__PURE__*/_interopDefault(premiumCommonPlugin);

    const MIN_AUTO_LABELS = 18; // more than `12` months but less that `24` hours
    const MAX_AUTO_SLOTS_PER_LABEL = 6; // allows 6 10-min slots in an hour
    const MAX_AUTO_CELLS = 200; // allows 4-days to have a :30 slot duration
    internal$1.config.MAX_TIMELINE_SLOTS = 1000;
    // potential nice values for slot-duration and interval-duration
    const STOCK_SUB_DURATIONS = [
        { years: 1 },
        { months: 1 },
        { days: 1 },
        { hours: 1 },
        { minutes: 30 },
        { minutes: 15 },
        { minutes: 10 },
        { minutes: 5 },
        { minutes: 1 },
        { seconds: 30 },
        { seconds: 15 },
        { seconds: 10 },
        { seconds: 5 },
        { seconds: 1 },
        { milliseconds: 500 },
        { milliseconds: 100 },
        { milliseconds: 10 },
        { milliseconds: 1 },
    ];
    function buildTimelineDateProfile(dateProfile, dateEnv, allOptions, dateProfileGenerator) {
        let tDateProfile = {
            labelInterval: allOptions.slotLabelInterval,
            slotDuration: allOptions.slotDuration,
        };
        validateLabelAndSlot(tDateProfile, dateProfile, dateEnv); // validate after computed grid duration
        ensureLabelInterval(tDateProfile, dateProfile, dateEnv);
        ensureSlotDuration(tDateProfile, dateProfile, dateEnv);
        let input = allOptions.slotLabelFormat;
        let rawFormats = Array.isArray(input) ? input :
            (input != null) ? [input] :
                computeHeaderFormats(tDateProfile, dateProfile, dateEnv, allOptions);
        tDateProfile.headerFormats = rawFormats.map((rawFormat) => internal$1.createFormatter(rawFormat));
        tDateProfile.isTimeScale = Boolean(tDateProfile.slotDuration.milliseconds);
        let largeUnit = null;
        if (!tDateProfile.isTimeScale) {
            const slotUnit = internal$1.greatestDurationDenominator(tDateProfile.slotDuration).unit;
            if (/year|month|week/.test(slotUnit)) {
                largeUnit = slotUnit;
            }
        }
        tDateProfile.largeUnit = largeUnit;
        tDateProfile.emphasizeWeeks =
            internal$1.asCleanDays(tDateProfile.slotDuration) === 1 &&
                currentRangeAs('weeks', dateProfile, dateEnv) >= 2 &&
                !allOptions.businessHours;
        /*
        console.log('label interval =', timelineView.labelInterval.humanize())
        console.log('slot duration =', timelineView.slotDuration.humanize())
        console.log('header formats =', timelineView.headerFormats)
        console.log('isTimeScale', timelineView.isTimeScale)
        console.log('largeUnit', timelineView.largeUnit)
        */
        let rawSnapDuration = allOptions.snapDuration;
        let snapDuration;
        let snapsPerSlot;
        if (rawSnapDuration) {
            snapDuration = internal$1.createDuration(rawSnapDuration);
            snapsPerSlot = internal$1.wholeDivideDurations(tDateProfile.slotDuration, snapDuration);
            // ^ TODO: warning if not whole?
        }
        if (snapsPerSlot == null) {
            snapDuration = tDateProfile.slotDuration;
            snapsPerSlot = 1;
        }
        tDateProfile.snapDuration = snapDuration;
        tDateProfile.snapsPerSlot = snapsPerSlot;
        // more...
        let timeWindowMs = internal$1.asRoughMs(dateProfile.slotMaxTime) - internal$1.asRoughMs(dateProfile.slotMinTime);
        // TODO: why not use normalizeRange!?
        let normalizedStart = normalizeDate(dateProfile.renderRange.start, tDateProfile, dateEnv);
        let normalizedEnd = normalizeDate(dateProfile.renderRange.end, tDateProfile, dateEnv);
        // apply slotMinTime/slotMaxTime
        // TODO: View should be responsible.
        if (tDateProfile.isTimeScale) {
            normalizedStart = dateEnv.add(normalizedStart, dateProfile.slotMinTime);
            normalizedEnd = dateEnv.add(internal$1.addDays(normalizedEnd, -1), dateProfile.slotMaxTime);
        }
        tDateProfile.timeWindowMs = timeWindowMs;
        tDateProfile.normalizedRange = { start: normalizedStart, end: normalizedEnd };
        let slotDates = [];
        let date = normalizedStart;
        while (date < normalizedEnd) {
            if (isValidDate(date, tDateProfile, dateProfile, dateProfileGenerator)) {
                slotDates.push(date);
            }
            date = dateEnv.add(date, tDateProfile.slotDuration);
        }
        tDateProfile.slotDates = slotDates;
        // more...
        let snapIndex = -1;
        let snapDiff = 0; // index of the diff :(
        const snapDiffToIndex = [];
        const snapIndexToDiff = [];
        date = normalizedStart;
        while (date < normalizedEnd) {
            if (isValidDate(date, tDateProfile, dateProfile, dateProfileGenerator)) {
                snapIndex += 1;
                snapDiffToIndex.push(snapIndex);
                snapIndexToDiff.push(snapDiff);
            }
            else {
                snapDiffToIndex.push(snapIndex + 0.5);
            }
            date = dateEnv.add(date, tDateProfile.snapDuration);
            snapDiff += 1;
        }
        tDateProfile.snapDiffToIndex = snapDiffToIndex;
        tDateProfile.snapIndexToDiff = snapIndexToDiff;
        tDateProfile.snapCnt = snapIndex + 1; // is always one behind
        tDateProfile.slotCnt = tDateProfile.snapCnt / tDateProfile.snapsPerSlot;
        // more...
        tDateProfile.isWeekStarts = buildIsWeekStarts(tDateProfile, dateEnv);
        tDateProfile.cellRows = buildCellRows(tDateProfile, dateEnv);
        tDateProfile.slotsPerLabel = internal$1.wholeDivideDurations(tDateProfile.labelInterval, tDateProfile.slotDuration);
        return tDateProfile;
    }
    /*
    snaps to appropriate unit
    */
    function normalizeDate(date, tDateProfile, dateEnv) {
        let normalDate = date;
        if (!tDateProfile.isTimeScale) {
            normalDate = internal$1.startOfDay(normalDate);
            if (tDateProfile.largeUnit) {
                normalDate = dateEnv.startOf(normalDate, tDateProfile.largeUnit);
            }
        }
        return normalDate;
    }
    /*
    snaps to appropriate unit
    */
    function normalizeRange(range, tDateProfile, dateEnv) {
        if (!tDateProfile.isTimeScale) {
            range = internal$1.computeVisibleDayRange(range);
            if (tDateProfile.largeUnit) {
                let dayRange = range; // preserve original result
                range = {
                    start: dateEnv.startOf(range.start, tDateProfile.largeUnit),
                    end: dateEnv.startOf(range.end, tDateProfile.largeUnit),
                };
                // if date is partially through the interval, or is in the same interval as the start,
                // make the exclusive end be the *next* interval
                if (range.end.valueOf() !== dayRange.end.valueOf() || range.end <= range.start) {
                    range = {
                        start: range.start,
                        end: dateEnv.add(range.end, tDateProfile.slotDuration),
                    };
                }
            }
        }
        return range;
    }
    function isValidDate(date, tDateProfile, dateProfile, dateProfileGenerator) {
        if (dateProfileGenerator.isHiddenDay(date)) {
            return false;
        }
        if (tDateProfile.isTimeScale) {
            // determine if the time is within slotMinTime/slotMaxTime, which may have wacky values
            let day = internal$1.startOfDay(date);
            let timeMs = date.valueOf() - day.valueOf();
            let ms = timeMs - internal$1.asRoughMs(dateProfile.slotMinTime); // milliseconds since slotMinTime
            ms = ((ms % 86400000) + 86400000) % 86400000; // make negative values wrap to 24hr clock
            return ms < tDateProfile.timeWindowMs; // before the slotMaxTime?
        }
        return true;
    }
    function validateLabelAndSlot(tDateProfile, dateProfile, dateEnv) {
        const { currentRange } = dateProfile;
        // make sure labelInterval doesn't exceed the max number of cells
        if (tDateProfile.labelInterval) {
            const labelCnt = dateEnv.countDurationsBetween(currentRange.start, currentRange.end, tDateProfile.labelInterval);
            if (labelCnt > internal$1.config.MAX_TIMELINE_SLOTS) {
                console.warn('slotLabelInterval results in too many cells');
                tDateProfile.labelInterval = null;
            }
        }
        // make sure slotDuration doesn't exceed the maximum number of cells
        if (tDateProfile.slotDuration) {
            const slotCnt = dateEnv.countDurationsBetween(currentRange.start, currentRange.end, tDateProfile.slotDuration);
            if (slotCnt > internal$1.config.MAX_TIMELINE_SLOTS) {
                console.warn('slotDuration results in too many cells');
                tDateProfile.slotDuration = null;
            }
        }
        // make sure labelInterval is a multiple of slotDuration
        if (tDateProfile.labelInterval && tDateProfile.slotDuration) {
            const slotsPerLabel = internal$1.wholeDivideDurations(tDateProfile.labelInterval, tDateProfile.slotDuration);
            if (slotsPerLabel === null || slotsPerLabel < 1) {
                console.warn('slotLabelInterval must be a multiple of slotDuration');
                tDateProfile.slotDuration = null;
            }
        }
    }
    function ensureLabelInterval(tDateProfile, dateProfile, dateEnv) {
        const { currentRange } = dateProfile;
        let { labelInterval } = tDateProfile;
        if (!labelInterval) {
            // compute based off the slot duration
            // find the largest label interval with an acceptable slots-per-label
            let input;
            if (tDateProfile.slotDuration) {
                for (input of STOCK_SUB_DURATIONS) {
                    const tryLabelInterval = internal$1.createDuration(input);
                    const slotsPerLabel = internal$1.wholeDivideDurations(tryLabelInterval, tDateProfile.slotDuration);
                    if (slotsPerLabel !== null && slotsPerLabel <= MAX_AUTO_SLOTS_PER_LABEL) {
                        labelInterval = tryLabelInterval;
                        break;
                    }
                }
                // use the slot duration as a last resort
                if (!labelInterval) {
                    labelInterval = tDateProfile.slotDuration;
                }
                // compute based off the view's duration
                // find the largest label interval that yields the minimum number of labels
            }
            else {
                for (input of STOCK_SUB_DURATIONS) {
                    labelInterval = internal$1.createDuration(input);
                    const labelCnt = dateEnv.countDurationsBetween(currentRange.start, currentRange.end, labelInterval);
                    if (labelCnt >= MIN_AUTO_LABELS) {
                        break;
                    }
                }
            }
            tDateProfile.labelInterval = labelInterval;
        }
        return labelInterval;
    }
    function ensureSlotDuration(tDateProfile, dateProfile, dateEnv) {
        const { currentRange } = dateProfile;
        let { slotDuration } = tDateProfile;
        if (!slotDuration) {
            const labelInterval = ensureLabelInterval(tDateProfile, dateProfile, dateEnv); // will compute if necessary
            // compute based off the label interval
            // find the largest slot duration that is different from labelInterval, but still acceptable
            for (let input of STOCK_SUB_DURATIONS) {
                const trySlotDuration = internal$1.createDuration(input);
                const slotsPerLabel = internal$1.wholeDivideDurations(labelInterval, trySlotDuration);
                if (slotsPerLabel !== null && slotsPerLabel > 1 && slotsPerLabel <= MAX_AUTO_SLOTS_PER_LABEL) {
                    slotDuration = trySlotDuration;
                    break;
                }
            }
            // only allow the value if it won't exceed the view's # of slots limit
            if (slotDuration) {
                const slotCnt = dateEnv.countDurationsBetween(currentRange.start, currentRange.end, slotDuration);
                if (slotCnt > MAX_AUTO_CELLS) {
                    slotDuration = null;
                }
            }
            // use the label interval as a last resort
            if (!slotDuration) {
                slotDuration = labelInterval;
            }
            tDateProfile.slotDuration = slotDuration;
        }
        return slotDuration;
    }
    function computeHeaderFormats(tDateProfile, dateProfile, dateEnv, allOptions) {
        let format1;
        let format2;
        const { labelInterval } = tDateProfile;
        let unit = internal$1.greatestDurationDenominator(labelInterval).unit;
        const weekNumbersVisible = allOptions.weekNumbers;
        let format0 = (format1 = (format2 = null));
        // NOTE: weekNumber computation function wont work
        if ((unit === 'week') && !weekNumbersVisible) {
            unit = 'day';
        }
        switch (unit) {
            case 'year':
                format0 = { year: 'numeric' }; // '2015'
                break;
            case 'month':
                if (currentRangeAs('years', dateProfile, dateEnv) > 1) {
                    format0 = { year: 'numeric' }; // '2015'
                }
                format1 = { month: 'short' }; // 'Jan'
                break;
            case 'week':
                if (currentRangeAs('years', dateProfile, dateEnv) > 1) {
                    format0 = { year: 'numeric' }; // '2015'
                }
                format1 = { week: 'narrow' }; // 'Wk4'
                break;
            case 'day':
                if (currentRangeAs('years', dateProfile, dateEnv) > 1) {
                    format0 = { year: 'numeric', month: 'long' }; // 'January 2014'
                }
                else if (currentRangeAs('months', dateProfile, dateEnv) > 1) {
                    format0 = { month: 'long' }; // 'January'
                }
                if (weekNumbersVisible) {
                    format1 = { week: 'short' }; // 'Wk 4'
                }
                format2 = { weekday: 'narrow', day: 'numeric' }; // 'Su 9'
                break;
            case 'hour':
                if (weekNumbersVisible) {
                    format0 = { week: 'short' }; // 'Wk 4'
                }
                if (currentRangeAs('days', dateProfile, dateEnv) > 1) {
                    format1 = { weekday: 'short', day: 'numeric', month: 'numeric', omitCommas: true }; // Sat 4/7
                }
                format2 = {
                    hour: 'numeric',
                    minute: '2-digit',
                    omitZeroMinute: true,
                    meridiem: 'short',
                };
                break;
            case 'minute':
                // sufficiently large number of different minute cells?
                if ((internal$1.asRoughMinutes(labelInterval) / 60) >= MAX_AUTO_SLOTS_PER_LABEL) {
                    format0 = {
                        hour: 'numeric',
                        meridiem: 'short',
                    };
                    format1 = (params) => (':' + internal$1.padStart(params.date.minute, 2) // ':30'
                    );
                }
                else {
                    format0 = {
                        hour: 'numeric',
                        minute: 'numeric',
                        meridiem: 'short',
                    };
                }
                break;
            case 'second':
                // sufficiently large number of different second cells?
                if ((internal$1.asRoughSeconds(labelInterval) / 60) >= MAX_AUTO_SLOTS_PER_LABEL) {
                    format0 = { hour: 'numeric', minute: '2-digit', meridiem: 'lowercase' }; // '8:30 PM'
                    format1 = (params) => (':' + internal$1.padStart(params.date.second, 2) // ':30'
                    );
                }
                else {
                    format0 = { hour: 'numeric', minute: '2-digit', second: '2-digit', meridiem: 'lowercase' }; // '8:30:45 PM'
                }
                break;
            case 'millisecond':
                format0 = { hour: 'numeric', minute: '2-digit', second: '2-digit', meridiem: 'lowercase' }; // '8:30:45 PM'
                format1 = (params) => ('.' + internal$1.padStart(params.millisecond, 3));
                break;
        }
        return [].concat(format0 || [], format1 || [], format2 || []);
    }
    // Compute the number of the give units in the "current" range.
    // Won't go more precise than days.
    // Will return `0` if there's not a clean whole interval.
    function currentRangeAs(unit, dateProfile, dateEnv) {
        let range = dateProfile.currentRange;
        let res = null;
        if (unit === 'years') {
            res = dateEnv.diffWholeYears(range.start, range.end);
        }
        else if (unit === 'months') {
            res = dateEnv.diffWholeMonths(range.start, range.end);
        }
        else if (unit === 'weeks') {
            res = dateEnv.diffWholeMonths(range.start, range.end);
        }
        else if (unit === 'days') {
            res = internal$1.diffWholeDays(range.start, range.end);
        }
        return res || 0;
    }
    function buildIsWeekStarts(tDateProfile, dateEnv) {
        let { slotDates, emphasizeWeeks } = tDateProfile;
        let prevWeekNumber = null;
        let isWeekStarts = [];
        for (let slotDate of slotDates) {
            let weekNumber = dateEnv.computeWeekNumber(slotDate);
            let isWeekStart = emphasizeWeeks && (prevWeekNumber !== null) && (prevWeekNumber !== weekNumber);
            prevWeekNumber = weekNumber;
            isWeekStarts.push(isWeekStart);
        }
        return isWeekStarts;
    }
    function buildCellRows(tDateProfile, dateEnv) {
        let slotDates = tDateProfile.slotDates;
        let formats = tDateProfile.headerFormats;
        let cellRows = formats.map(() => []); // indexed by row,col
        let slotAsDays = internal$1.asCleanDays(tDateProfile.slotDuration);
        let guessedSlotUnit = slotAsDays === 7 ? 'week' :
            slotAsDays === 1 ? 'day' :
                null;
        // specifically for navclicks
        let rowUnitsFromFormats = formats.map((format) => (format.getLargestUnit ? format.getLargestUnit() : null));
        // builds cellRows and slotCells
        for (let i = 0; i < slotDates.length; i += 1) {
            let date = slotDates[i];
            let isWeekStart = tDateProfile.isWeekStarts[i];
            for (let row = 0; row < formats.length; row += 1) {
                let format = formats[row];
                let rowCells = cellRows[row];
                let leadingCell = rowCells[rowCells.length - 1];
                let isLastRow = row === formats.length - 1;
                let isSuperRow = formats.length > 1 && !isLastRow; // more than one row and not the last
                let newCell = null;
                let rowUnit = rowUnitsFromFormats[row] || (isLastRow ? guessedSlotUnit : null);
                if (isSuperRow) {
                    let text = dateEnv.format(date, format);
                    if (!leadingCell || (leadingCell.text !== text)) {
                        newCell = buildCellObject(date, text, rowUnit);
                    }
                    else {
                        leadingCell.colspan += 1;
                    }
                }
                else if (!leadingCell ||
                    internal$1.isInt(dateEnv.countDurationsBetween(tDateProfile.normalizedRange.start, date, tDateProfile.labelInterval))) {
                    let text = dateEnv.format(date, format);
                    newCell = buildCellObject(date, text, rowUnit);
                }
                else {
                    leadingCell.colspan += 1;
                }
                if (newCell) {
                    newCell.weekStart = isWeekStart;
                    rowCells.push(newCell);
                }
            }
        }
        return cellRows;
    }
    function buildCellObject(date, text, rowUnit) {
        return { date, text, rowUnit, colspan: 1, isWeekStart: false };
    }

    class TimelineHeaderTh extends internal$1.BaseComponent {
        constructor() {
            super(...arguments);
            this.refineRenderProps = internal$1.memoizeObjArg(refineRenderProps);
            this.buildCellNavLinkAttrs = internal$1.memoize(buildCellNavLinkAttrs);
        }
        render() {
            let { props, context } = this;
            let { dateEnv, options } = context;
            let { cell, dateProfile, tDateProfile } = props;
            // the cell.rowUnit is f'd
            // giving 'month' for a 3-day view
            // workaround: to infer day, do NOT time
            let dateMeta = internal$1.getDateMeta(cell.date, props.todayRange, props.nowDate, dateProfile);
            let renderProps = this.refineRenderProps({
                level: props.rowLevel,
                dateMarker: cell.date,
                text: cell.text,
                dateEnv: context.dateEnv,
                viewApi: context.viewApi,
            });
            return (preact.createElement(internal$1.ContentContainer, { elTag: "th", elClasses: [
                    'fc-timeline-slot',
                    'fc-timeline-slot-label',
                    cell.isWeekStart && 'fc-timeline-slot-em',
                    ...( // TODO: so slot classnames for week/month/bigger. see note above about rowUnit
                    cell.rowUnit === 'time' ?
                        internal$1.getSlotClassNames(dateMeta, context.theme) :
                        internal$1.getDayClassNames(dateMeta, context.theme)),
                ], elAttrs: {
                    colSpan: cell.colspan,
                    'data-date': dateEnv.formatIso(cell.date, {
                        omitTime: !tDateProfile.isTimeScale,
                        omitTimeZoneOffset: true,
                    }),
                }, renderProps: renderProps, generatorName: "slotLabelContent", customGenerator: options.slotLabelContent, defaultGenerator: renderInnerContent, classNameGenerator: options.slotLabelClassNames, didMount: options.slotLabelDidMount, willUnmount: options.slotLabelWillUnmount }, (InnerContent) => (preact.createElement("div", { className: "fc-timeline-slot-frame", style: { height: props.rowInnerHeight } },
                preact.createElement(InnerContent, { elTag: "a", elClasses: [
                        'fc-timeline-slot-cushion',
                        'fc-scrollgrid-sync-inner',
                        props.isSticky && 'fc-sticky',
                    ], elAttrs: this.buildCellNavLinkAttrs(context, cell.date, cell.rowUnit) })))));
        }
    }
    function buildCellNavLinkAttrs(context, cellDate, rowUnit) {
        return (rowUnit && rowUnit !== 'time')
            ? internal$1.buildNavLinkAttrs(context, cellDate, rowUnit)
            : {};
    }
    function renderInnerContent(renderProps) {
        return renderProps.text;
    }
    function refineRenderProps(input) {
        return {
            level: input.level,
            date: input.dateEnv.toDate(input.dateMarker),
            view: input.viewApi,
            text: input.text,
        };
    }

    class TimelineHeaderRows extends internal$1.BaseComponent {
        render() {
            let { dateProfile, tDateProfile, rowInnerHeights, todayRange, nowDate } = this.props;
            let { cellRows } = tDateProfile;
            return (preact.createElement(preact.Fragment, null, cellRows.map((rowCells, rowLevel) => {
                let isLast = rowLevel === cellRows.length - 1;
                let isChrono = tDateProfile.isTimeScale && isLast; // the final row, with times?
                let classNames = [
                    'fc-timeline-header-row',
                    isChrono ? 'fc-timeline-header-row-chrono' : '',
                ];
                return ( // eslint-disable-next-line react/no-array-index-key
                preact.createElement("tr", { key: rowLevel, className: classNames.join(' ') }, rowCells.map((cell) => (preact.createElement(TimelineHeaderTh, { key: cell.date.toISOString(), cell: cell, rowLevel: rowLevel, dateProfile: dateProfile, tDateProfile: tDateProfile, todayRange: todayRange, nowDate: nowDate, rowInnerHeight: rowInnerHeights && rowInnerHeights[rowLevel], isSticky: !isLast })))));
            })));
        }
    }

    class TimelineCoords {
        constructor(slatRootEl, // okay to expose?
        slatEls, dateProfile, tDateProfile, dateEnv, isRtl) {
            this.slatRootEl = slatRootEl;
            this.dateProfile = dateProfile;
            this.tDateProfile = tDateProfile;
            this.dateEnv = dateEnv;
            this.isRtl = isRtl;
            this.outerCoordCache = new internal$1.PositionCache(slatRootEl, slatEls, true, // isHorizontal
            false);
            // for the inner divs within the slats
            // used for event rendering and scrollTime, to disregard slat border
            this.innerCoordCache = new internal$1.PositionCache(slatRootEl, internal$1.findDirectChildren(slatEls, 'div'), true, // isHorizontal
            false);
        }
        isDateInRange(date) {
            return internal$1.rangeContainsMarker(this.dateProfile.currentRange, date);
        }
        // results range from negative width of area to 0
        dateToCoord(date) {
            let { tDateProfile } = this;
            let snapCoverage = this.computeDateSnapCoverage(date);
            let slotCoverage = snapCoverage / tDateProfile.snapsPerSlot;
            let slotIndex = Math.floor(slotCoverage);
            slotIndex = Math.min(slotIndex, tDateProfile.slotCnt - 1);
            let partial = slotCoverage - slotIndex;
            let { innerCoordCache, outerCoordCache } = this;
            if (this.isRtl) {
                return outerCoordCache.originClientRect.width - (outerCoordCache.rights[slotIndex] -
                    (innerCoordCache.getWidth(slotIndex) * partial));
            }
            return (outerCoordCache.lefts[slotIndex] +
                (innerCoordCache.getWidth(slotIndex) * partial));
        }
        rangeToCoords(range) {
            return {
                start: this.dateToCoord(range.start),
                end: this.dateToCoord(range.end),
            };
        }
        durationToCoord(duration) {
            let { dateProfile, tDateProfile, dateEnv, isRtl } = this;
            let coord = 0;
            if (dateProfile) {
                let date = dateEnv.add(dateProfile.activeRange.start, duration);
                if (!tDateProfile.isTimeScale) {
                    date = internal$1.startOfDay(date);
                }
                coord = this.dateToCoord(date);
                // hack to overcome the left borders of non-first slat
                if (!isRtl && coord) {
                    coord += 1;
                }
            }
            return coord;
        }
        coordFromLeft(coord) {
            if (this.isRtl) {
                return this.outerCoordCache.originClientRect.width - coord;
            }
            return coord;
        }
        // returned value is between 0 and the number of snaps
        computeDateSnapCoverage(date) {
            return computeDateSnapCoverage(date, this.tDateProfile, this.dateEnv);
        }
    }
    // returned value is between 0 and the number of snaps
    function computeDateSnapCoverage(date, tDateProfile, dateEnv) {
        let snapDiff = dateEnv.countDurationsBetween(tDateProfile.normalizedRange.start, date, tDateProfile.snapDuration);
        if (snapDiff < 0) {
            return 0;
        }
        if (snapDiff >= tDateProfile.snapDiffToIndex.length) {
            return tDateProfile.snapCnt;
        }
        let snapDiffInt = Math.floor(snapDiff);
        let snapCoverage = tDateProfile.snapDiffToIndex[snapDiffInt];
        if (internal$1.isInt(snapCoverage)) { // not an in-between value
            snapCoverage += snapDiff - snapDiffInt; // add the remainder
        }
        else {
            // a fractional value, meaning the date is not visible
            // always round up in this case. works for start AND end dates in a range.
            snapCoverage = Math.ceil(snapCoverage);
        }
        return snapCoverage;
    }
    function coordToCss(hcoord, isRtl) {
        if (hcoord === null) {
            return { left: '', right: '' };
        }
        if (isRtl) {
            return { right: hcoord, left: '' };
        }
        return { left: hcoord, right: '' };
    }
    function coordsToCss(hcoords, isRtl) {
        if (!hcoords) {
            return { left: '', right: '' };
        }
        if (isRtl) {
            return { right: hcoords.start, left: -hcoords.end };
        }
        return { left: hcoords.start, right: -hcoords.end };
    }

    class TimelineHeader extends internal$1.BaseComponent {
        constructor() {
            super(...arguments);
            this.rootElRef = preact.createRef();
        }
        render() {
            let { props, context } = this;
            // TODO: very repetitive
            // TODO: make part of tDateProfile?
            let timerUnit = internal$1.greatestDurationDenominator(props.tDateProfile.slotDuration).unit;
            // WORKAROUND: make ignore slatCoords when out of sync with dateProfile
            let slatCoords = props.slatCoords && props.slatCoords.dateProfile === props.dateProfile ? props.slatCoords : null;
            return (preact.createElement(internal$1.NowTimer, { unit: timerUnit }, (nowDate, todayRange) => (preact.createElement("div", { className: "fc-timeline-header", ref: this.rootElRef },
                preact.createElement("table", { "aria-hidden": true, className: "fc-scrollgrid-sync-table", style: { minWidth: props.tableMinWidth, width: props.clientWidth } },
                    props.tableColGroupNode,
                    preact.createElement("tbody", null,
                        preact.createElement(TimelineHeaderRows, { dateProfile: props.dateProfile, tDateProfile: props.tDateProfile, nowDate: nowDate, todayRange: todayRange, rowInnerHeights: props.rowInnerHeights }))),
                context.options.nowIndicator && (
                // need to have a container regardless of whether the current view has a visible now indicator
                // because apparently removal of the element resets the scroll for some reasons (issue #5351).
                // this issue doesn't happen for the timeline body however (
                preact.createElement("div", { className: "fc-timeline-now-indicator-container" }, (slatCoords && slatCoords.isDateInRange(nowDate)) && (preact.createElement(internal$1.NowIndicatorContainer, { elClasses: ['fc-timeline-now-indicator-arrow'], elStyle: coordToCss(slatCoords.dateToCoord(nowDate), context.isRtl), isAxis: true, date: nowDate }))))))));
        }
        componentDidMount() {
            this.updateSize();
        }
        componentDidUpdate() {
            this.updateSize();
        }
        updateSize() {
            if (this.props.onMaxCushionWidth) {
                this.props.onMaxCushionWidth(this.computeMaxCushionWidth());
            }
        }
        computeMaxCushionWidth() {
            return Math.max(...internal$1.findElements(this.rootElRef.current, '.fc-timeline-header-row:last-child .fc-timeline-slot-cushion').map((el) => el.getBoundingClientRect().width));
        }
    }

    class TimelineSlatCell extends internal$1.BaseComponent {
        render() {
            let { props, context } = this;
            let { dateEnv, options, theme } = context;
            let { date, tDateProfile, isEm } = props;
            let dateMeta = internal$1.getDateMeta(props.date, props.todayRange, props.nowDate, props.dateProfile);
            let renderProps = Object.assign(Object.assign({ date: dateEnv.toDate(props.date) }, dateMeta), { view: context.viewApi });
            return (preact.createElement(internal$1.ContentContainer, { elTag: "td", elRef: props.elRef, elClasses: [
                    'fc-timeline-slot',
                    'fc-timeline-slot-lane',
                    isEm && 'fc-timeline-slot-em',
                    tDateProfile.isTimeScale ? (internal$1.isInt(dateEnv.countDurationsBetween(tDateProfile.normalizedRange.start, props.date, tDateProfile.labelInterval)) ?
                        'fc-timeline-slot-major' :
                        'fc-timeline-slot-minor') : '',
                    ...(props.isDay ?
                        internal$1.getDayClassNames(dateMeta, theme) :
                        internal$1.getSlotClassNames(dateMeta, theme)),
                ], elAttrs: {
                    'data-date': dateEnv.formatIso(date, {
                        omitTimeZoneOffset: true,
                        omitTime: !tDateProfile.isTimeScale,
                    }),
                }, renderProps: renderProps, generatorName: "slotLaneContent", customGenerator: options.slotLaneContent, classNameGenerator: options.slotLaneClassNames, didMount: options.slotLaneDidMount, willUnmount: options.slotLaneWillUnmount }, (InnerContent) => (preact.createElement(InnerContent, { elTag: "div" }))));
        }
    }

    class TimelineSlatsBody extends internal$1.BaseComponent {
        render() {
            let { props } = this;
            let { tDateProfile, cellElRefs } = props;
            let { slotDates, isWeekStarts } = tDateProfile;
            let isDay = !tDateProfile.isTimeScale && !tDateProfile.largeUnit;
            return (preact.createElement("tbody", null,
                preact.createElement("tr", null, slotDates.map((slotDate, i) => {
                    let key = slotDate.toISOString();
                    return (preact.createElement(TimelineSlatCell, { key: key, elRef: cellElRefs.createRef(key), date: slotDate, dateProfile: props.dateProfile, tDateProfile: tDateProfile, nowDate: props.nowDate, todayRange: props.todayRange, isEm: isWeekStarts[i], isDay: isDay }));
                }))));
        }
    }

    class TimelineSlats extends internal$1.BaseComponent {
        constructor() {
            super(...arguments);
            this.rootElRef = preact.createRef();
            this.cellElRefs = new internal$1.RefMap();
            this.handleScrollRequest = (request) => {
                let { onScrollLeftRequest } = this.props;
                let { coords } = this;
                if (onScrollLeftRequest && coords) {
                    if (request.time) {
                        let scrollLeft = coords.coordFromLeft(coords.durationToCoord(request.time));
                        onScrollLeftRequest(scrollLeft);
                    }
                    return true;
                }
                return null; // best?
            };
        }
        render() {
            let { props, context } = this;
            return (preact.createElement("div", { className: "fc-timeline-slots", ref: this.rootElRef },
                preact.createElement("table", { "aria-hidden": true, className: context.theme.getClass('table'), style: {
                        minWidth: props.tableMinWidth,
                        width: props.clientWidth,
                    } },
                    props.tableColGroupNode,
                    preact.createElement(TimelineSlatsBody, { cellElRefs: this.cellElRefs, dateProfile: props.dateProfile, tDateProfile: props.tDateProfile, nowDate: props.nowDate, todayRange: props.todayRange }))));
        }
        componentDidMount() {
            this.updateSizing();
            this.scrollResponder = this.context.createScrollResponder(this.handleScrollRequest);
        }
        componentDidUpdate(prevProps) {
            this.updateSizing();
            this.scrollResponder.update(prevProps.dateProfile !== this.props.dateProfile);
        }
        componentWillUnmount() {
            this.scrollResponder.detach();
            if (this.props.onCoords) {
                this.props.onCoords(null);
            }
        }
        updateSizing() {
            let { props, context } = this;
            if (props.clientWidth !== null && // is sizing stable?
                this.scrollResponder
            // ^it's possible to have clientWidth immediately after mount (when returning from print view), but w/o scrollResponder
            ) {
                let rootEl = this.rootElRef.current;
                if (rootEl.offsetWidth) { // not hidden by css
                    this.coords = new TimelineCoords(this.rootElRef.current, collectCellEls(this.cellElRefs.currentMap, props.tDateProfile.slotDates), props.dateProfile, props.tDateProfile, context.dateEnv, context.isRtl);
                    if (props.onCoords) {
                        props.onCoords(this.coords);
                    }
                    this.scrollResponder.update(false); // TODO: wouldn't have to do this if coords were in state
                }
            }
        }
        positionToHit(leftPosition) {
            let { outerCoordCache } = this.coords;
            let { dateEnv, isRtl } = this.context;
            let { tDateProfile } = this.props;
            let slatIndex = outerCoordCache.leftToIndex(leftPosition);
            if (slatIndex != null) {
                // somewhat similar to what TimeGrid does. consolidate?
                let slatWidth = outerCoordCache.getWidth(slatIndex);
                let partial = isRtl ?
                    (outerCoordCache.rights[slatIndex] - leftPosition) / slatWidth :
                    (leftPosition - outerCoordCache.lefts[slatIndex]) / slatWidth;
                let localSnapIndex = Math.floor(partial * tDateProfile.snapsPerSlot);
                let start = dateEnv.add(tDateProfile.slotDates[slatIndex], internal$1.multiplyDuration(tDateProfile.snapDuration, localSnapIndex));
                let end = dateEnv.add(start, tDateProfile.snapDuration);
                return {
                    dateSpan: {
                        range: { start, end },
                        allDay: !this.props.tDateProfile.isTimeScale,
                    },
                    dayEl: this.cellElRefs.currentMap[slatIndex],
                    left: outerCoordCache.lefts[slatIndex],
                    right: outerCoordCache.rights[slatIndex],
                };
            }
            return null;
        }
    }
    function collectCellEls(elMap, slotDates) {
        return slotDates.map((slotDate) => {
            let key = slotDate.toISOString();
            return elMap[key];
        });
    }

    function computeSegHCoords(segs, minWidth, timelineCoords) {
        let hcoords = [];
        if (timelineCoords) {
            for (let seg of segs) {
                let res = timelineCoords.rangeToCoords(seg);
                let start = Math.round(res.start); // for barely-overlapping collisions
                let end = Math.round(res.end); //
                if (end - start < minWidth) {
                    end = start + minWidth;
                }
                hcoords.push({ start, end });
            }
        }
        return hcoords;
    }
    function computeFgSegPlacements(segs, segHCoords, // might not have for every seg
    eventInstanceHeights, // might not have for every seg
    moreLinkHeights, // might not have for every more-link
    strictOrder, maxStackCnt) {
        let segInputs = [];
        let crudePlacements = []; // when we don't know dims
        for (let i = 0; i < segs.length; i += 1) {
            let seg = segs[i];
            let instanceId = seg.eventRange.instance.instanceId;
            let height = eventInstanceHeights[instanceId];
            let hcoords = segHCoords[i];
            if (height && hcoords) {
                segInputs.push({
                    index: i,
                    span: hcoords,
                    thickness: height,
                });
            }
            else {
                crudePlacements.push({
                    seg,
                    hcoords,
                    top: null,
                });
            }
        }
        let hierarchy = new internal$1.SegHierarchy();
        if (strictOrder != null) {
            hierarchy.strictOrder = strictOrder;
        }
        if (maxStackCnt != null) {
            hierarchy.maxStackCnt = maxStackCnt;
        }
        let hiddenEntries = hierarchy.addSegs(segInputs);
        let hiddenPlacements = hiddenEntries.map((entry) => ({
            seg: segs[entry.index],
            hcoords: entry.span,
            top: null,
        }));
        let hiddenGroups = internal$1.groupIntersectingEntries(hiddenEntries);
        let moreLinkInputs = [];
        let moreLinkCrudePlacements = [];
        const extractSeg = (entry) => segs[entry.index];
        for (let i = 0; i < hiddenGroups.length; i += 1) {
            let hiddenGroup = hiddenGroups[i];
            let sortedSegs = hiddenGroup.entries.map(extractSeg);
            let height = moreLinkHeights[internal$1.buildIsoString(internal$1.computeEarliestSegStart(sortedSegs))]; // not optimal :(
            if (height != null) {
                // NOTE: the hiddenGroup's spanStart/spanEnd are already computed by rangeToCoords. computed during input.
                moreLinkInputs.push({
                    index: segs.length + i,
                    thickness: height,
                    span: hiddenGroup.span,
                });
            }
            else {
                moreLinkCrudePlacements.push({
                    seg: sortedSegs,
                    hcoords: hiddenGroup.span,
                    top: null,
                });
            }
        }
        // add more-links into the hierarchy, but don't limit
        hierarchy.maxStackCnt = -1;
        hierarchy.addSegs(moreLinkInputs);
        let visibleRects = hierarchy.toRects();
        let visiblePlacements = [];
        let maxHeight = 0;
        for (let rect of visibleRects) {
            let segIndex = rect.index;
            visiblePlacements.push({
                seg: segIndex < segs.length
                    ? segs[segIndex] // a real seg
                    : hiddenGroups[segIndex - segs.length].entries.map(extractSeg),
                hcoords: rect.span,
                top: rect.levelCoord,
            });
            maxHeight = Math.max(maxHeight, rect.levelCoord + rect.thickness);
        }
        return [
            visiblePlacements.concat(crudePlacements, hiddenPlacements, moreLinkCrudePlacements),
            maxHeight,
        ];
    }

    class TimelineLaneBg extends internal$1.BaseComponent {
        render() {
            let { props } = this;
            let highlightSeg = [].concat(props.eventResizeSegs, props.dateSelectionSegs);
            return props.timelineCoords && (preact.createElement("div", { className: "fc-timeline-bg" },
                this.renderSegs(props.businessHourSegs || [], props.timelineCoords, 'non-business'),
                this.renderSegs(props.bgEventSegs || [], props.timelineCoords, 'bg-event'),
                this.renderSegs(highlightSeg, props.timelineCoords, 'highlight')));
        }
        renderSegs(segs, timelineCoords, fillType) {
            let { todayRange, nowDate } = this.props;
            let { isRtl } = this.context;
            let segHCoords = computeSegHCoords(segs, 0, timelineCoords);
            let children = segs.map((seg, i) => {
                let hcoords = segHCoords[i];
                let hStyle = coordsToCss(hcoords, isRtl);
                return (preact.createElement("div", { key: internal$1.buildEventRangeKey(seg.eventRange), className: "fc-timeline-bg-harness", style: hStyle }, fillType === 'bg-event' ?
                    preact.createElement(internal$1.BgEvent, Object.assign({ seg: seg }, internal$1.getSegMeta(seg, todayRange, nowDate))) :
                    internal$1.renderFill(fillType)));
            });
            return preact.createElement(preact.Fragment, null, children);
        }
    }

    class TimelineLaneSlicer extends internal$1.Slicer {
        sliceRange(origRange, dateProfile, dateProfileGenerator, tDateProfile, dateEnv) {
            let normalRange = normalizeRange(origRange, tDateProfile, dateEnv);
            let segs = [];
            // protect against when the span is entirely in an invalid date region
            if (computeDateSnapCoverage(normalRange.start, tDateProfile, dateEnv)
                < computeDateSnapCoverage(normalRange.end, tDateProfile, dateEnv)) {
                // intersect the footprint's range with the grid's range
                let slicedRange = internal$1.intersectRanges(normalRange, tDateProfile.normalizedRange);
                if (slicedRange) {
                    segs.push({
                        start: slicedRange.start,
                        end: slicedRange.end,
                        isStart: slicedRange.start.valueOf() === normalRange.start.valueOf()
                            && isValidDate(slicedRange.start, tDateProfile, dateProfile, dateProfileGenerator),
                        isEnd: slicedRange.end.valueOf() === normalRange.end.valueOf()
                            && isValidDate(internal$1.addMs(slicedRange.end, -1), tDateProfile, dateProfile, dateProfileGenerator),
                    });
                }
            }
            return segs;
        }
    }

    const DEFAULT_TIME_FORMAT = internal$1.createFormatter({
        hour: 'numeric',
        minute: '2-digit',
        omitZeroMinute: true,
        meridiem: 'narrow',
    });
    class TimelineEvent extends internal$1.BaseComponent {
        render() {
            let { props } = this;
            return (preact.createElement(internal$1.StandardEvent, Object.assign({}, props, { elClasses: ['fc-timeline-event', 'fc-h-event'], defaultTimeFormat: DEFAULT_TIME_FORMAT, defaultDisplayEventTime: !props.isTimeScale })));
        }
    }

    class TimelineLaneMoreLink extends internal$1.BaseComponent {
        render() {
            let { props, context } = this;
            let { hiddenSegs, placement, resourceId } = props;
            let { top, hcoords } = placement;
            let isVisible = hcoords && top !== null;
            let hStyle = coordsToCss(hcoords, context.isRtl);
            let extraDateSpan = resourceId ? { resourceId } : {};
            return (preact.createElement(internal$1.MoreLinkContainer, { elRef: props.elRef, elClasses: ['fc-timeline-more-link'], elStyle: Object.assign({ visibility: isVisible ? '' : 'hidden', top: top || 0 }, hStyle), allDayDate: null, moreCnt: hiddenSegs.length, allSegs: hiddenSegs, hiddenSegs: hiddenSegs, dateProfile: props.dateProfile, todayRange: props.todayRange, extraDateSpan: extraDateSpan, popoverContent: () => (preact.createElement(preact.Fragment, null, hiddenSegs.map((seg) => {
                    let instanceId = seg.eventRange.instance.instanceId;
                    return (preact.createElement("div", { key: instanceId, style: { visibility: props.isForcedInvisible[instanceId] ? 'hidden' : '' } },
                        preact.createElement(TimelineEvent, Object.assign({ isTimeScale: props.isTimeScale, seg: seg, isDragging: false, isResizing: false, isDateSelecting: false, isSelected: instanceId === props.eventSelection }, internal$1.getSegMeta(seg, props.todayRange, props.nowDate)))));
                }))) }, (InnerContent) => (preact.createElement(InnerContent, { elTag: "div", elClasses: ['fc-timeline-more-link-inner', 'fc-sticky'] }))));
        }
    }

    class TimelineLane extends internal$1.BaseComponent {
        constructor() {
            super(...arguments);
            this.slicer = new TimelineLaneSlicer();
            this.sortEventSegs = internal$1.memoize(internal$1.sortEventSegs);
            this.harnessElRefs = new internal$1.RefMap();
            this.moreElRefs = new internal$1.RefMap();
            this.innerElRef = preact.createRef();
            // TODO: memoize event positioning
            this.state = {
                eventInstanceHeights: {},
                moreLinkHeights: {},
            };
            this.handleResize = (isForced) => {
                if (isForced) {
                    this.updateSize();
                }
            };
        }
        render() {
            let { props, state, context } = this;
            let { options } = context;
            let { dateProfile, tDateProfile } = props;
            let slicedProps = this.slicer.sliceProps(props, dateProfile, tDateProfile.isTimeScale ? null : props.nextDayThreshold, context, // wish we didn't have to pass in the rest of the args...
            dateProfile, context.dateProfileGenerator, tDateProfile, context.dateEnv);
            let mirrorSegs = (slicedProps.eventDrag ? slicedProps.eventDrag.segs : null) ||
                (slicedProps.eventResize ? slicedProps.eventResize.segs : null) ||
                [];
            let fgSegs = this.sortEventSegs(slicedProps.fgEventSegs, options.eventOrder);
            let fgSegHCoords = computeSegHCoords(fgSegs, options.eventMinWidth, props.timelineCoords);
            let [fgPlacements, fgHeight] = computeFgSegPlacements(fgSegs, fgSegHCoords, state.eventInstanceHeights, state.moreLinkHeights, options.eventOrderStrict, options.eventMaxStack);
            let isForcedInvisible = // TODO: more convenient
             (slicedProps.eventDrag ? slicedProps.eventDrag.affectedInstances : null) ||
                (slicedProps.eventResize ? slicedProps.eventResize.affectedInstances : null) ||
                {};
            return (preact.createElement(preact.Fragment, null,
                preact.createElement(TimelineLaneBg, { businessHourSegs: slicedProps.businessHourSegs, bgEventSegs: slicedProps.bgEventSegs, timelineCoords: props.timelineCoords, eventResizeSegs: slicedProps.eventResize ? slicedProps.eventResize.segs : [] /* bad new empty array? */, dateSelectionSegs: slicedProps.dateSelectionSegs, nowDate: props.nowDate, todayRange: props.todayRange }),
                preact.createElement("div", { className: "fc-timeline-events fc-scrollgrid-sync-inner", ref: this.innerElRef, style: { height: fgHeight } },
                    this.renderFgSegs(fgPlacements, isForcedInvisible, false, false, false),
                    this.renderFgSegs(buildMirrorPlacements(mirrorSegs, props.timelineCoords, fgPlacements), {}, Boolean(slicedProps.eventDrag), Boolean(slicedProps.eventResize), false))));
        }
        componentDidMount() {
            this.updateSize();
            this.context.addResizeHandler(this.handleResize);
        }
        componentDidUpdate(prevProps, prevState) {
            if (prevProps.eventStore !== this.props.eventStore || // external thing changed?
                prevProps.timelineCoords !== this.props.timelineCoords || // external thing changed?
                prevState.moreLinkHeights !== this.state.moreLinkHeights // HACK. see addStateEquality
            ) {
                this.updateSize();
            }
        }
        componentWillUnmount() {
            this.context.removeResizeHandler(this.handleResize);
        }
        updateSize() {
            let { props } = this;
            let { timelineCoords } = props;
            const innerEl = this.innerElRef.current;
            if (props.onHeightChange) {
                props.onHeightChange(innerEl, false);
            }
            if (timelineCoords) {
                this.setState({
                    eventInstanceHeights: internal$1.mapHash(this.harnessElRefs.currentMap, (harnessEl) => (Math.round(harnessEl.getBoundingClientRect().height))),
                    moreLinkHeights: internal$1.mapHash(this.moreElRefs.currentMap, (moreEl) => (Math.round(moreEl.getBoundingClientRect().height))),
                }, () => {
                    if (props.onHeightChange) {
                        props.onHeightChange(innerEl, true);
                    }
                });
            }
            // hack
            if (props.syncParentMinHeight) {
                innerEl.parentElement.style.minHeight = innerEl.style.height;
            }
        }
        renderFgSegs(segPlacements, isForcedInvisible, isDragging, isResizing, isDateSelecting) {
            let { harnessElRefs, moreElRefs, props, context } = this;
            let isMirror = isDragging || isResizing || isDateSelecting;
            return (preact.createElement(preact.Fragment, null, segPlacements.map((segPlacement) => {
                let { seg, hcoords, top } = segPlacement;
                if (Array.isArray(seg)) { // a more-link
                    let isoStr = internal$1.buildIsoString(internal$1.computeEarliestSegStart(seg));
                    return (preact.createElement(TimelineLaneMoreLink, { key: 'm:' + isoStr /* "m" for "more" */, elRef: moreElRefs.createRef(isoStr), hiddenSegs: seg, placement: segPlacement, dateProfile: props.dateProfile, nowDate: props.nowDate, todayRange: props.todayRange, isTimeScale: props.tDateProfile.isTimeScale, eventSelection: props.eventSelection, resourceId: props.resourceId, isForcedInvisible: isForcedInvisible }));
                }
                let instanceId = seg.eventRange.instance.instanceId;
                let isVisible = isMirror || Boolean(!isForcedInvisible[instanceId] && hcoords && top !== null);
                let hStyle = coordsToCss(hcoords, context.isRtl);
                return (preact.createElement("div", { key: 'e:' + instanceId /* "e" for "event" */, ref: isMirror ? null : harnessElRefs.createRef(instanceId), className: "fc-timeline-event-harness", style: Object.assign({ visibility: isVisible ? '' : 'hidden', top: top || 0 }, hStyle) },
                    preact.createElement(TimelineEvent, Object.assign({ isTimeScale: props.tDateProfile.isTimeScale, seg: seg, isDragging: isDragging, isResizing: isResizing, isDateSelecting: isDateSelecting, isSelected: instanceId === props.eventSelection /* TODO: bad for mirror? */ }, internal$1.getSegMeta(seg, props.todayRange, props.nowDate)))));
            })));
        }
    }
    TimelineLane.addStateEquality({
        eventInstanceHeights: internal$1.isPropsEqual,
        moreLinkHeights: internal$1.isPropsEqual,
    });
    function buildMirrorPlacements(mirrorSegs, timelineCoords, fgPlacements) {
        if (!mirrorSegs.length || !timelineCoords) {
            return [];
        }
        let topsByInstanceId = buildAbsoluteTopHash(fgPlacements); // TODO: cache this at first render?
        return mirrorSegs.map((seg) => ({
            seg,
            hcoords: timelineCoords.rangeToCoords(seg),
            top: topsByInstanceId[seg.eventRange.instance.instanceId],
        }));
    }
    function buildAbsoluteTopHash(placements) {
        let topsByInstanceId = {};
        for (let placement of placements) {
            let { seg } = placement;
            if (!Array.isArray(seg)) { // doesn't represent a more-link
                topsByInstanceId[seg.eventRange.instance.instanceId] = placement.top;
            }
        }
        return topsByInstanceId;
    }

    class TimelineGrid extends internal$1.DateComponent {
        constructor() {
            super(...arguments);
            this.slatsRef = preact.createRef();
            this.state = {
                coords: null,
            };
            this.handeEl = (el) => {
                if (el) {
                    this.context.registerInteractiveComponent(this, { el });
                }
                else {
                    this.context.unregisterInteractiveComponent(this);
                }
            };
            this.handleCoords = (coords) => {
                this.setState({ coords });
                if (this.props.onSlatCoords) {
                    this.props.onSlatCoords(coords);
                }
            };
        }
        render() {
            let { props, state, context } = this;
            let { options } = context;
            let { dateProfile, tDateProfile } = props;
            let timerUnit = internal$1.greatestDurationDenominator(tDateProfile.slotDuration).unit;
            return (preact.createElement("div", { className: "fc-timeline-body", ref: this.handeEl, style: {
                    minWidth: props.tableMinWidth,
                    height: props.clientHeight,
                    width: props.clientWidth,
                } },
                preact.createElement(internal$1.NowTimer, { unit: timerUnit }, (nowDate, todayRange) => (preact.createElement(preact.Fragment, null,
                    preact.createElement(TimelineSlats, { ref: this.slatsRef, dateProfile: dateProfile, tDateProfile: tDateProfile, nowDate: nowDate, todayRange: todayRange, clientWidth: props.clientWidth, tableColGroupNode: props.tableColGroupNode, tableMinWidth: props.tableMinWidth, onCoords: this.handleCoords, onScrollLeftRequest: props.onScrollLeftRequest }),
                    preact.createElement(TimelineLane, { dateProfile: dateProfile, tDateProfile: props.tDateProfile, nowDate: nowDate, todayRange: todayRange, nextDayThreshold: options.nextDayThreshold, businessHours: props.businessHours, eventStore: props.eventStore, eventUiBases: props.eventUiBases, dateSelection: props.dateSelection, eventSelection: props.eventSelection, eventDrag: props.eventDrag, eventResize: props.eventResize, timelineCoords: state.coords, syncParentMinHeight: true }),
                    (options.nowIndicator && state.coords && state.coords.isDateInRange(nowDate)) && (preact.createElement("div", { className: "fc-timeline-now-indicator-container" },
                        preact.createElement(internal$1.NowIndicatorContainer, { elClasses: ['fc-timeline-now-indicator-line'], elStyle: coordToCss(state.coords.dateToCoord(nowDate), context.isRtl), isAxis: false, date: nowDate }))))))));
        }
        // Hit System
        // ------------------------------------------------------------------------------------------
        queryHit(positionLeft, positionTop, elWidth, elHeight) {
            let slats = this.slatsRef.current;
            let slatHit = slats.positionToHit(positionLeft);
            if (slatHit) {
                return {
                    dateProfile: this.props.dateProfile,
                    dateSpan: slatHit.dateSpan,
                    rect: {
                        left: slatHit.left,
                        right: slatHit.right,
                        top: 0,
                        bottom: elHeight,
                    },
                    dayEl: slatHit.dayEl,
                    layer: 0,
                };
            }
            return null;
        }
    }

    class TimelineView extends internal$1.DateComponent {
        constructor() {
            super(...arguments);
            this.buildTimelineDateProfile = internal$1.memoize(buildTimelineDateProfile);
            this.scrollGridRef = preact.createRef();
            this.state = {
                slatCoords: null,
                slotCushionMaxWidth: null,
            };
            this.handleSlatCoords = (slatCoords) => {
                this.setState({ slatCoords });
            };
            this.handleScrollLeftRequest = (scrollLeft) => {
                let scrollGrid = this.scrollGridRef.current;
                scrollGrid.forceScrollLeft(0, scrollLeft);
            };
            this.handleMaxCushionWidth = (slotCushionMaxWidth) => {
                this.setState({
                    slotCushionMaxWidth: Math.ceil(slotCushionMaxWidth), // for less rerendering TODO: DRY
                });
            };
        }
        render() {
            let { props, state, context } = this;
            let { options } = context;
            let stickyHeaderDates = !props.forPrint && internal$1.getStickyHeaderDates(options);
            let stickyFooterScrollbar = !props.forPrint && internal$1.getStickyFooterScrollbar(options);
            let tDateProfile = this.buildTimelineDateProfile(props.dateProfile, context.dateEnv, options, context.dateProfileGenerator);
            let { slotMinWidth } = options;
            let slatCols = buildSlatCols(tDateProfile, slotMinWidth || this.computeFallbackSlotMinWidth(tDateProfile));
            let sections = [
                {
                    type: 'header',
                    key: 'header',
                    isSticky: stickyHeaderDates,
                    chunks: [{
                            key: 'timeline',
                            content: (contentArg) => (preact.createElement(TimelineHeader, { dateProfile: props.dateProfile, clientWidth: contentArg.clientWidth, clientHeight: contentArg.clientHeight, tableMinWidth: contentArg.tableMinWidth, tableColGroupNode: contentArg.tableColGroupNode, tDateProfile: tDateProfile, slatCoords: state.slatCoords, onMaxCushionWidth: slotMinWidth ? null : this.handleMaxCushionWidth })),
                        }],
                },
                {
                    type: 'body',
                    key: 'body',
                    liquid: true,
                    chunks: [{
                            key: 'timeline',
                            content: (contentArg) => (preact.createElement(TimelineGrid, Object.assign({}, props, { clientWidth: contentArg.clientWidth, clientHeight: contentArg.clientHeight, tableMinWidth: contentArg.tableMinWidth, tableColGroupNode: contentArg.tableColGroupNode, tDateProfile: tDateProfile, onSlatCoords: this.handleSlatCoords, onScrollLeftRequest: this.handleScrollLeftRequest }))),
                        }],
                },
            ];
            if (stickyFooterScrollbar) {
                sections.push({
                    type: 'footer',
                    key: 'footer',
                    isSticky: true,
                    chunks: [{
                            key: 'timeline',
                            content: internal$1.renderScrollShim,
                        }],
                });
            }
            return (preact.createElement(internal$1.ViewContainer, { elClasses: [
                    'fc-timeline',
                    options.eventOverlap === false ?
                        'fc-timeline-overlap-disabled' :
                        '',
                ], viewSpec: context.viewSpec },
                preact.createElement(internal$2.ScrollGrid, { ref: this.scrollGridRef, liquid: !props.isHeightAuto && !props.forPrint, forPrint: props.forPrint, collapsibleWidth: false, colGroups: [
                        { cols: slatCols },
                    ], sections: sections })));
        }
        computeFallbackSlotMinWidth(tDateProfile) {
            return Math.max(30, ((this.state.slotCushionMaxWidth || 0) / tDateProfile.slotsPerLabel));
        }
    }
    function buildSlatCols(tDateProfile, slotMinWidth) {
        return [{
                span: tDateProfile.slotCnt,
                minWidth: slotMinWidth || 1, // needs to be a non-zero number to trigger horizontal scrollbars!??????
            }];
    }

    var css_248z = ".fc .fc-timeline-body{min-height:100%;position:relative;z-index:1}.fc .fc-timeline-slots{bottom:0;position:absolute;top:0;z-index:1}.fc .fc-timeline-slots>table{height:100%}.fc .fc-timeline-slot-minor{border-style:dotted}.fc .fc-timeline-slot-frame{align-items:center;display:flex;justify-content:center}.fc .fc-timeline-header-row-chrono .fc-timeline-slot-frame{justify-content:flex-start}.fc .fc-timeline-header-row:last-child .fc-timeline-slot-frame{overflow:hidden}.fc .fc-timeline-slot-cushion{padding:4px 5px;white-space:nowrap}.fc-direction-ltr .fc-timeline-slot{border-right:0!important}.fc-direction-rtl .fc-timeline-slot{border-left:0!important}.fc .fc-timeline-now-indicator-container{bottom:0;left:0;position:absolute;right:0;top:0;width:0;z-index:4}.fc .fc-timeline-now-indicator-arrow,.fc .fc-timeline-now-indicator-line{border-color:var(--fc-now-indicator-color);border-style:solid;pointer-events:none;position:absolute;top:0}.fc .fc-timeline-now-indicator-arrow{border-left-color:transparent;border-right-color:transparent;border-width:6px 5px 0;margin:0 -6px}.fc .fc-timeline-now-indicator-line{border-width:0 0 0 1px;bottom:0;margin:0 -1px}.fc .fc-timeline-events{position:relative;width:0;z-index:3}.fc .fc-timeline-event-harness,.fc .fc-timeline-more-link{position:absolute;top:0}.fc-timeline-event{z-index:1}.fc-timeline-event.fc-event-mirror{z-index:2}.fc-timeline-event{align-items:center;border-radius:0;display:flex;font-size:var(--fc-small-font-size);margin-bottom:1px;padding:2px 1px;position:relative}.fc-timeline-event .fc-event-main{flex-grow:1;flex-shrink:1;min-width:0}.fc-timeline-event .fc-event-time{font-weight:700}.fc-timeline-event .fc-event-time,.fc-timeline-event .fc-event-title{padding:0 2px;white-space:nowrap}.fc-direction-ltr .fc-timeline-event.fc-event-end,.fc-direction-ltr .fc-timeline-more-link{margin-right:1px}.fc-direction-rtl .fc-timeline-event.fc-event-end,.fc-direction-rtl .fc-timeline-more-link{margin-left:1px}.fc-timeline-overlap-disabled .fc-timeline-event{margin-bottom:0;padding-bottom:5px;padding-top:5px}.fc-timeline-event:not(.fc-event-end):after,.fc-timeline-event:not(.fc-event-start):before{border-color:transparent #000;border-style:solid;border-width:5px;content:\"\";flex-grow:0;flex-shrink:0;height:0;margin:0 1px;opacity:.5;width:0}.fc-direction-ltr .fc-timeline-event:not(.fc-event-start):before,.fc-direction-rtl .fc-timeline-event:not(.fc-event-end):after{border-left:0}.fc-direction-ltr .fc-timeline-event:not(.fc-event-end):after,.fc-direction-rtl .fc-timeline-event:not(.fc-event-start):before{border-right:0}.fc-timeline-more-link{background:var(--fc-more-link-bg-color);color:var(--fc-more-link-text-color);cursor:pointer;font-size:var(--fc-small-font-size);padding:1px}.fc-timeline-more-link-inner{display:inline-block;left:0;padding:2px;right:0}.fc .fc-timeline-bg{bottom:0;left:0;position:absolute;right:0;top:0;width:0;z-index:2}.fc .fc-timeline-bg .fc-non-business{z-index:1}.fc .fc-timeline-bg .fc-bg-event{z-index:2}.fc .fc-timeline-bg .fc-highlight{z-index:3}.fc .fc-timeline-bg-harness{bottom:0;position:absolute;top:0}";
    internal$1.injectStyles(css_248z);

    var plugin = core.createPlugin({
        name: '@fullcalendar/timeline',
        premiumReleaseDate: '2024-07-12',
        deps: [premiumCommonPlugin__default["default"]],
        initialView: 'timelineDay',
        views: {
            timeline: {
                component: TimelineView,
                usesMinMaxTime: true,
                eventResizableFromStart: true, // how is this consumed for TimelineView tho?
            },
            timelineDay: {
                type: 'timeline',
                duration: { days: 1 },
            },
            timelineWeek: {
                type: 'timeline',
                duration: { weeks: 1 },
            },
            timelineMonth: {
                type: 'timeline',
                duration: { months: 1 },
            },
            timelineYear: {
                type: 'timeline',
                duration: { years: 1 },
            },
        },
    });

    var internal = {
        __proto__: null,
        TimelineView: TimelineView,
        buildSlatCols: buildSlatCols,
        TimelineLane: TimelineLane,
        TimelineLaneBg: TimelineLaneBg,
        TimelineHeader: TimelineHeader,
        TimelineSlats: TimelineSlats,
        buildTimelineDateProfile: buildTimelineDateProfile,
        TimelineCoords: TimelineCoords,
        coordToCss: coordToCss,
        coordsToCss: coordsToCss,
        TimelineLaneSlicer: TimelineLaneSlicer,
        TimelineHeaderRows: TimelineHeaderRows
    };

    core.globalPlugins.push(plugin);

    exports.Internal = internal;
    exports["default"] = plugin;

    Object.defineProperty(exports, '__esModule', { value: true });

    return exports;

})({}, FullCalendar, FullCalendar.PremiumCommon, FullCalendar.Internal, FullCalendar.Preact, FullCalendar.ScrollGrid.Internal);
