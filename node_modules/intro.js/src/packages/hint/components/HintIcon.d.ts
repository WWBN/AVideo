import { State } from "../../dom";
import { HintItem } from "../hintItem";
export type HintProps = {
    index: number;
    hintItem: HintItem;
    refreshesSignal: State<number>;
    onClick: (e: any) => void;
};
export declare const HintIcon: ({ index, hintItem, onClick, refreshesSignal, }: HintProps) => HTMLAnchorElement;
