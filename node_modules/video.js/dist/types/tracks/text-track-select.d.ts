export default TextTrackSelect;
/**
 * Creates DOM element of 'select' & its options.
 *
 * @extends Component
 */
declare class TextTrackSelect extends Component {
    /**
     * Creates an instance of this class.
     *
     * @param { import('./player').default } player
     *        The `Player` that this class should be attached to.
     *
     * @param {Object} [options]
     *        The key/value store of player options.
     *
     * @param { import('../utils/dom').ContentDescriptor} [options.content=undefined]
     *        Provide customized content for this modal.
     *
     * @param {string} [options.legendId]
     *        A text with part of an string to create atribute of aria-labelledby.
     *
     * @param {string} [options.id]
     *        A text with part of an string to create atribute of aria-labelledby.
     *
     * @param {array} [options.SelectOptions]
     *        Array that contains the value & textContent of for each of the
     *        options elements.
     */
    constructor(player: any, options?: {
        content?: import('../utils/dom').ContentDescriptor;
        legendId?: string;
        id?: string;
        SelectOptions?: any[];
    });
    /**
     * Create the `TextTrackSelect`'s DOM element
     *
     * @return {Element}
     *         The DOM element that gets created.
     */
    createEl(): Element;
    selectLabelledbyIds: string;
}
import Component from "../component";
//# sourceMappingURL=text-track-select.d.ts.map