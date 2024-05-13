import { IntroJs } from "../intro";
/**
 * Exit from intro
 *
 * @api private
 * @param {Boolean} force - Setting to `true` will skip the result of beforeExit callback
 */
export default function exitIntro(intro: IntroJs, targetElement: HTMLElement, force?: boolean): Promise<void>;
