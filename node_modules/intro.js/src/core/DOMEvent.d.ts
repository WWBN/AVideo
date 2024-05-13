import { IntroJs } from "../intro";
/**
 * DOMEvent Handles all DOM events
 *
 * methods:
 *
 * on - add event handler
 * off - remove event
 */
declare class DOMEvent {
    private readonly events_key;
    /**
     * Gets a unique ID for an event listener
     */
    private _id;
    /**
     * Adds event listener
     */
    on(obj: EventTarget, type: string, listener: (context: IntroJs | EventTarget, e: Event) => void | undefined | string | Promise<string | void>, context: IntroJs, useCapture: boolean): void;
    /**
     * Removes event listener
     */
    off(obj: EventTarget, type: string, listener: (context: IntroJs | EventTarget, e: Event) => void | undefined | string | Promise<string | void>, context: IntroJs, useCapture: boolean): void;
}
declare const _default: DOMEvent;
export default _default;
