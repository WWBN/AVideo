export default BigPlayButton;
/**
 * The initial play button that shows before the video has played. The hiding of the
 * `BigPlayButton` get done via CSS and `Player` states.
 *
 * @extends Button
 */
declare class BigPlayButton extends Button {
    constructor(player: any, options: any);
    mouseused_: boolean;
    /**
     * This gets called when a `BigPlayButton` "clicked". See {@link ClickableComponent}
     * for more detailed information on what a click can be.
     *
     * @param {KeyboardEvent|MouseEvent|TouchEvent} event
     *        The `keydown`, `tap`, or `click` event that caused this function to be
     *        called.
     *
     * @listens tap
     * @listens click
     */
    handleClick(event: KeyboardEvent | MouseEvent | TouchEvent): void;
    /**
     * Handle `mousedown` events on the `BigPlayButton`.
     *
     * @param {MouseEvent} event
     *        `mousedown` or `touchstart` event that triggered this function
     *
     * @listens mousedown
     */
    handleMouseDown(event: MouseEvent): void;
}
import Button from "./button.js";
//# sourceMappingURL=big-play-button.d.ts.map