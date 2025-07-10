import { Tour } from "./tour";
/**
 * Exit from intro
 *
 * @api private
 * @param {Boolean} force - Setting to `true` will skip the result of beforeExit callback
 */
export default function exitIntro(tour: Tour, force?: boolean): Promise<boolean>;
