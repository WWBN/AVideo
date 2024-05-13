/**
 * Set tooltip right so it doesn't go off the left side of the window
 *
 * @return boolean true, if tooltipLayerStyleRight is ok. false, otherwise.
 */
export default function checkLeft(targetOffset: {
    top: number;
    left: number;
    width: number;
    height: number;
}, tooltipLayerStyleRight: number, tooltipOffset: {
    top: number;
    left: number;
    width: number;
    height: number;
}, tooltipLayer: HTMLElement): boolean;
