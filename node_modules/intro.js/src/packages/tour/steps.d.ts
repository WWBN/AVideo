import { TooltipPosition } from "../../packages/tooltip";
import { Tour } from "./tour";
export type ScrollTo = "off" | "element" | "tooltip";
export type TourStep = {
    step: number;
    title: string;
    intro: string;
    tooltipClass?: string;
    highlightClass?: string;
    element?: Element | HTMLElement | string | null;
    position: TooltipPosition;
    scrollTo: ScrollTo;
    disableInteraction?: boolean;
};
/**
 * Go to next step on intro
 *
 * @api private
 */
export declare function nextStep(tour: Tour): Promise<boolean>;
/**
 * Go to previous step on intro
 *
 * @api private
 */
export declare function previousStep(tour: Tour): Promise<boolean>;
/**
 * Finds all Intro steps from the data-* attributes and the options.steps array
 *
 * @api private
 */
export declare const fetchSteps: (tour: Tour) => TourStep[];
