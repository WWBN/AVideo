/**
 * Create a DOM element with various attributes
 */
export default function _createElement<K extends keyof HTMLElementTagNameMap>(tagName: K, attrs?: {
    [key: string]: string | Function;
}): HTMLElementTagNameMap[K];
