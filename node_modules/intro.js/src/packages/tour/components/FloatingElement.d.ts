import { State } from "../../dom";
export type FloatingElementProps = {
    currentStep: State<number | undefined>;
};
export declare const FloatingElement: ({ currentStep }: FloatingElementProps) => HTMLDivElement;
