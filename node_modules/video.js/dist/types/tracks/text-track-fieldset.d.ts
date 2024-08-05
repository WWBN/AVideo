export default TextTrackFieldset;
/** @import Player from './player' */
/** @import { ContentDescriptor } from '../utils/dom' */
/**
 * Creates fieldset section of 'TextTrackSettings'.
 * Manganes two versions of fieldsets, one for type of 'colors'
 * & the other for 'font', Component adds diferent DOM elements
 * to that fieldset  depending on the type.
 *
 * @extends Component
 */
declare class TextTrackFieldset extends Component {
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
     * @param {string} [options.legendId]
     *        A text with part of an string to create atribute of aria-labelledby.
     *        It passes to 'TextTrackSelect'.
     *
     * @param {string} [options.id]
     *        A text with part of an string to create atribute of aria-labelledby.
     *        It passes to 'TextTrackSelect'.
     *
     * @param {string} [options.legendText]
     *        A text to use as the text content of the legend element.
     *
     * @param {Array} [options.selects]
     *        Array that contains the selects that are use to create 'selects'
     *        components.
     *
     * @param {Array} [options.SelectOptions]
     *        Array that contains the value & textContent of for each of the
     *        options elements, it passes to 'TextTrackSelect'.
     *
     * @param {string} [options.type]
     *        Conditions if some DOM elements will be added to the fieldset
     *        component.
     *
     * @param {Object} [options.selectConfigs]
     *        Object with the following properties that are the selects configurations:
     *        backgroundColor, backgroundOpacity, color, edgeStyle, fontFamily,
     *        fontPercent, textOpacity, windowColor, windowOpacity.
     *        These properties are use to configure the 'TextTrackSelect' Component.
     */
    constructor(player: Player, options?: {
        content?: Dom.ContentDescriptor;
        legendId?: string;
        id?: string;
        legendText?: string;
        selects?: any[];
        SelectOptions?: any[];
        type?: string;
        selectConfigs?: any;
    });
    /**
     * Create the `TextTrackFieldset`'s DOM element
     *
     * @return {Element}
     *         The DOM element that gets created.
     */
    createEl(): Element;
}
import Component from '../component';
import * as Dom from '../utils/dom';
//# sourceMappingURL=text-track-fieldset.d.ts.map