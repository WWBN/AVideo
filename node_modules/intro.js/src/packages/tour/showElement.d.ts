import { TourStep } from "./steps";
import { Tour } from "./tour";
/**
 * Show an element on the page
 *
 * @api private
 */
export declare function showElement(tour: Tour, step: TourStep): Promise<void>;
/**
 * To remove all show element(s)
 *
 * @api private
 */
export declare function removeShowElement(): void;
