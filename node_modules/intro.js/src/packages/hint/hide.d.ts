import { Hint } from "./hint";
import { HintItem } from "./hintItem";
/**
 * Hide a hint
 *
 * @api private
 */
export declare function hideHint(hint: Hint, hintItem: HintItem): Promise<void>;
/**
 * Hide all hints
 *
 * @api private
 */
export declare function hideHints(hint: Hint): Promise<void>;
