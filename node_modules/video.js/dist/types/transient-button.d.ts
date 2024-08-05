export default TransientButton;
export type TransientButtonOptions = {
    /**
     * Control text, usually visible for these buttons
     */
    controlText?: string;
    /**
     * Time in ms that button should initially remain visible
     */
    initialDisplay?: number;
    /**
     * Array of position strings to add basic styles for positioning
     */
    position?: Array<"top" | "neartop" | "bottom" | "left" | "right">;
    /**
     * Class(es) to add
     */
    className?: string;
    /**
     * Whether element sohuld take focus when shown
     */
    takeFocus?: boolean;
    /**
     * Function called on button activation
     */
    clickHandler?: Function;
};
/**
 * A floating transient button.
 * It's recommended to insert these buttons _before_ the control bar with the this argument to `addChild`
 * for a logical tab order.
 *
 * @example
 * ```
 * player.addChild(
 *   'TransientButton',
 *   options,
 *   player.children().indexOf(player.getChild("ControlBar"))
 * )
 * ```
 *
 * @extends Button
 */
declare class TransientButton extends Button {
    /**
     * TransientButton constructor
     *
     * @param {Player} player The button's player
     * @param {TransientButtonOptions} options Options for the transient button
     */
    constructor(player: Player, options: TransientButtonOptions);
    /**
     * Create the button element
     *
     * @return {HTMLButtonElement} The button element
     */
    createEl(): HTMLButtonElement;
    forceDisplayTimeout: any;
}
import Button from './button.js';
import type Player from './player';
//# sourceMappingURL=transient-button.d.ts.map