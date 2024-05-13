import { IntroJs } from "./intro";
/**
 * Create a new IntroJS instance
 *
 * @param targetElm Optional target element to start the tour/hint on
 * @returns
 */
declare const introJs: {
    (targetElm?: string | HTMLElement): IntroJs;
    /**
     * Current IntroJs version
     *
     * @property version
     * @type String
     */
    version: string;
    /**
     * key-val object helper for introJs instances
     *
     * @property instances
     * @type Object
     */
    instances: {
        [key: number]: IntroJs;
    };
};
export default introJs;
