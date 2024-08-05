export default TextTrackSettings;
/**
 * Manipulate Text Tracks settings.
 *
 * @extends ModalDialog
 */
declare class TextTrackSettings extends ModalDialog {
    /**
     * Creates an instance of this class.
     *
     * @param {Player} player
     *         The `Player` that this class should be attached to.
     *
     * @param {Object} [options]
     *         The key/value store of player options.
     */
    constructor(player: Player, options?: any);
    /**
     * Update display of text track settings
     */
    updateDisplay(): void;
    endDialog: Element;
    renderModalComponents(player: any): void;
    bindFunctionsToSelectsAndButtons(): void;
    /**
     * Gets an object of text track settings (or null).
     *
     * @return {Object}
     *         An object with config values parsed from the DOM or localStorage.
     */
    getValues(): any;
    /**
     * Sets text track settings from an object of values.
     *
     * @param {Object} values
     *        An object with config values parsed from the DOM or localStorage.
     */
    setValues(values: any): void;
    /**
     * Sets all `<select>` elements to their default values.
     */
    setDefaults(): void;
    /**
     * Restore texttrack settings from localStorage
     */
    restoreSettings(): void;
    /**
     * Save text track settings to localStorage
     */
    saveSettings(): void;
}
import ModalDialog from '../modal-dialog';
import type Player from '../player';
//# sourceMappingURL=text-track-settings.d.ts.map