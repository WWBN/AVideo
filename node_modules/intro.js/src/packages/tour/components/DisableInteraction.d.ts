import { State } from "../../dom";
import { TourStep } from "../steps";
export type HelperLayerProps = {
    currentStep: State<number | undefined>;
    steps: TourStep[];
    refreshes: State<number>;
    targetElement: HTMLElement;
    helperElementPadding: number;
};
export declare const DisableInteraction: ({ currentStep, steps, refreshes, targetElement, helperElementPadding, }: HelperLayerProps) => () => HTMLDivElement | null;
