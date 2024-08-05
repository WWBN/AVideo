export default LoadProgressBar;
/**
 * Shows loading progress
 *
 * @extends Component
 */
declare class LoadProgressBar extends Component {
    /**
     * Creates an instance of this class.
     *
     * @param {Player} player
     *        The `Player` that this class should be attached to.
     *
     * @param {Object} [options]
     *        The key/value store of player options.
     */
    constructor(player: Player, options?: any);
    partEls_: any[];
    /**
     * Create the `Component`'s DOM element
     *
     * @return {Element}
     *         The element that was created.
     */
    createEl(): Element;
    percentageEl_: Element;
    dispose(): void;
    /**
     * Update progress bar
     *
     * @param {Event} [event]
     *        The `progress` event that caused this function to run.
     *
     * @listens Player#progress
     */
    update(event?: Event): void;
    percent_: any;
}
import Component from '../../component.js';
import type Player from '../../player';
//# sourceMappingURL=load-progress-bar.d.ts.map