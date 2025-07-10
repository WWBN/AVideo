import { Hint } from "./hint";
import { HintItem } from "./hintItem";
/**
 * Show all hints
 *
 * @api private
 */
export declare function showHints(hint: Hint): Promise<void>;
/**
 * Show a hint
 *
 * @api private
 */
export declare function showHint(hintItem: HintItem): void;
