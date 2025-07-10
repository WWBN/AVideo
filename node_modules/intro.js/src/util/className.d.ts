/**
 * Append CSS classes to an element
 * @api private
 */
export declare const addClass: (element: HTMLElement | SVGElement, ...classNames: string[]) => void;
/**
 * Set CSS classes to an element
 * @param element element to set class
 * @param classNames list of class names
 */
export declare const setClass: (element: HTMLElement | SVGElement, ...classNames: string[]) => void;
/**
 * Remove a class from an element
 *
 * @api private
 */
export declare const removeClass: (element: HTMLElement | SVGElement, classNameRegex: RegExp | string) => void;
