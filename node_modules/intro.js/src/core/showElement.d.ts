import { IntroStep } from "./steps";
import { IntroJs } from "../intro";
/**
 * Deletes and recreates the bullets layer
 * @private
 */
export declare function _recreateBullets(intro: IntroJs, targetElement: IntroStep): void;
/**
 * Updates an existing progress bar variables
 * @private
 */
export declare function _updateProgressBar(oldReferenceLayer: HTMLElement, currentStep: number, introItemsLength: number): void;
/**
 * Show an element on the page
 *
 * @api private
 */
export default function _showElement(intro: IntroJs, targetElement: IntroStep): Promise<void>;
