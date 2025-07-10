/**
 * Converts a style object to a css text
 * @param style style object
 * @returns css text
 */
export declare const style: (style: {
    [key: string]: string | number;
}) => string;
/**
 * Sets the style of an DOM element
 */
export default function setStyle(element: HTMLElement, styles: string | {
    [key: string]: string | number;
}): void;
