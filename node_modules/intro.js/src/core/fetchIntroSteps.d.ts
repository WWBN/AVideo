import { IntroJs } from "../intro";
import { IntroStep } from "./steps";
/**
 * Finds all Intro steps from the data-* attributes and the options.steps array
 *
 * @api private
 */
export default function fetchIntroSteps(intro: IntroJs, targetElm: HTMLElement): IntroStep[];
