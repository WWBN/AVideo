import { State } from "../../dom";
import { HintTooltipProps } from "./HintTooltip";
export type ReferenceLayerProps = HintTooltipProps & {
    activeHintSignal: State<number | undefined>;
    targetElement: HTMLElement;
    helperElementPadding: number;
};
export declare const ReferenceLayer: ({ activeHintSignal, targetElement, helperElementPadding, ...props }: ReferenceLayerProps) => () => HTMLDivElement | null;
