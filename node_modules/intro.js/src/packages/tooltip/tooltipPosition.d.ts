import { Offset } from "../../util/getOffset";
export type TooltipPosition = "floating" | "top" | "bottom" | "left" | "right" | "top-right-aligned" | "top-left-aligned" | "top-middle-aligned" | "bottom-right-aligned" | "bottom-left-aligned" | "bottom-middle-aligned";
/**
 * Determines the position of the tooltip based on the position precedence and availability
 * of screen space.
 */
export declare function determineAutoPosition(positionPrecedence: TooltipPosition[], targetOffset: Offset, tooltipWidth: number, tooltipHeight: number, desiredTooltipPosition: TooltipPosition, windowSize: {
    width: number;
    height: number;
}): TooltipPosition;
