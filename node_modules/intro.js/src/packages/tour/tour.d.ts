import { TourStep } from "./steps";
import { Package } from "../package";
import { introAfterChangeCallback, introBeforeChangeCallback, introBeforeExitCallback, introChangeCallback, introCompleteCallback, introExitCallback, introSkipCallback, introStartCallback } from "./callback";
import { TourOptions } from "./option";
/**
 * Intro.js Tour class
 */
export declare class Tour implements Package<TourOptions> {
    private _steps;
    private _currentStepSignal;
    private _refreshesSignal;
    private _root;
    private _direction;
    private readonly _targetElement;
    private _options;
    private _floatingElement;
    private readonly callbacks;
    private _keyboardNavigationHandler?;
    private _refreshOnResizeHandler?;
    /**
     * Create a new Tour instance
     * @param elementOrSelector Optional target element or CSS query to start the Tour on
     * @param options Optional Tour options
     */
    constructor(elementOrSelector?: string | HTMLElement, options?: Partial<TourOptions>);
    /**
     * Get a specific callback function
     * @param callbackName callback name
     */
    callback<K extends keyof typeof this.callbacks>(callbackName: K): (typeof this.callbacks)[K] | undefined;
    /**
     * Go to a specific step of the tour
     * @param step step number
     */
    goToStep(step: number): Promise<this>;
    /**
     * Go to a specific step of the tour with the explicit [data-step] number
     * @param stepNumber [data-step] value of the step
     */
    goToStepNumber(stepNumber: number): Promise<this>;
    /**
     * Add a step to the tour options.
     * This method should be used in conjunction with the `start()` method.
     * @param step step to add
     */
    addStep(step: Partial<TourStep>): this;
    /**
     * Add multiple steps to the tour options.
     * This method should be used in conjunction with the `start()` method.
     * @param steps steps to add
     */
    addSteps(steps: Partial<TourStep>[]): this;
    /**
     * Set the steps of the tour
     * @param steps steps to set
     */
    setSteps(steps: TourStep[]): this;
    /**
     * Get all available steps of the tour
     */
    getSteps(): TourStep[];
    /**
     * Get a specific step of the tour
     * @param {number} step step number
     */
    getStep(step: number): TourStep;
    /**
     * Returns the underlying state of the current step
     * This is an internal method and should not be used outside of the package.
     */
    getCurrentStepSignal(): import("../dom").State<number | undefined>;
    /**
     * Returns the underlying state of the refreshes
     * This is an internal method and should not be used outside of the package.
     */
    getRefreshesSignal(): import("../dom").State<number>;
    /**
     * Get the current step of the tour
     */
    getCurrentStep(): number | undefined;
    /**
     * @deprecated `currentStep()` is deprecated, please use `getCurrentStep()` instead.
     */
    currentStep(): number | undefined;
    resetCurrentStep(): void;
    /**
     * Set the current step of the tour and the direction of the tour
     * @param step
     */
    setCurrentStep(step: number): this;
    /**
     * Increment the current step of the tour (does not start the tour step, must be called in conjunction with `nextStep`)
     */
    incrementCurrentStep(): this;
    /**
     * Decrement the current step of the tour (does not start the tour step, must be in conjunction with `previousStep`)
     */
    decrementCurrentStep(): this;
    /**
     * Get the direction of the tour (forward or backward)
     */
    getDirection(): "backward" | "forward";
    /**
     * Go to the next step of the tour
     */
    nextStep(): Promise<this>;
    /**
     * Go to the previous step of the tour
     */
    previousStep(): Promise<this>;
    /**
     * Check if the current step is the last step
     */
    isEnd(): boolean;
    /**
     * Check if the current step is the last step of the tour
     */
    isLastStep(): boolean;
    /**
     * Get the target element of the tour
     */
    getTargetElement(): HTMLElement;
    /**
     * Set the options for the tour
     * @param partialOptions key/value pair of options
     */
    setOptions(partialOptions: Partial<TourOptions>): this;
    /**
     * Set a specific option for the tour
     * @param key option key
     * @param value option value
     */
    setOption<K extends keyof TourOptions>(key: K, value: TourOptions[K]): this;
    /**
     * Get a specific option for the tour
     * @param key option key
     */
    getOption<K extends keyof TourOptions>(key: K): TourOptions[K];
    /**
     * Clone the current tour instance
     */
    clone(): ThisType<this>;
    /**
     * Returns true if the tour instance is active
     */
    isActive(): boolean;
    /**
     * Returns true if the tour has started
     */
    hasStarted(): boolean;
    /**
     * Set the `dontShowAgain` option for the tour so that the tour does not show twice to the same user
     * This is a persistent option that is stored in the browser's cookies
     *
     * @param dontShowAgain boolean value to set the `dontShowAgain` option
     */
    setDontShowAgain(dontShowAgain: boolean): this;
    /**
     * Enable keyboard navigation for the tour
     */
    enableKeyboardNavigation(): this;
    /**
     * Disable keyboard navigation for the tour
     */
    disableKeyboardNavigation(): this;
    /**
     * Enable refresh on window resize for the tour
     */
    enableRefreshOnResize(): void;
    /**
     * Disable refresh on window resize for the tour
     */
    disableRefreshOnResize(): void;
    /**
     * Append the floating element to the target element.
     * Floating element is a helper element that is used when the step does not have a target element.
     * For internal use only.
     */
    appendFloatingElement(): Element;
    /**
     * Create the root element for the tour
     */
    private createRoot;
    /**
     * Deletes the root element and recreates it
     */
    private recreateRoot;
    /**
     * Starts the tour and shows the first step
     */
    start(): Promise<this>;
    /**
     * Exit the tour
     * @param {boolean} force whether to force exit the tour
     */
    exit(force?: boolean): Promise<this>;
    /**
     * Refresh the tour
     * @param {boolean} refreshSteps whether to refresh the tour steps
     */
    refresh(refreshSteps?: boolean): this;
    /**
     * @deprecated onbeforechange is deprecated, please use onBeforeChange instead.
     */
    onbeforechange(callback: introBeforeChangeCallback): this;
    /**
     * Add a callback to be called before the tour changes steps
     * @param {Function} callback callback function to be called
     */
    onBeforeChange(callback: introBeforeChangeCallback): this;
    /**
     * @deprecated onchange is deprecated, please use onChange instead.
     */
    onchange(callback: introChangeCallback): void;
    /**
     * Add a callback to be called when the tour changes steps
     * @param {Function} callback callback function to be called
     */
    onChange(callback: introChangeCallback): this;
    /**
     * @deprecated onafterchange is deprecated, please use onAfterChange instead.
     */
    onafterchange(callback: introAfterChangeCallback): void;
    /**
     * Add a callback to be called after the tour changes steps
     * @param {Function} callback callback function to be called
     */
    onAfterChange(callback: introAfterChangeCallback): this;
    /**
     * @deprecated oncomplete is deprecated, please use onComplete instead.
     */
    oncomplete(callback: introCompleteCallback): this;
    /**
     * Add a callback to be called when the tour is completed
     * @param {Function} callback callback function to be called
     */
    onComplete(callback: introCompleteCallback): this;
    /**
     * @deprecated onstart is deprecated, please use onStart instead.
     */
    onstart(callback: introStartCallback): this;
    /**
     * Add a callback to be called when the tour is started
     * @param {Function} callback callback function to be called
     */
    onStart(callback: introStartCallback): this;
    /**
     * @deprecated onexit is deprecated, please use onExit instead.
     */
    onexit(callback: introExitCallback): this;
    /**
     * Add a callback to be called when the tour is exited
     * @param {Function} callback callback function to be called
     */
    onExit(callback: introExitCallback): this;
    /**
     * @deprecated onskip is deprecated, please use onSkip instead.
     */
    onskip(callback: introSkipCallback): this;
    /**
     * Add a callback to be called when the tour is skipped
     * @param {Function} callback callback function to be called
     */
    onSkip(callback: introSkipCallback): this;
    /**
     * @deprecated onbeforeexit is deprecated, please use onBeforeExit instead.
     */
    onbeforeexit(callback: introBeforeExitCallback): this;
    /**
     * Add a callback to be called before the tour is exited
     * @param {Function} callback callback function to be called
     */
    onBeforeExit(callback: introBeforeExitCallback): this;
}
