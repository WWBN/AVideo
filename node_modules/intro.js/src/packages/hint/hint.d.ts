import { Package } from "../package";
import { HintOptions } from "./option";
import { HintItem } from "./hintItem";
type hintsAddedCallback = (this: Hint) => void | Promise<void>;
type hintClickCallback = (this: Hint, item: HintItem) => void | Promise<void>;
type hintCloseCallback = (this: Hint, item: HintItem) => void | Promise<void>;
export declare class Hint implements Package<HintOptions> {
    private _root;
    private _hints;
    private readonly _targetElement;
    private _options;
    private _activeHintSignal;
    private _refreshesSignal;
    private readonly callbacks;
    private _hintsAutoRefreshFunction?;
    private _windowClickFunction?;
    /**
     * Create a new Hint instance
     * @param elementOrSelector Optional target element or CSS query to start the Hint on
     * @param options Optional Hint options
     */
    constructor(elementOrSelector?: string | HTMLElement, options?: Partial<HintOptions>);
    /**
     * Get the callback function for the provided callback name
     * @param callbackName The name of the callback
     */
    callback<K extends keyof typeof this.callbacks>(callbackName: K): (typeof this.callbacks)[K] | undefined;
    /**
     * Get the target element for the Hint
     */
    getTargetElement(): HTMLElement;
    /**
     * Get the Hint items
     */
    getHints(): HintItem[];
    /**
     * Get the Hint item for the provided step ID
     * @param stepId The step ID
     */
    getHint(stepId: number): HintItem | undefined;
    /**
     * Set the Hint items
     * @param hints The Hint items
     */
    setHints(hints: HintItem[]): this;
    /**
     * Add a Hint item
     * @param hint The Hint item
     */
    addHint(hint: HintItem): this;
    /**
     * Get the active hint signal
     * This is meant to be used internally by the Hint package
     */
    getActiveHintSignal(): import("../dom").State<number | undefined>;
    /**
     * Returns the underlying state of the refreshes
     * This is an internal method and should not be used outside of the package.
     */
    getRefreshesSignal(): import("../dom").State<number>;
    /**
     * Returns true if the hints are rendered
     */
    isRendered(): boolean;
    private createRoot;
    private recreateRoot;
    /**
     * Render hints on the page
     */
    render(): Promise<this>;
    /**
     * Enable closing the dialog when the user clicks outside the hint
     */
    enableCloseDialogOnWindowClick(): void;
    /**
     * Disable closing the dialog when the user clicks outside the hint
     */
    disableCloseDialogOnWindowClick(): void;
    /**
     * @deprecated renderHints() is deprecated, please use render() instead
     */
    addHints(): Promise<this>;
    /**
     * Hide a specific hint on the page
     * @param stepId The hint step ID
     */
    hideHint(stepId: number): Promise<this>;
    /**
     * Hide all hints on the page
     */
    hideHints(): Promise<this>;
    /**
     * Show a specific hint on the page
     * @param stepId The hint step ID
     */
    showHint(stepId: number): this;
    /**
     * Show all hints on the page
     */
    showHints(): Promise<this>;
    /**
     * Destroys and removes all hint elements on the page
     * Useful when you want to destroy the elements and add them again (e.g. a modal or popup)
     */
    destroy(): this;
    /**
     * @deprecated removeHints() is deprecated, please use destroy() instead
     */
    removeHints(): this;
    /**
     * Remove one single hint element from the page
     * Useful when you want to destroy the element and add them again (e.g. a modal or popup)
     * Use removeHints if you want to remove all elements.
     *
     * @param stepId The hint step ID
     */
    removeHint(stepId: number): this;
    /**
     * Show hint dialog for a specific hint
     * @param stepId The hint step ID
     */
    showHintDialog(stepId: number): Promise<this | undefined>;
    /**
     * Hide hint dialog from the page
     */
    hideHintDialog(): this;
    /**
     * Refresh the hints on the page
     */
    refresh(): this;
    /**
     * Enable hint auto refresh on page scroll and resize for hints
     */
    enableHintAutoRefresh(): this;
    /**
     * Disable hint auto refresh on page scroll and resize for hints
     */
    disableHintAutoRefresh(): this;
    /**
     * Get specific Hint option
     * @param key The option key
     */
    getOption<K extends keyof HintOptions>(key: K): HintOptions[K];
    /**
     * Set Hint options
     * @param partialOptions Hint options
     */
    setOptions(partialOptions: Partial<HintOptions>): this;
    /**
     * Set specific Hint option
     * @param key Option key
     * @param value Option value
     */
    setOption<K extends keyof HintOptions>(key: K, value: HintOptions[K]): this;
    /**
     * Clone the Hint instance
     */
    clone(): ThisType<this>;
    /**
     * Returns true if the Hint is active
     */
    isActive(): boolean;
    onHintsAdded(providedCallback: hintsAddedCallback): this;
    /**
     * @deprecated onhintsadded is deprecated, please use onHintsAdded instead
     * @param providedCallback callback function
     */
    onhintsadded(providedCallback: hintsAddedCallback): void;
    /**
     * Callback for when hint items are clicked
     * @param providedCallback callback function
     */
    onHintClick(providedCallback: hintClickCallback): this;
    /**
     * @deprecated onhintclick is deprecated, please use onHintClick instead
     * @param providedCallback
     */
    onhintclick(providedCallback: hintClickCallback): void;
    /**
     * Callback for when hint items are closed
     * @param providedCallback callback function
     */
    onHintClose(providedCallback: hintCloseCallback): this;
    /**
     * @deprecated onhintclose is deprecated, please use onHintClose instead
     * @param providedCallback
     */
    onhintclose(providedCallback: hintCloseCallback): void;
}
export {};
