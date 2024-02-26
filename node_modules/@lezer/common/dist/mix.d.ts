import { SyntaxNodeRef } from "./tree";
import { Input, Parser, ParseWrapper } from "./parse";
export interface NestedParse {
    parser: Parser;
    overlay?: readonly {
        from: number;
        to: number;
    }[] | ((node: SyntaxNodeRef) => {
        from: number;
        to: number;
    } | boolean);
}
export declare function parseMixed(nest: (node: SyntaxNodeRef, input: Input) => NestedParse | null): ParseWrapper;
