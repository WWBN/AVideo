import { IntroJs } from "../intro";
export type ScrollTo = "off" | "element" | "tooltip";
export type TooltipPosition = "floating" | "top" | "bottom" | "left" | "right" | "top-right-aligned" | "top-left-aligned" | "top-middle-aligned" | "bottom-right-aligned" | "bottom-left-aligned" | "bottom-middle-aligned";
export type HintPosition = "top-left" | "top-right" | "top-middle" | "bottom-left" | "bottom-right" | "bottom-middle" | "middle-left" | "middle-right" | "middle-middle";
export type IntroStep = {
    step: number;
    title: string;
    intro: string;
    tooltipClass?: string;
    highlightClass?: string;
    element?: HTMLElement | string | null;
    position: TooltipPosition;
    scrollTo: ScrollTo;
    disableInteraction?: boolean;
};
export type HintStep = {
    element?: HTMLElement | string | null;
    tooltipClass?: string;
    position: TooltipPosition;
    hint?: string;
    hintTargetElement?: HTMLElement;
    hintAnimation?: boolean;
    hintPosition: HintPosition;
};
/**
 * Go to specific step of introduction
 *
 * @api private
 */
export declare function goToStep(intro: IntroJs, step: number): Promise<void>;
/**
 * Go to the specific step of introduction with the explicit [data-step] number
 *
 * @api private
 */
export declare function goToStepNumber(intro: IntroJs, step: number): Promise<void>;
/**
 * Go to next step on intro
 *
 * @api private
 */
export declare function nextStep(intro: IntroJs): Promise<boolean>;
/**
 * Go to previous step on intro
 *
 * @api private
 */
export declare function previousStep(intro: IntroJs): Promise<boolean>;
