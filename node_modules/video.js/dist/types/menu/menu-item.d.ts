export default MenuItem;
/** @import Player from '../player' */
/**
 * The component for a menu item. `<li>`
 *
 * @extends ClickableComponent
 */
declare class MenuItem extends ClickableComponent {
    /**
     * Creates an instance of the this class.
     *
     * @param {Player} player
     *        The `Player` that this class should be attached to.
     *
     * @param {Object} [options={}]
     *        The key/value store of player options.
     *
     */
    constructor(player: Player, options?: any);
    selectable: any;
    isSelected_: any;
    multiSelectable: any;
    nonIconControl: boolean;
    /**
     * Any click on a `MenuItem` puts it into the selected state.
     * See {@link ClickableComponent#handleClick} for instances where this is called.
     *
     * @param {Event} event
     *        The `keydown`, `tap`, or `click` event that caused this function to be
     *        called.
     *
     * @listens tap
     * @listens click
     */
    handleClick(event: Event): void;
    /**
     * Set the state for this menu item as selected or not.
     *
     * @param {boolean} selected
     *        if the menu item is selected or not
     */
    selected(selected: boolean): void;
}
import ClickableComponent from '../clickable-component.js';
import type Player from '../player';
//# sourceMappingURL=menu-item.d.ts.map