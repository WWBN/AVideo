export default SpatialNavigation;
/**
 * Spatial Navigation in Video.js enhances user experience and accessibility on smartTV devices,
 * enabling seamless navigation through interactive elements within the player using remote control arrow keys.
 * This functionality allows users to effortlessly navigate through focusable components.
 *
 * @extends EventTarget
 */
declare class SpatialNavigation extends EventTarget {
    /**
     * Constructs a SpatialNavigation instance with initial settings.
     * Sets up the player instance, and prepares the spatial navigation system.
     *
     * @class
     * @param {Player} player - The Video.js player instance to which the spatial navigation is attached.
     */
    constructor(player: Player);
    player_: Player;
    focusableComponents: any[];
    isListening_: boolean;
    isPaused_: boolean;
    /**
     * Responds to keydown events for spatial navigation and media control.
     *
     * Determines if spatial navigation or media control is active and handles key inputs accordingly.
     *
     * @param {KeyboardEvent} event - The keydown event to be handled.
     */
    onKeyDown_(event: KeyboardEvent): void;
    lastFocusedComponent_: Component;
    /**
     * Starts the spatial navigation by adding a keydown event listener to the video container.
     * This method ensures that the event listener is added only once.
     */
    start(): void;
    /**
     * Stops the spatial navigation by removing the keydown event listener from the video container.
     * Also sets the `isListening_` flag to false.
     */
    stop(): void;
    /**
     * Performs media control actions based on the given key input.
     *
     * Controls the playback and seeking functionalities of the media player.
     *
     * @param {string} key - The key representing the media action to be performed.
     *   Accepted keys: 'play', 'pause', 'ff' (fast-forward), 'rw' (rewind).
     */
    performMediaAction_(key: string): void;
    /**
     * Prevent liveThreshold from causing seeks to seem like they
     * are not happening from a user perspective.
     *
     * @param {number} ct
     *        current time to seek to
     */
    userSeek_(ct: number): void;
    /**
     * Pauses the spatial navigation functionality.
     * This method sets a flag that can be used to temporarily disable the navigation logic.
     */
    pause(): void;
    /**
     * Resumes the spatial navigation functionality if it has been paused.
     * This method resets the pause flag, re-enabling the navigation logic.
     */
    resume(): void;
    /**
     * Handles Player Blur.
     *
     * @param {string|Event|Object} event
     *        The name of the event, an `Event`, or an object with a key of type set to
     *        an event name.
     *
     * Calls for handling of the Player Blur if:
     * *The next focused element is not a child of current focused element &
     * The next focused element is not a child of the Player.
     * *There is no next focused element
     */
    handlePlayerBlur_(event: string | Event | any): void;
    /**
     * Handles the Player focus event.
     *
     * Calls for handling of the Player Focus if current element is focusable.
     */
    handlePlayerFocus_(): void;
    /**
     * Gets a set of focusable components.
     *
     * @return {Array}
     *         Returns an array of focusable components.
     */
    updateFocusableComponents(): any[];
    /**
     * Finds a suitable child element within the provided component's DOM element.
     *
     * @param {Object} component - The component containing the DOM element to search within.
     * @return {HTMLElement|null} Returns the suitable child element if found, or null if not found.
     */
    findSuitableDOMChild(component: any): HTMLElement | null;
    /**
     * Gets the currently focused component from the list of focusable components.
     * If a target element is provided, it uses that element to find the corresponding
     * component. If no target is provided, it defaults to using the document's currently
     * active element.
     *
     * @param {HTMLElement} [target] - The DOM element to check against the focusable components.
     *                                 If not provided, `document.activeElement` is used.
     * @return {Component|null} - Returns the focused component if found among the focusable components,
     *                            otherwise returns null if no matching component is found.
     */
    getCurrentComponent(target?: HTMLElement): Component | null;
    /**
     * Adds a component to the array of focusable components.
     *
     * @param {Component} component
     *        The `Component` to be added.
     */
    add(component: Component): void;
    /**
     * Removes component from the array of focusable components.
     *
     * @param {Component} component - The component to be removed from the focusable components array.
     */
    remove(component: Component): void;
    /**
     * Clears array of focusable components.
     */
    clear(): void;
    /**
     * Navigates to the next focusable component based on the specified direction.
     *
     * @param {string} direction 'up', 'down', 'left', 'right'
     */
    move(direction: string): void;
    /**
     * Finds the best candidate on the current center position,
     * the list of candidates, and the specified navigation direction.
     *
     * @param {Object} currentCenter The center position of the current focused component element.
     * @param {Array} candidates An array of candidate components to receive focus.
     * @param {string} direction The direction of navigation ('up', 'down', 'left', 'right').
     * @return {Object|null} The component that is the best candidate for receiving focus.
     */
    findBestCandidate_(currentCenter: any, candidates: any[], direction: string): any | null;
    /**
     * Determines if a target rectangle is in the specified navigation direction
     * relative to a source rectangle.
     *
     * @param {Object} srcRect The bounding rectangle of the source element.
     * @param {Object} targetRect The bounding rectangle of the target element.
     * @param {string} direction The navigation direction ('up', 'down', 'left', 'right').
     * @return {boolean} True if the target is in the specified direction relative to the source.
     */
    isInDirection_(srcRect: any, targetRect: any, direction: string): boolean;
    /**
     * Focus the last focused component saved before blur on player.
     */
    refocusComponent(): void;
    /**
     * Focuses on a given component.
     * If the component is available to be focused, it focuses on the component.
     * If not, it attempts to find a suitable DOM child within the component and focuses on it.
     *
     * @param {Component} component - The component to be focused.
     */
    focus(component: Component): void;
    /**
     * Calculates the distance between two points, adjusting the calculation based on
     * the specified navigation direction.
     *
     * @param {Object} center1 The center point of the first element.
     * @param {Object} center2 The center point of the second element.
     * @param {string} direction The direction of navigation ('up', 'down', 'left', 'right').
     * @return {number} The calculated distance between the two centers.
     */
    calculateDistance_(center1: any, center2: any, direction: string): number;
    /**
     * This gets called by 'handlePlayerBlur_' if 'spatialNavigation' is enabled.
     * Searches for the first 'TextTrackSelect' inside of modal to focus.
     *
     * @private
     */
    private searchForTrackSelect_;
}
import EventTarget from './event-target';
import type Player from './player';
import type Component from './component';
//# sourceMappingURL=spatial-navigation.d.ts.map