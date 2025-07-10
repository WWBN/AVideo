import { Tour } from "../tour";
export type OverlayLayerProps = {
    exitOnOverlayClick: boolean;
    onExitTour: () => Promise<Tour>;
};
export declare const OverlayLayer: ({ exitOnOverlayClick, onExitTour, }: OverlayLayerProps) => HTMLDivElement;
