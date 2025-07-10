import { TooltipPosition } from "../../packages/tooltip";
import { Hint } from "./hint";
import { State } from "../dom";
export type HintPosition = "top-left" | "top-right" | "top-middle" | "bottom-left" | "bottom-right" | "bottom-middle" | "middle-left" | "middle-right" | "middle-middle";
export type HintItem = {
    element?: HTMLElement | string | null;
    tooltipClass?: string;
    position: TooltipPosition;
    hint?: string;
    hintTooltipElement?: HTMLElement;
    hintAnimation?: boolean;
    hintPosition: HintPosition;
    isActive?: State<boolean>;
};
export declare const fetchHintItems: (hint: Hint) => boolean;
