export default TextTrackSettingsFont;
/** @import Player from './player' */
/** @import { ContentDescriptor } from  '../utils/dom' */
/**
 * The component 'TextTrackSettingsFont' displays a set of 'fieldsets'
 * using the component 'TextTrackFieldset'.
 *
 * @extends Component
 */
declare class TextTrackSettingsFont extends Component {
    /**
     * Creates an instance of this class.
     *
     * @param {Player} player
     *        The `Player` that this class should be attached to.
     *
     * @param {Object} [options]
     *        The key/value store of player options.
     *
     * @param {ContentDescriptor} [options.content=undefined]
     *        Provide customized content for this modal.
     *
     * @param {Array} [options.fieldSets]
     *        Array that contains the configurations for the selects.
     *
     * @param {Object} [options.selectConfigs]
     *        Object with the following properties that are the select confugations:
     *        backgroundColor, backgroundOpacity, color, edgeStyle, fontFamily,
     *        fontPercent, textOpacity, windowColor, windowOpacity.
     *        it passes to 'TextTrackFieldset'.
     */
    constructor(player: Player, options?: {
        content?: Dom.ContentDescriptor;
        fieldSets?: any[];
        selectConfigs?: any;
    });
    /**
     * Create the `TextTrackSettingsFont`'s DOM element
     *
     * @return {Element}
     *         The DOM element that gets created.
     */
    createEl(): Element;
}
import Component from '../component';
import * as Dom from '../utils/dom';
//# sourceMappingURL=text-track-settings-font.d.ts.map