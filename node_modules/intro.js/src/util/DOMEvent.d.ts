/**
 * DOMEvent Handles all DOM events
 *
 * methods:
 *
 * on - add event handler
 * off - remove event
 */
interface Events {
    keydown: KeyboardEvent;
    resize: Event;
    scroll: Event;
    click: MouseEvent;
}
type Listener<T> = (e: T) => void | undefined | string | Promise<string | void>;
declare class DOMEvent {
    /**
     * Adds event listener
     */
    on<T extends keyof Events>(obj: EventTarget, type: T, listener: Listener<Events[T]>, useCapture: boolean): void;
    /**
     * Removes event listener
     */
    off<T extends keyof Events>(obj: EventTarget, type: T, listener: Listener<Events[T]>, useCapture: boolean): void;
}
declare const _default: DOMEvent;
export default _default;
