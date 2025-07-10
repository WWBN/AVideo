import { State } from "../../dom";
import { TourStep } from "../steps";
export type HelperLayerProps = {
    currentStep: State<number | undefined>;
    steps: TourStep[];
    refreshes: State<number>;
    targetElement: HTMLElement;
    tourHighlightClass: string;
    overlayOpacity: number;
    helperLayerPadding: number;
};
export declare const HelperLayer: ({ currentStep, steps, refreshes, targetElement, tourHighlightClass, overlayOpacity, helperLayerPadding, }: HelperLayerProps) => HTMLDivElement;
