/**
 * Set tooltip left so it doesn't go off the right side of the window
 *
 * @return boolean true, if tooltipLayerStyleLeft is ok.  false, otherwise.
 */
export default function checkRight(targetOffset: {
    top: number;
    left: number;
    width: number;
    height: number;
}, tooltipLayerStyleLeft: number, tooltipOffset: {
    top: number;
    left: number;
    width: number;
    height: number;
}, windowSize: {
    width: number;
    height: number;
}, tooltipLayer: HTMLElement): boolean;
