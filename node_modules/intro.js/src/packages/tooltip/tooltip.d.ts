import { ChildDom, State } from "../dom";
import { TooltipPosition } from "./tooltipPosition";
export declare const TooltipArrow: (props: {
    tooltipPosition: State<TooltipPosition>;
    tooltipBottomOverflow: State<boolean>;
}) => HTMLDivElement;
export type TooltipProps = {
    position: TooltipPosition;
    element: HTMLElement;
    refreshes: State<number>;
    hintMode: boolean;
    showStepNumbers: boolean;
    transitionDuration?: number;
    autoPosition: boolean;
    positionPrecedence: TooltipPosition[];
    onClick?: (e: any) => void;
    className?: string;
};
export declare const Tooltip: ({ position: initialPosition, element, refreshes, hintMode, showStepNumbers, transitionDuration, positionPrecedence, className, autoPosition, onClick, }: TooltipProps, children?: ChildDom[]) => HTMLDivElement;
