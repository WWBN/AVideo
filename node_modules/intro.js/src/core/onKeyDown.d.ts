import { IntroJs } from "../intro";
/**
 * on keyCode:
 * https://developer.mozilla.org/en-US/docs/Web/API/KeyboardEvent/keyCode
 * This feature has been removed from the Web standards.
 * Though some browsers may still support it, it is in
 * the process of being dropped.
 * Instead, you should use KeyboardEvent.code,
 * if it's implemented.
 *
 * jQuery's approach is to test for
 *   (1) e.which, then
 *   (2) e.charCode, then
 *   (3) e.keyCode
 * https://github.com/jquery/jquery/blob/a6b0705294d336ae2f63f7276de0da1195495363/src/event.js#L638
 */
export default function onKeyDown(intro: IntroJs, e: KeyboardEvent): Promise<void>;
