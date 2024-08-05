export default DurationDisplay;
/** @import Player from '../../player' */
/**
 * Displays the duration
 *
 * @extends Component
 */
declare class DurationDisplay extends Component {
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
    /**
     * Update duration time display.
     *
     * @param {Event} [event]
     *        The `durationchange`, `timeupdate`, or `loadedmetadata` event that caused
     *        this function to be called.
     *
     * @listens Player#durationchange
     * @listens Player#timeupdate
     * @listens Player#loadedmetadata
     */
    updateContent(event?: Event): void;
    /**
     * The text that is added to the `DurationDisplay` for screen reader users.
     *
     * @type {string}
     * @private
     */
    private labelText_;
    /**
     * The text that should display over the `DurationDisplay`s controls. Added to for localization.
     *
     * @type {string}
     * @protected
     *
     * @deprecated in v7; controlText_ is not used in non-active display Components
     */
    protected controlText_: string;
}
import Component from '../../component.js';
import type Player from '../../player';
//# sourceMappingURL=duration-display.d.ts.map