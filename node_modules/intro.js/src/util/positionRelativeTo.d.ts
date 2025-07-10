export declare const getPositionRelativeTo: (relativeElement: HTMLElement, element: HTMLElement, targetElement: HTMLElement, padding: number) => {
    width: string;
    height: string;
    top: string;
    left: string;
} | undefined;
/**
 * Sets the position of the element relative to the target element
 * @api private
 */
export declare const setPositionRelativeTo: (relativeElement: HTMLElement, element: HTMLElement, targetElement: HTMLElement, padding: number) => void;
