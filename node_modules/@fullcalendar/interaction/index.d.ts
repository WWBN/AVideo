import { DatePointApi, ViewApi, EventApi, EventChangeArg, Duration, EventDropArg, PluginDef } from '@fullcalendar/core';
import { Emitter, PointerDragEvent, Rect, ScrollController, ElementDragging, Identity, DragMetaInput } from '@fullcalendar/core/internal';

declare class PointerDragging {
    containerEl: EventTarget;
    subjectEl: HTMLElement | null;
    emitter: Emitter<any>;
    selector: string;
    handleSelector: string;
    shouldIgnoreMove: boolean;
    shouldWatchScroll: boolean;
    isDragging: boolean;
    isTouchDragging: boolean;
    wasTouchScroll: boolean;
    origPageX: number;
    origPageY: number;
    prevPageX: number;
    prevPageY: number;
    prevScrollX: number;
    prevScrollY: number;
    constructor(containerEl: EventTarget);
    destroy(): void;
    tryStart(ev: UIEvent): boolean;
    cleanup(): void;
    querySubjectEl(ev: UIEvent): HTMLElement;
    handleMouseDown: (ev: MouseEvent) => void;
    handleMouseMove: (ev: MouseEvent) => void;
    handleMouseUp: (ev: MouseEvent) => void;
    shouldIgnoreMouse(): number | boolean;
    handleTouchStart: (ev: TouchEvent) => void;
    handleTouchMove: (ev: TouchEvent) => void;
    handleTouchEnd: (ev: TouchEvent) => void;
    handleTouchScroll: () => void;
    cancelTouchScroll(): void;
    initScrollWatch(ev: PointerDragEvent): void;
    recordCoords(ev: PointerDragEvent): void;
    handleScroll: (ev: UIEvent) => void;
    destroyScrollWatch(): void;
    createEventFromMouse(ev: MouseEvent, isFirst?: boolean): PointerDragEvent;
    createEventFromTouch(ev: TouchEvent, isFirst?: boolean): PointerDragEvent;
}

declare class ElementMirror {
    isVisible: boolean;
    origScreenX?: number;
    origScreenY?: number;
    deltaX?: number;
    deltaY?: number;
    sourceEl: HTMLElement | null;
    mirrorEl: HTMLElement | null;
    sourceElRect: Rect | null;
    parentNode: HTMLElement;
    zIndex: number;
    revertDuration: number;
    start(sourceEl: HTMLElement, pageX: number, pageY: number): void;
    handleMove(pageX: number, pageY: number): void;
    setIsVisible(bool: boolean): void;
    stop(needsRevertAnimation: boolean, callback: () => void): void;
    doRevertAnimation(callback: () => void, revertDuration: number): void;
    cleanup(): void;
    updateElPosition(): void;
    getMirrorEl(): HTMLElement;
}

declare abstract class ScrollGeomCache extends ScrollController {
    clientRect: Rect;
    origScrollTop: number;
    origScrollLeft: number;
    protected scrollController: ScrollController;
    protected doesListening: boolean;
    protected scrollTop: number;
    protected scrollLeft: number;
    protected scrollWidth: number;
    protected scrollHeight: number;
    protected clientWidth: number;
    protected clientHeight: number;
    constructor(scrollController: ScrollController, doesListening: boolean);
    abstract getEventTarget(): EventTarget;
    abstract computeClientRect(): Rect;
    destroy(): void;
    handleScroll: () => void;
    getScrollTop(): number;
    getScrollLeft(): number;
    setScrollTop(top: number): void;
    setScrollLeft(top: number): void;
    getClientWidth(): number;
    getClientHeight(): number;
    getScrollWidth(): number;
    getScrollHeight(): number;
    handleScrollChange(): void;
}

declare class AutoScroller {
    isEnabled: boolean;
    scrollQuery: (Window | string)[];
    edgeThreshold: number;
    maxVelocity: number;
    pointerScreenX: number | null;
    pointerScreenY: number | null;
    isAnimating: boolean;
    scrollCaches: ScrollGeomCache[] | null;
    msSinceRequest?: number;
    everMovedUp: boolean;
    everMovedDown: boolean;
    everMovedLeft: boolean;
    everMovedRight: boolean;
    start(pageX: number, pageY: number, scrollStartEl: HTMLElement): void;
    handleMove(pageX: number, pageY: number): void;
    stop(): void;
    requestAnimation(now: number): void;
    private animate;
    private handleSide;
    private computeBestEdge;
    private buildCaches;
    private queryScrollEls;
}

declare class FeaturefulElementDragging extends ElementDragging {
    private containerEl;
    pointer: PointerDragging;
    mirror: ElementMirror;
    autoScroller: AutoScroller;
    delay: number | null;
    minDistance: number;
    touchScrollAllowed: boolean;
    mirrorNeedsRevert: boolean;
    isInteracting: boolean;
    isDragging: boolean;
    isDelayEnded: boolean;
    isDistanceSurpassed: boolean;
    delayTimeoutId: number | null;
    constructor(containerEl: HTMLElement, selector?: string);
    destroy(): void;
    onPointerDown: (ev: PointerDragEvent) => void;
    onPointerMove: (ev: PointerDragEvent) => void;
    onPointerUp: (ev: PointerDragEvent) => void;
    startDelay(ev: PointerDragEvent): void;
    handleDelayEnd(ev: PointerDragEvent): void;
    handleDistanceSurpassed(ev: PointerDragEvent): void;
    tryStartDrag(ev: PointerDragEvent): void;
    tryStopDrag(ev: PointerDragEvent): void;
    stopDrag(ev: PointerDragEvent): void;
    setIgnoreMove(bool: boolean): void;
    setMirrorIsVisible(bool: boolean): void;
    setMirrorNeedsRevert(bool: boolean): void;
    setAutoScrollEnabled(bool: boolean): void;
}

interface DateClickArg extends DatePointApi {
    dayEl: HTMLElement;
    jsEvent: MouseEvent;
    view: ViewApi;
}

type EventDragStopArg = EventDragArg;
type EventDragStartArg = EventDragArg;
interface EventDragArg {
    el: HTMLElement;
    event: EventApi;
    jsEvent: MouseEvent;
    view: ViewApi;
}

type EventResizeStartArg = EventResizeStartStopArg;
type EventResizeStopArg = EventResizeStartStopArg;
interface EventResizeStartStopArg {
    el: HTMLElement;
    event: EventApi;
    jsEvent: MouseEvent;
    view: ViewApi;
}
interface EventResizeDoneArg extends EventChangeArg {
    el: HTMLElement;
    startDelta: Duration;
    endDelta: Duration;
    jsEvent: MouseEvent;
    view: ViewApi;
}

interface DropArg extends DatePointApi {
    draggedEl: HTMLElement;
    jsEvent: MouseEvent;
    view: ViewApi;
}
type EventReceiveArg = EventReceiveLeaveArg;
type EventLeaveArg = EventReceiveLeaveArg;
interface EventReceiveLeaveArg {
    draggedEl: HTMLElement;
    event: EventApi;
    relatedEvents: EventApi[];
    revert: () => void;
    view: ViewApi;
}

declare const OPTION_REFINERS: {
    fixedMirrorParent: Identity<HTMLElement>;
};
declare const LISTENER_REFINERS: {
    dateClick: Identity<(arg: DateClickArg) => void>;
    eventDragStart: Identity<(arg: EventDragStartArg) => void>;
    eventDragStop: Identity<(arg: EventDragStopArg) => void>;
    eventDrop: Identity<(arg: EventDropArg) => void>;
    eventResizeStart: Identity<(arg: EventResizeStartArg) => void>;
    eventResizeStop: Identity<(arg: EventResizeStopArg) => void>;
    eventResize: Identity<(arg: EventResizeDoneArg) => void>;
    drop: Identity<(arg: DropArg) => void>;
    eventReceive: Identity<(arg: EventReceiveArg) => void>;
    eventLeave: Identity<(arg: EventLeaveArg) => void>;
};

type ExtraOptionRefiners = typeof OPTION_REFINERS;
type ExtraListenerRefiners = typeof LISTENER_REFINERS;
declare module '@fullcalendar/core/internal' {
    interface BaseOptionRefiners extends ExtraOptionRefiners {
    }
    interface CalendarListenerRefiners extends ExtraListenerRefiners {
    }
}
//# sourceMappingURL=ambient.d.ts.map

type DragMetaGenerator = DragMetaInput | ((el: HTMLElement) => DragMetaInput);

interface ExternalDraggableSettings {
    eventData?: DragMetaGenerator;
    itemSelector?: string;
    minDistance?: number;
    longPressDelay?: number;
    appendTo?: HTMLElement;
}
declare class ExternalDraggable {
    dragging: FeaturefulElementDragging;
    settings: ExternalDraggableSettings;
    constructor(el: HTMLElement, settings?: ExternalDraggableSettings);
    handlePointerDown: (ev: PointerDragEvent) => void;
    handleDragStart: (ev: PointerDragEvent) => void;
    destroy(): void;
}

declare class InferredElementDragging extends ElementDragging {
    pointer: PointerDragging;
    shouldIgnoreMove: boolean;
    mirrorSelector: string;
    currentMirrorEl: HTMLElement | null;
    constructor(containerEl: HTMLElement);
    destroy(): void;
    handlePointerDown: (ev: PointerDragEvent) => void;
    handlePointerMove: (ev: PointerDragEvent) => void;
    handlePointerUp: (ev: PointerDragEvent) => void;
    setIgnoreMove(bool: boolean): void;
    setMirrorIsVisible(bool: boolean): void;
}

interface ThirdPartyDraggableSettings {
    eventData?: DragMetaGenerator;
    itemSelector?: string;
    mirrorSelector?: string;
}
declare class ThirdPartyDraggable {
    dragging: InferredElementDragging;
    constructor(containerOrSettings?: EventTarget | ThirdPartyDraggableSettings, settings?: ThirdPartyDraggableSettings);
    destroy(): void;
}

declare const _default: PluginDef;
//# sourceMappingURL=index.d.ts.map

export { DateClickArg, ExternalDraggable as Draggable, DropArg, EventDragStartArg, EventDragStopArg, EventLeaveArg, EventReceiveArg, EventResizeDoneArg, EventResizeStartArg, EventResizeStopArg, ThirdPartyDraggable, _default as default };
