export type Offset = {
    width: number;
    height: number;
    left: number;
    right: number;
    top: number;
    bottom: number;
    absoluteTop: number;
    absoluteLeft: number;
    absoluteRight: number;
    absoluteBottom: number;
};
/**
 * Get an element position on the page relative to another element (or body) including scroll offset
 * Thanks to `meouw`: http://stackoverflow.com/a/442474/375966
 *
 * @api private
 * @returns Element's position info
 */
export default function getOffset(element: HTMLElement, relativeEl?: HTMLElement): Offset;
