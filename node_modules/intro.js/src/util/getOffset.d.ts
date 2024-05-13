/**
 * Get an element position on the page relative to another element (or body)
 * Thanks to `meouw`: http://stackoverflow.com/a/442474/375966
 *
 * @api private
 * @returns Element's position info
 */
export default function getOffset(element: HTMLElement, relativeEl?: HTMLElement): {
    width: number;
    height: number;
    left: number;
    top: number;
};
