import { TooltipPosition } from "../../packages/tooltip";
import { HintItem, HintPosition } from "./hintItem";
export interface HintOptions {
    hints: Partial<HintItem>[];
    isActive: boolean;
    tooltipPosition: string;
    tooltipClass: string;
    hintPosition: HintPosition;
    hintButtonLabel: string;
    hintShowButton: boolean;
    hintAutoRefreshInterval: number;
    hintAnimation: boolean;
    buttonClass: string;
    helperElementPadding: number;
    autoPosition: boolean;
    positionPrecedence: TooltipPosition[];
    tooltipRenderAsHtml?: boolean;
}
export declare function getDefaultHintOptions(): HintOptions;
