import { Hint } from "./packages/hint";
import { Tour } from "./packages/tour";
declare class LegacyIntroJs extends Tour {
    /**
     * @deprecated introJs().addHints() is deprecated, please use introJs.hint().addHints() instead
     * @param args
     */
    addHints(..._: any[]): void;
    /**
     * @deprecated introJs().addHint() is deprecated, please use introJs.hint.addHint() instead
     * @param args
     */
    addHint(..._: any[]): void;
    /**
     * @deprecated introJs().removeHints() is deprecated, please use introJs.hint.hideHints() instead
     * @param args
     */
    removeHints(..._: any[]): void;
}
/**
 * Intro.js module
 */
declare const introJs: {
    (elementOrSelector?: string | HTMLElement): LegacyIntroJs;
    /**
     * Create a new Intro.js Tour instance
     * @param elementOrSelector Optional target element to start the Tour on
     */
    tour(elementOrSelector?: string | HTMLElement): Tour;
    /**
     * Create a new Intro.js Hint instance
     * @param elementOrSelector Optional target element to start the Hint on
     */
    hint(elementOrSelector?: string | HTMLElement): Hint;
    /**
     * Current Intro.js version
     */
    version: string;
};
export default introJs;
