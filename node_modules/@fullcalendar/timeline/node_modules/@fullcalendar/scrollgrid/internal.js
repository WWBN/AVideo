import { computeEdges, removeElement, findElements, translateRect, computeInnerRect, applyStyle, BaseComponent, setRef, getIsRtlScrollbarOnLeft, Scroller, isPropsEqual, Emitter, DelayedRunner, config, memoizeArraylike, renderMicroColGroup, RefMap, getScrollGridClassNames, getCanVGrowWithinCell, getSectionClassNames, getAllowYScrolling, getSectionHasLiquidHeight, renderChunkContent, memoizeHashlike, computeShrinkWidth, getScrollbarWidths, collectFromHash, mapHash, isArraysEqual, sanitizeShrinkWidth, hasShrinkWidth, compareObjs, isColPropsEqual } from '@fullcalendar/core/internal.js';
import { createRef, createElement, Fragment } from '@fullcalendar/core/preact.js';

// TODO: assume the el has no borders?
function getScrollCanvasOrigin(scrollEl) {
    let rect = scrollEl.getBoundingClientRect();
    let edges = computeEdges(scrollEl); // TODO: pass in isRtl?
    return {
        left: rect.left + edges.borderLeft + edges.scrollbarLeft - getScrollFromLeftEdge(scrollEl),
        top: rect.top + edges.borderTop - scrollEl.scrollTop,
    };
}
function getScrollFromLeftEdge(el) {
    let scrollLeft = el.scrollLeft;
    let computedStyles = window.getComputedStyle(el); // TODO: pass in isRtl instead?
    if (computedStyles.direction === 'rtl') {
        switch (getRtlScrollSystem()) {
            case 'negative':
                scrollLeft *= -1; // convert to 'reverse'. fall through...
            case 'reverse': // scrollLeft is distance between scrollframe's right edge scrollcanvas's right edge
                scrollLeft = el.scrollWidth - scrollLeft - el.clientWidth;
        }
    }
    return scrollLeft;
}
function setScrollFromLeftEdge(el, scrollLeft) {
    let computedStyles = window.getComputedStyle(el); // TODO: pass in isRtl instead?
    if (computedStyles.direction === 'rtl') {
        switch (getRtlScrollSystem()) {
            case 'reverse':
                scrollLeft = el.scrollWidth - scrollLeft;
                break;
            case 'negative':
                scrollLeft = -(el.scrollWidth - scrollLeft);
                break;
        }
    }
    el.scrollLeft = scrollLeft;
}
// Horizontal Scroll System Detection
// ----------------------------------------------------------------------------------------------
let _rtlScrollSystem;
function getRtlScrollSystem() {
    return _rtlScrollSystem || (_rtlScrollSystem = detectRtlScrollSystem());
}
function detectRtlScrollSystem() {
    let el = document.createElement('div');
    el.style.position = 'absolute';
    el.style.top = '-1000px';
    el.style.width = '100px'; // must be at least the side of scrollbars or you get inaccurate values (#7335)
    el.style.height = '100px'; // "
    el.style.overflow = 'scroll';
    el.style.direction = 'rtl';
    let innerEl = document.createElement('div');
    innerEl.style.width = '200px';
    innerEl.style.height = '200px';
    el.appendChild(innerEl);
    document.body.appendChild(el);
    let system;
    if (el.scrollLeft > 0) {
        system = 'positive'; // scroll is a positive number from the left edge
    }
    else {
        el.scrollLeft = 1;
        if (el.scrollLeft > 0) {
            system = 'reverse'; // scroll is a positive number from the right edge
        }
        else {
            system = 'negative'; // scroll is a negative number from the right edge
        }
    }
    removeElement(el);
    return system;
}

const STICKY_SELECTOR = '.fc-sticky';
/*
Goes beyond mere position:sticky, allows horizontal centering

REQUIREMENT: fc-sticky elements, if the fc-sticky className is taken away, should NOT have relative or absolute positioning.
This is because we attach the coords with JS, and the VDOM might take away the fc-sticky class but doesn't know kill the positioning.

TODO: don't query text-align:center. isn't compatible with flexbox centering. instead, check natural X coord within parent container
*/
class StickyScrolling {
    constructor(scrollEl, isRtl) {
        this.scrollEl = scrollEl;
        this.isRtl = isRtl;
        this.updateSize = () => {
            let { scrollEl } = this;
            let els = findElements(scrollEl, STICKY_SELECTOR);
            let elGeoms = this.queryElGeoms(els);
            let viewportWidth = scrollEl.clientWidth;
            assignStickyPositions(els, elGeoms, viewportWidth);
        };
    }
    queryElGeoms(els) {
        let { scrollEl, isRtl } = this;
        let canvasOrigin = getScrollCanvasOrigin(scrollEl);
        let elGeoms = [];
        for (let el of els) {
            let parentBound = translateRect(computeInnerRect(el.parentNode, true, true), // weird way to call this!!!
            -canvasOrigin.left, -canvasOrigin.top);
            let elRect = el.getBoundingClientRect();
            let computedStyles = window.getComputedStyle(el);
            let textAlign = window.getComputedStyle(el.parentNode).textAlign; // ask the parent
            let naturalBound = null;
            if (textAlign === 'start') {
                textAlign = isRtl ? 'right' : 'left';
            }
            else if (textAlign === 'end') {
                textAlign = isRtl ? 'left' : 'right';
            }
            if (computedStyles.position !== 'sticky') {
                naturalBound = translateRect(elRect, -canvasOrigin.left - (parseFloat(computedStyles.left) || 0), // could be 'auto'
                -canvasOrigin.top - (parseFloat(computedStyles.top) || 0));
            }
            elGeoms.push({
                parentBound,
                naturalBound,
                elWidth: elRect.width,
                elHeight: elRect.height,
                textAlign,
            });
        }
        return elGeoms;
    }
}
function assignStickyPositions(els, elGeoms, viewportWidth) {
    els.forEach((el, i) => {
        let { textAlign, elWidth, parentBound } = elGeoms[i];
        let parentWidth = parentBound.right - parentBound.left;
        let left;
        if (textAlign === 'center' &&
            parentWidth > viewportWidth) {
            left = (viewportWidth - elWidth) / 2;
        }
        else { // if parent container can be completely in view, we don't need stickiness
            left = '';
        }
        applyStyle(el, {
            left,
            right: left,
            top: 0,
        });
    });
}

class ClippedScroller extends BaseComponent {
    constructor() {
        super(...arguments);
        this.elRef = createRef();
        this.state = {
            xScrollbarWidth: 0,
            yScrollbarWidth: 0,
        };
        this.handleScroller = (scroller) => {
            this.scroller = scroller;
            setRef(this.props.scrollerRef, scroller);
        };
        this.handleSizing = () => {
            let { props } = this;
            if (props.overflowY === 'scroll-hidden') {
                this.setState({ yScrollbarWidth: this.scroller.getYScrollbarWidth() });
            }
            if (props.overflowX === 'scroll-hidden') {
                this.setState({ xScrollbarWidth: this.scroller.getXScrollbarWidth() });
            }
        };
    }
    render() {
        let { props, state, context } = this;
        let isScrollbarOnLeft = context.isRtl && getIsRtlScrollbarOnLeft();
        let overcomeLeft = 0;
        let overcomeRight = 0;
        let overcomeBottom = 0;
        let { overflowX, overflowY } = props;
        if (props.forPrint) {
            overflowX = 'visible';
            overflowY = 'visible';
        }
        if (overflowX === 'scroll-hidden') {
            overcomeBottom = state.xScrollbarWidth;
        }
        if (overflowY === 'scroll-hidden') {
            if (state.yScrollbarWidth != null) {
                if (isScrollbarOnLeft) {
                    overcomeLeft = state.yScrollbarWidth;
                }
                else {
                    overcomeRight = state.yScrollbarWidth;
                }
            }
        }
        return (createElement("div", { ref: this.elRef, className: 'fc-scroller-harness' + (props.liquid ? ' fc-scroller-harness-liquid' : '') },
            createElement(Scroller, { ref: this.handleScroller, elRef: this.props.scrollerElRef, overflowX: overflowX === 'scroll-hidden' ? 'scroll' : overflowX, overflowY: overflowY === 'scroll-hidden' ? 'scroll' : overflowY, overcomeLeft: overcomeLeft, overcomeRight: overcomeRight, overcomeBottom: overcomeBottom, maxHeight: typeof props.maxHeight === 'number'
                    ? (props.maxHeight + (overflowX === 'scroll-hidden' ? state.xScrollbarWidth : 0))
                    : '', liquid: props.liquid, liquidIsAbsolute: true }, props.children)));
    }
    componentDidMount() {
        this.handleSizing();
        this.context.addResizeHandler(this.handleSizing);
    }
    getSnapshotBeforeUpdate(prevProps) {
        if (this.props.forPrint && !prevProps.forPrint) {
            return { simulateScrollLeft: this.scroller.el.scrollLeft };
        }
        return {};
    }
    componentDidUpdate(prevProps, prevState, snapshot) {
        const { props, scroller: { el: scrollerEl } } = this;
        if (!isPropsEqual(prevProps, props)) { // an external change?
            this.handleSizing();
        }
        if (snapshot.simulateScrollLeft !== undefined) {
            scrollerEl.style.left = -snapshot.simulateScrollLeft + 'px';
        }
        else if (!props.forPrint && prevProps.forPrint) {
            const restoredScrollLeft = -parseInt(scrollerEl.style.left);
            scrollerEl.style.left = '';
            scrollerEl.scrollLeft = restoredScrollLeft;
        }
    }
    componentWillUnmount() {
        this.context.removeResizeHandler(this.handleSizing);
    }
    needsXScrolling() {
        return this.scroller.needsXScrolling();
    }
    needsYScrolling() {
        return this.scroller.needsYScrolling();
    }
}

const WHEEL_EVENT_NAMES = 'wheel mousewheel DomMouseScroll MozMousePixelScroll'.split(' ');
/*
ALSO, with the ability to disable touch
*/
class ScrollListener {
    constructor(el) {
        this.el = el;
        this.emitter = new Emitter();
        this.isScrolling = false;
        this.isTouching = false; // user currently has finger down?
        this.isRecentlyWheeled = false;
        this.isRecentlyScrolled = false;
        this.wheelWaiter = new DelayedRunner(this._handleWheelWaited.bind(this));
        this.scrollWaiter = new DelayedRunner(this._handleScrollWaited.bind(this));
        // Handlers
        // ----------------------------------------------------------------------------------------------
        this.handleScroll = () => {
            this.startScroll();
            this.emitter.trigger('scroll', this.isRecentlyWheeled, this.isTouching);
            this.isRecentlyScrolled = true;
            this.scrollWaiter.request(500);
        };
        // will fire *before* the scroll event is fired (might not cause a scroll)
        this.handleWheel = () => {
            this.isRecentlyWheeled = true;
            this.wheelWaiter.request(500);
        };
        // will fire *before* the scroll event is fired (might not cause a scroll)
        this.handleTouchStart = () => {
            this.isTouching = true;
        };
        this.handleTouchEnd = () => {
            this.isTouching = false;
            // if the user ended their touch, and the scroll area wasn't moving,
            // we consider this to be the end of the scroll.
            if (!this.isRecentlyScrolled) {
                this.endScroll(); // won't fire if already ended
            }
        };
        el.addEventListener('scroll', this.handleScroll);
        el.addEventListener('touchstart', this.handleTouchStart, { passive: true });
        el.addEventListener('touchend', this.handleTouchEnd);
        for (let eventName of WHEEL_EVENT_NAMES) {
            el.addEventListener(eventName, this.handleWheel);
        }
    }
    destroy() {
        let { el } = this;
        el.removeEventListener('scroll', this.handleScroll);
        el.removeEventListener('touchstart', this.handleTouchStart, { passive: true });
        el.removeEventListener('touchend', this.handleTouchEnd);
        for (let eventName of WHEEL_EVENT_NAMES) {
            el.removeEventListener(eventName, this.handleWheel);
        }
    }
    // Start / Stop
    // ----------------------------------------------------------------------------------------------
    startScroll() {
        if (!this.isScrolling) {
            this.isScrolling = true;
            this.emitter.trigger('scrollStart', this.isRecentlyWheeled, this.isTouching);
        }
    }
    endScroll() {
        if (this.isScrolling) {
            this.emitter.trigger('scrollEnd');
            this.isScrolling = false;
            this.isRecentlyScrolled = true;
            this.isRecentlyWheeled = false;
            this.scrollWaiter.clear();
            this.wheelWaiter.clear();
        }
    }
    _handleScrollWaited() {
        this.isRecentlyScrolled = false;
        // only end the scroll if not currently touching.
        // if touching, the scrolling will end later, on touchend.
        if (!this.isTouching) {
            this.endScroll(); // won't fire if already ended
        }
    }
    _handleWheelWaited() {
        this.isRecentlyWheeled = false;
    }
}

class ScrollSyncer {
    constructor(isVertical, scrollEls) {
        this.isVertical = isVertical;
        this.scrollEls = scrollEls;
        this.isPaused = false;
        this.scrollListeners = scrollEls.map((el) => this.bindScroller(el));
    }
    destroy() {
        for (let scrollListener of this.scrollListeners) {
            scrollListener.destroy();
        }
    }
    bindScroller(el) {
        let { scrollEls, isVertical } = this;
        let scrollListener = new ScrollListener(el);
        const onScroll = (isWheel, isTouch) => {
            if (!this.isPaused) {
                if (!this.masterEl || (this.masterEl !== el && (isWheel || isTouch))) {
                    this.assignMaster(el);
                }
                if (this.masterEl === el) { // dealing with current
                    for (let otherEl of scrollEls) {
                        if (otherEl !== el) {
                            if (isVertical) {
                                otherEl.scrollTop = el.scrollTop;
                            }
                            else {
                                otherEl.scrollLeft = el.scrollLeft;
                            }
                        }
                    }
                }
            }
        };
        const onScrollEnd = () => {
            if (this.masterEl === el) {
                this.masterEl = null;
            }
        };
        scrollListener.emitter.on('scroll', onScroll);
        scrollListener.emitter.on('scrollEnd', onScrollEnd);
        return scrollListener;
    }
    assignMaster(el) {
        this.masterEl = el;
        for (let scrollListener of this.scrollListeners) {
            if (scrollListener.el !== el) {
                scrollListener.endScroll(); // to prevent residual scrolls from reclaiming master
            }
        }
    }
    /*
    will normalize the scrollLeft value
    */
    forceScrollLeft(scrollLeft) {
        this.isPaused = true;
        for (let listener of this.scrollListeners) {
            setScrollFromLeftEdge(listener.el, scrollLeft);
        }
        this.isPaused = false;
    }
    forceScrollTop(top) {
        this.isPaused = true;
        for (let listener of this.scrollListeners) {
            listener.el.scrollTop = top;
        }
        this.isPaused = false;
    }
}

config.SCROLLGRID_RESIZE_INTERVAL = 500;
/*
TODO: make <ScrollGridSection> subcomponent
NOTE: doesn't support collapsibleWidth (which is sortof a hack anyway)
*/
class ScrollGrid extends BaseComponent {
    constructor() {
        super(...arguments);
        this.compileColGroupStats = memoizeArraylike(compileColGroupStat, isColGroupStatsEqual);
        this.renderMicroColGroups = memoizeArraylike(renderMicroColGroup); // yucky to memoize VNodes, but much more efficient for consumers
        this.clippedScrollerRefs = new RefMap();
        // doesn't hold non-scrolling els used just for padding
        this.scrollerElRefs = new RefMap(this._handleScrollerEl.bind(this));
        this.chunkElRefs = new RefMap(this._handleChunkEl.bind(this));
        this.scrollSyncersBySection = {};
        this.scrollSyncersByColumn = {};
        // for row-height-syncing
        this.rowUnstableMap = new Map(); // no need to groom. always self-cancels
        this.rowInnerMaxHeightMap = new Map();
        this.anyRowHeightsChanged = false;
        this.recentSizingCnt = 0;
        this.state = {
            shrinkWidths: [],
            forceYScrollbars: false,
            forceXScrollbars: false,
            scrollerClientWidths: {},
            scrollerClientHeights: {},
            sectionRowMaxHeights: [],
        };
        this.handleSizing = (isForcedResize, sectionRowMaxHeightsChanged) => {
            if (!this.allowSizing()) {
                return;
            }
            if (!sectionRowMaxHeightsChanged) { // something else changed, probably external
                this.anyRowHeightsChanged = true;
            }
            let otherState = {};
            // if reacting to self-change of sectionRowMaxHeightsChanged, or not stable, don't do anything
            if (isForcedResize || (!sectionRowMaxHeightsChanged && !this.rowUnstableMap.size)) {
                otherState.sectionRowMaxHeights = this.computeSectionRowMaxHeights();
            }
            this.setState(Object.assign(Object.assign({ shrinkWidths: this.computeShrinkWidths() }, this.computeScrollerDims()), otherState), () => {
                if (!this.rowUnstableMap.size) {
                    this.updateStickyScrolling(); // needs to happen AFTER final positioning committed to DOM
                }
            });
        };
        this.handleRowHeightChange = (rowEl, isStable) => {
            let { rowUnstableMap, rowInnerMaxHeightMap } = this;
            if (!isStable) {
                rowUnstableMap.set(rowEl, true);
            }
            else {
                rowUnstableMap.delete(rowEl);
                let innerMaxHeight = getRowInnerMaxHeight(rowEl);
                if (!rowInnerMaxHeightMap.has(rowEl) || rowInnerMaxHeightMap.get(rowEl) !== innerMaxHeight) {
                    rowInnerMaxHeightMap.set(rowEl, innerMaxHeight);
                    this.anyRowHeightsChanged = true;
                }
                if (!rowUnstableMap.size && this.anyRowHeightsChanged) {
                    this.anyRowHeightsChanged = false;
                    this.setState({
                        sectionRowMaxHeights: this.computeSectionRowMaxHeights(),
                    });
                }
            }
        };
    }
    render() {
        let { props, state, context } = this;
        let { shrinkWidths } = state;
        let colGroupStats = this.compileColGroupStats(props.colGroups.map((colGroup) => [colGroup]));
        let microColGroupNodes = this.renderMicroColGroups(colGroupStats.map((stat, i) => [stat.cols, shrinkWidths[i]]));
        let classNames = getScrollGridClassNames(props.liquid, context);
        this.getDims();
        // TODO: make DRY
        let sectionConfigs = props.sections;
        let configCnt = sectionConfigs.length;
        let configI = 0;
        let currentConfig;
        let headSectionNodes = [];
        let bodySectionNodes = [];
        let footSectionNodes = [];
        while (configI < configCnt && (currentConfig = sectionConfigs[configI]).type === 'header') {
            headSectionNodes.push(this.renderSection(currentConfig, configI, colGroupStats, microColGroupNodes, state.sectionRowMaxHeights, true));
            configI += 1;
        }
        while (configI < configCnt && (currentConfig = sectionConfigs[configI]).type === 'body') {
            bodySectionNodes.push(this.renderSection(currentConfig, configI, colGroupStats, microColGroupNodes, state.sectionRowMaxHeights, false));
            configI += 1;
        }
        while (configI < configCnt && (currentConfig = sectionConfigs[configI]).type === 'footer') {
            footSectionNodes.push(this.renderSection(currentConfig, configI, colGroupStats, microColGroupNodes, state.sectionRowMaxHeights, true));
            configI += 1;
        }
        const isBuggy = !getCanVGrowWithinCell(); // see NOTE in SimpleScrollGrid
        const roleAttrs = { role: 'rowgroup' };
        return createElement('table', {
            ref: props.elRef,
            role: 'grid',
            className: classNames.join(' '),
        }, renderMacroColGroup(colGroupStats, shrinkWidths), Boolean(!isBuggy && headSectionNodes.length) && createElement('thead', roleAttrs, ...headSectionNodes), Boolean(!isBuggy && bodySectionNodes.length) && createElement('tbody', roleAttrs, ...bodySectionNodes), Boolean(!isBuggy && footSectionNodes.length) && createElement('tfoot', roleAttrs, ...footSectionNodes), isBuggy && createElement('tbody', roleAttrs, ...headSectionNodes, ...bodySectionNodes, ...footSectionNodes));
    }
    renderSection(sectionConfig, sectionIndex, colGroupStats, microColGroupNodes, sectionRowMaxHeights, isHeader) {
        if ('outerContent' in sectionConfig) {
            return (createElement(Fragment, { key: sectionConfig.key }, sectionConfig.outerContent));
        }
        return (createElement("tr", { key: sectionConfig.key, role: "presentation", className: getSectionClassNames(sectionConfig, this.props.liquid).join(' ') }, sectionConfig.chunks.map((chunkConfig, i) => this.renderChunk(sectionConfig, sectionIndex, colGroupStats[i], microColGroupNodes[i], chunkConfig, i, (sectionRowMaxHeights[sectionIndex] || [])[i] || [], isHeader))));
    }
    renderChunk(sectionConfig, sectionIndex, colGroupStat, microColGroupNode, chunkConfig, chunkIndex, rowHeights, isHeader) {
        if ('outerContent' in chunkConfig) {
            return (createElement(Fragment, { key: chunkConfig.key }, chunkConfig.outerContent));
        }
        let { state } = this;
        let { scrollerClientWidths, scrollerClientHeights } = state;
        let [sectionCnt, chunksPerSection] = this.getDims();
        let index = sectionIndex * chunksPerSection + chunkIndex;
        let sideScrollIndex = (!this.context.isRtl || getIsRtlScrollbarOnLeft()) ? chunksPerSection - 1 : 0;
        let isVScrollSide = chunkIndex === sideScrollIndex;
        let isLastSection = sectionIndex === sectionCnt - 1;
        let forceXScrollbars = isLastSection && state.forceXScrollbars; // NOOOO can result in `null`
        let forceYScrollbars = isVScrollSide && state.forceYScrollbars; // NOOOO can result in `null`
        let allowXScrolling = colGroupStat && colGroupStat.allowXScrolling; // rename?
        let allowYScrolling = getAllowYScrolling(this.props, sectionConfig); // rename? do in section func?
        let chunkVGrow = getSectionHasLiquidHeight(this.props, sectionConfig); // do in section func?
        let expandRows = sectionConfig.expandRows && chunkVGrow;
        let tableMinWidth = (colGroupStat && colGroupStat.totalColMinWidth) || '';
        let content = renderChunkContent(sectionConfig, chunkConfig, {
            tableColGroupNode: microColGroupNode,
            tableMinWidth,
            clientWidth: scrollerClientWidths[index] !== undefined ? scrollerClientWidths[index] : null,
            clientHeight: scrollerClientHeights[index] !== undefined ? scrollerClientHeights[index] : null,
            expandRows,
            syncRowHeights: Boolean(sectionConfig.syncRowHeights),
            rowSyncHeights: rowHeights,
            reportRowHeightChange: this.handleRowHeightChange,
        }, isHeader);
        let overflowX = forceXScrollbars ? (isLastSection ? 'scroll' : 'scroll-hidden') :
            !allowXScrolling ? 'hidden' :
                (isLastSection ? 'auto' : 'scroll-hidden');
        let overflowY = forceYScrollbars ? (isVScrollSide ? 'scroll' : 'scroll-hidden') :
            !allowYScrolling ? 'hidden' :
                (isVScrollSide ? 'auto' : 'scroll-hidden');
        // it *could* be possible to reduce DOM wrappers by only doing a ClippedScroller when allowXScrolling or allowYScrolling,
        // but if these values were to change, the inner components would be unmounted/remounted because of the parent change.
        content = (createElement(ClippedScroller, { ref: this.clippedScrollerRefs.createRef(index), scrollerElRef: this.scrollerElRefs.createRef(index), overflowX: overflowX, overflowY: overflowY, forPrint: this.props.forPrint, liquid: chunkVGrow, maxHeight: sectionConfig.maxHeight }, content));
        return createElement(isHeader ? 'th' : 'td', {
            key: chunkConfig.key,
            ref: this.chunkElRefs.createRef(index),
            role: 'presentation',
        }, content);
    }
    componentDidMount() {
        this.getStickyScrolling = memoizeArraylike(initStickyScrolling);
        this.getScrollSyncersBySection = memoizeHashlike(initScrollSyncer.bind(this, true), null, destroyScrollSyncer);
        this.getScrollSyncersByColumn = memoizeHashlike(initScrollSyncer.bind(this, false), null, destroyScrollSyncer);
        this.updateScrollSyncers();
        this.handleSizing(false);
        this.context.addResizeHandler(this.handleSizing);
    }
    componentDidUpdate(prevProps, prevState) {
        this.updateScrollSyncers();
        // TODO: need better solution when state contains non-sizing things
        this.handleSizing(false, prevState.sectionRowMaxHeights !== this.state.sectionRowMaxHeights);
    }
    componentWillUnmount() {
        this.context.removeResizeHandler(this.handleSizing);
        this.destroyScrollSyncers();
    }
    allowSizing() {
        let now = new Date();
        if (!this.lastSizingDate ||
            now.valueOf() > this.lastSizingDate.valueOf() + config.SCROLLGRID_RESIZE_INTERVAL) {
            this.lastSizingDate = now;
            this.recentSizingCnt = 0;
            return true;
        }
        return (this.recentSizingCnt += 1) <= 10;
    }
    computeShrinkWidths() {
        let colGroupStats = this.compileColGroupStats(this.props.colGroups.map((colGroup) => [colGroup]));
        let [sectionCnt, chunksPerSection] = this.getDims();
        let cnt = sectionCnt * chunksPerSection;
        let shrinkWidths = [];
        colGroupStats.forEach((colGroupStat, i) => {
            if (colGroupStat.hasShrinkCol) {
                let chunkEls = this.chunkElRefs.collect(i, cnt, chunksPerSection); // in one col
                shrinkWidths[i] = computeShrinkWidth(chunkEls);
            }
        });
        return shrinkWidths;
    }
    // has the side effect of grooming rowInnerMaxHeightMap
    // TODO: somehow short-circuit if there are no new height changes
    computeSectionRowMaxHeights() {
        let newHeightMap = new Map();
        let [sectionCnt, chunksPerSection] = this.getDims();
        let sectionRowMaxHeights = [];
        for (let sectionI = 0; sectionI < sectionCnt; sectionI += 1) {
            let sectionConfig = this.props.sections[sectionI];
            let assignableHeights = []; // chunk, row
            if (sectionConfig && sectionConfig.syncRowHeights) {
                let rowHeightsByChunk = [];
                for (let chunkI = 0; chunkI < chunksPerSection; chunkI += 1) {
                    let index = sectionI * chunksPerSection + chunkI;
                    let rowHeights = [];
                    let chunkEl = this.chunkElRefs.currentMap[index];
                    if (chunkEl) {
                        rowHeights = findElements(chunkEl, '.fc-scrollgrid-sync-table tr').map((rowEl) => {
                            let max = getRowInnerMaxHeight(rowEl);
                            newHeightMap.set(rowEl, max);
                            return max;
                        });
                    }
                    else {
                        rowHeights = [];
                    }
                    rowHeightsByChunk.push(rowHeights);
                }
                let rowCnt = rowHeightsByChunk[0].length;
                let isEqualRowCnt = true;
                for (let chunkI = 1; chunkI < chunksPerSection; chunkI += 1) {
                    let isOuterContent = sectionConfig.chunks[chunkI] && sectionConfig.chunks[chunkI].outerContent !== undefined; // can be null
                    if (!isOuterContent && rowHeightsByChunk[chunkI].length !== rowCnt) { // skip outer content
                        isEqualRowCnt = false;
                        break;
                    }
                }
                if (!isEqualRowCnt) {
                    let chunkHeightSums = [];
                    for (let chunkI = 0; chunkI < chunksPerSection; chunkI += 1) {
                        chunkHeightSums.push(sumNumbers(rowHeightsByChunk[chunkI]) + rowHeightsByChunk[chunkI].length);
                    }
                    let maxTotalSum = Math.max(...chunkHeightSums);
                    for (let chunkI = 0; chunkI < chunksPerSection; chunkI += 1) {
                        let rowInChunkCnt = rowHeightsByChunk[chunkI].length;
                        let rowInChunkTotalHeight = maxTotalSum - rowInChunkCnt; // subtract border
                        // height of non-first row. we do this to avoid rounding, because it's unreliable within a table
                        let rowInChunkHeightOthers = Math.floor(rowInChunkTotalHeight / rowInChunkCnt);
                        // whatever is leftover goes to the first row
                        let rowInChunkHeightFirst = rowInChunkTotalHeight - rowInChunkHeightOthers * (rowInChunkCnt - 1);
                        let rowInChunkHeights = [];
                        let row = 0;
                        if (row < rowInChunkCnt) {
                            rowInChunkHeights.push(rowInChunkHeightFirst);
                            row += 1;
                        }
                        while (row < rowInChunkCnt) {
                            rowInChunkHeights.push(rowInChunkHeightOthers);
                            row += 1;
                        }
                        assignableHeights.push(rowInChunkHeights);
                    }
                }
                else {
                    for (let chunkI = 0; chunkI < chunksPerSection; chunkI += 1) {
                        assignableHeights.push([]);
                    }
                    for (let row = 0; row < rowCnt; row += 1) {
                        let rowHeightsAcrossChunks = [];
                        for (let chunkI = 0; chunkI < chunksPerSection; chunkI += 1) {
                            let h = rowHeightsByChunk[chunkI][row];
                            if (h != null) { // protect against outerContent
                                rowHeightsAcrossChunks.push(h);
                            }
                        }
                        let maxHeight = Math.max(...rowHeightsAcrossChunks);
                        for (let chunkI = 0; chunkI < chunksPerSection; chunkI += 1) {
                            assignableHeights[chunkI].push(maxHeight);
                        }
                    }
                }
            }
            sectionRowMaxHeights.push(assignableHeights);
        }
        this.rowInnerMaxHeightMap = newHeightMap;
        return sectionRowMaxHeights;
    }
    computeScrollerDims() {
        let scrollbarWidth = getScrollbarWidths();
        let [sectionCnt, chunksPerSection] = this.getDims();
        let sideScrollI = (!this.context.isRtl || getIsRtlScrollbarOnLeft()) ? chunksPerSection - 1 : 0;
        let lastSectionI = sectionCnt - 1;
        let currentScrollers = this.clippedScrollerRefs.currentMap;
        let scrollerEls = this.scrollerElRefs.currentMap;
        let forceYScrollbars = false;
        let forceXScrollbars = false;
        let scrollerClientWidths = {};
        let scrollerClientHeights = {};
        for (let sectionI = 0; sectionI < sectionCnt; sectionI += 1) { // along edge
            let index = sectionI * chunksPerSection + sideScrollI;
            let scroller = currentScrollers[index];
            if (scroller && scroller.needsYScrolling()) {
                forceYScrollbars = true;
                break;
            }
        }
        for (let chunkI = 0; chunkI < chunksPerSection; chunkI += 1) { // along last row
            let index = lastSectionI * chunksPerSection + chunkI;
            let scroller = currentScrollers[index];
            if (scroller && scroller.needsXScrolling()) {
                forceXScrollbars = true;
                break;
            }
        }
        for (let sectionI = 0; sectionI < sectionCnt; sectionI += 1) {
            for (let chunkI = 0; chunkI < chunksPerSection; chunkI += 1) {
                let index = sectionI * chunksPerSection + chunkI;
                let scrollerEl = scrollerEls[index];
                if (scrollerEl) {
                    // TODO: weird way to get this. need harness b/c doesn't include table borders
                    let harnessEl = scrollerEl.parentNode;
                    scrollerClientWidths[index] = Math.floor(harnessEl.getBoundingClientRect().width - ((chunkI === sideScrollI && forceYScrollbars)
                        ? scrollbarWidth.y // use global because scroller might not have scrollbars yet but will need them in future
                        : 0));
                    scrollerClientHeights[index] = Math.floor(harnessEl.getBoundingClientRect().height - ((sectionI === lastSectionI && forceXScrollbars)
                        ? scrollbarWidth.x // use global because scroller might not have scrollbars yet but will need them in future
                        : 0));
                }
            }
        }
        return { forceYScrollbars, forceXScrollbars, scrollerClientWidths, scrollerClientHeights };
    }
    updateStickyScrolling() {
        let { isRtl } = this.context;
        let argsByKey = this.scrollerElRefs.getAll().map((scrollEl) => [scrollEl, isRtl]);
        this.getStickyScrolling(argsByKey)
            .forEach((stickyScrolling) => stickyScrolling.updateSize());
    }
    updateScrollSyncers() {
        let [sectionCnt, chunksPerSection] = this.getDims();
        let cnt = sectionCnt * chunksPerSection;
        let scrollElsBySection = {};
        let scrollElsByColumn = {};
        let scrollElMap = this.scrollerElRefs.currentMap;
        for (let sectionI = 0; sectionI < sectionCnt; sectionI += 1) {
            let startIndex = sectionI * chunksPerSection;
            let endIndex = startIndex + chunksPerSection;
            scrollElsBySection[sectionI] = collectFromHash(scrollElMap, startIndex, endIndex, 1); // use the filtered
        }
        for (let col = 0; col < chunksPerSection; col += 1) {
            scrollElsByColumn[col] = this.scrollerElRefs.collect(col, cnt, chunksPerSection); // DON'T use the filtered
        }
        this.scrollSyncersBySection = this.getScrollSyncersBySection(scrollElsBySection);
        this.scrollSyncersByColumn = this.getScrollSyncersByColumn(scrollElsByColumn);
    }
    destroyScrollSyncers() {
        mapHash(this.scrollSyncersBySection, destroyScrollSyncer);
        mapHash(this.scrollSyncersByColumn, destroyScrollSyncer);
    }
    getChunkConfigByIndex(index) {
        let chunksPerSection = this.getDims()[1];
        let sectionI = Math.floor(index / chunksPerSection);
        let chunkI = index % chunksPerSection;
        let sectionConfig = this.props.sections[sectionI];
        return sectionConfig && sectionConfig.chunks[chunkI];
    }
    forceScrollLeft(col, scrollLeft) {
        let scrollSyncer = this.scrollSyncersByColumn[col];
        if (scrollSyncer) {
            scrollSyncer.forceScrollLeft(scrollLeft);
        }
    }
    forceScrollTop(sectionI, scrollTop) {
        let scrollSyncer = this.scrollSyncersBySection[sectionI];
        if (scrollSyncer) {
            scrollSyncer.forceScrollTop(scrollTop);
        }
    }
    _handleChunkEl(chunkEl, key) {
        let chunkConfig = this.getChunkConfigByIndex(parseInt(key, 10));
        if (chunkConfig) { // null if section disappeared. bad, b/c won't null-set the elRef
            setRef(chunkConfig.elRef, chunkEl);
        }
    }
    _handleScrollerEl(scrollerEl, key) {
        let chunkConfig = this.getChunkConfigByIndex(parseInt(key, 10));
        if (chunkConfig) { // null if section disappeared. bad, b/c won't null-set the elRef
            setRef(chunkConfig.scrollerElRef, scrollerEl);
        }
    }
    getDims() {
        let sectionCnt = this.props.sections.length;
        let chunksPerSection = sectionCnt ? this.props.sections[0].chunks.length : 0;
        return [sectionCnt, chunksPerSection];
    }
}
ScrollGrid.addStateEquality({
    shrinkWidths: isArraysEqual,
    scrollerClientWidths: isPropsEqual,
    scrollerClientHeights: isPropsEqual,
});
function sumNumbers(numbers) {
    let sum = 0;
    for (let n of numbers) {
        sum += n;
    }
    return sum;
}
function getRowInnerMaxHeight(rowEl) {
    let innerHeights = findElements(rowEl, '.fc-scrollgrid-sync-inner').map(getElHeight);
    if (innerHeights.length) {
        return Math.max(...innerHeights);
    }
    return 0;
}
function getElHeight(el) {
    return el.offsetHeight; // better to deal with integers, for rounding, for PureComponent
}
function renderMacroColGroup(colGroupStats, shrinkWidths) {
    let children = colGroupStats.map((colGroupStat, i) => {
        let width = colGroupStat.width;
        if (width === 'shrink') {
            width = colGroupStat.totalColWidth + sanitizeShrinkWidth(shrinkWidths[i]) + 1; // +1 for border :(
        }
        return ( // eslint-disable-next-line react/jsx-key
        createElement("col", { style: { width } }));
    });
    return createElement('colgroup', {}, ...children);
}
function compileColGroupStat(colGroupConfig) {
    let totalColWidth = sumColProp(colGroupConfig.cols, 'width'); // excludes "shrink"
    let totalColMinWidth = sumColProp(colGroupConfig.cols, 'minWidth');
    let hasShrinkCol = hasShrinkWidth(colGroupConfig.cols);
    let allowXScrolling = colGroupConfig.width !== 'shrink' && Boolean(totalColWidth || totalColMinWidth || hasShrinkCol);
    return {
        hasShrinkCol,
        totalColWidth,
        totalColMinWidth,
        allowXScrolling,
        cols: colGroupConfig.cols,
        width: colGroupConfig.width,
    };
}
function sumColProp(cols, propName) {
    let total = 0;
    for (let col of cols) {
        let val = col[propName];
        if (typeof val === 'number') {
            total += val * (col.span || 1);
        }
    }
    return total;
}
const COL_GROUP_STAT_EQUALITY = {
    cols: isColPropsEqual,
};
function isColGroupStatsEqual(stat0, stat1) {
    return compareObjs(stat0, stat1, COL_GROUP_STAT_EQUALITY);
}
// for memoizers...
function initScrollSyncer(isVertical, ...scrollEls) {
    return new ScrollSyncer(isVertical, scrollEls);
}
function destroyScrollSyncer(scrollSyncer) {
    scrollSyncer.destroy();
}
function initStickyScrolling(scrollEl, isRtl) {
    return new StickyScrolling(scrollEl, isRtl);
}

export { ScrollGrid };
