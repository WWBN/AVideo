import { HintStep, IntroStep } from "./steps";
import { IntroJs } from "../intro";
/**
 * Render tooltip box in the page
 *
 * @api private
 */
export default function placeTooltip(intro: IntroJs, currentStep: IntroStep | HintStep, tooltipLayer: HTMLElement, arrowLayer: HTMLElement, hintMode?: boolean): void;
