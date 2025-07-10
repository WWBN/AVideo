import { TourTooltipProps } from "./TourTooltip";
export type ReferenceLayerProps = TourTooltipProps & {
    targetElement: HTMLElement;
    helperElementPadding: number;
};
export declare const ReferenceLayer: ({ targetElement, helperElementPadding, ...props }: ReferenceLayerProps) => HTMLDivElement;
