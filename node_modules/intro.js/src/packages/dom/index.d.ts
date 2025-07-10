/**
 * A TypeScript and modified version of the VanJS project.
 * Credits: https://github.com/vanjs-org/van & https://github.com/ge3224/van-ts
 */
/**
 * A type representing primitive JavaScript types.
 */
export type Primitive = string | number | boolean | bigint;
/**
 * A type representing a property value which can be a primitive, a function,
 * or null.
 */
export type PropValue = Primitive | (<T>(e: T) => void) | null;
/**
 * A type representing valid child DOM values.
 */
export type ValidChildDomValue = Primitive | Node | null | undefined;
/**
 * A type representing functions that generate DOM values.
 */
export type BindingFunc = ((dom?: Node) => ValidChildDomValue) | ((dom?: Element) => Element);
/**
 * A type representing various possible child DOM values.
 */
export type ChildDom = ValidChildDomValue | StateView<Primitive | null | undefined> | BindingFunc | readonly ChildDom[];
type Binding = {
    f: BindingFunc;
    _dom: HTMLElement | null | undefined;
};
type Listener<T> = {
    f: BindingFunc;
    s: State<T>;
    _dom?: HTMLElement | null | undefined;
};
/**
 * Interface representing a state object with various properties and bindings.
 */
export interface State<T> {
    val: T | undefined;
    readonly oldVal: T | undefined;
    rawVal: T | undefined;
    _oldVal: T | undefined;
    _bindings: Array<Binding>;
    _listeners: Array<Listener<T>>;
}
/**
 * A type representing a read-only view of a `State` object.
 */
export type StateView<T> = Readonly<State<T>>;
/**
 * A type representing a value that can be either a `State` object or a direct
 * value of type `T`.
 */
export type Val<T> = State<T> | T;
/**
 * A type representing a property value, a state view of a property value, or a
 * function returning a property value.
 */
export type PropValueOrDerived = PropValue | StateView<PropValue> | (() => PropValue);
/**
 * A type representing partial props with known keys for a specific
 * element type.
 */
export type Props = Record<string, PropValueOrDerived> & {
    class?: PropValueOrDerived;
};
export type PropsWithKnownKeys<ElementType> = Partial<{
    [K in keyof ElementType]: PropValueOrDerived;
}>;
/**
 * Represents a function type that constructs a tagged result using provided
 * properties and children.
 */
export type TagFunc<Result> = (first?: (Props & PropsWithKnownKeys<Result>) | ChildDom, ...rest: readonly ChildDom[]) => Result;
/**
 * Represents a function type for creating a namespace-specific collection of
 * tag functions.
 *
 * @param {string} namespaceURI
 * - The URI of the namespace for which the tag functions are being created.
 *
 * @returns {Readonly<Record<string, TagFunc<Element>>>}
 * - A readonly record of string keys to TagFunc<Element> functions,
 *   representing the collection of tag functions within the specified
 *   namespace.
 */
export type NamespaceFunction = (namespaceURI: string) => Readonly<Record<string, TagFunc<Element>>>;
/**
 * Represents a type for a collection of tag functions.
 *
 * This type includes:
 * - A readonly record of string keys to TagFunc<Element> functions, enabling
 *   the creation of generic HTML elements.
 * - Specific tag functions for each HTML element type as defined in
 *   HTMLElementTagNameMap, with the return type corresponding to the specific
 *   type of the HTML element (e.g., HTMLDivElement for 'div',
 *   HTMLAnchorElement for 'a').
 *
 * Usage of this type allows for type-safe creation of HTML elements with
 * specific properties and child elements.
 */
export type Tags = Readonly<Record<string, TagFunc<Element>>> & {
    [K in keyof HTMLElementTagNameMap]: TagFunc<HTMLElementTagNameMap[K]>;
};
declare const _default: {
    add: (dom: Element, ...children: readonly ChildDom[]) => Element;
    tags: Readonly<Record<string, TagFunc<Element>>> & {
        a: TagFunc<HTMLAnchorElement>;
        abbr: TagFunc<HTMLElement>;
        address: TagFunc<HTMLElement>;
        area: TagFunc<HTMLAreaElement>;
        article: TagFunc<HTMLElement>;
        aside: TagFunc<HTMLElement>;
        audio: TagFunc<HTMLAudioElement>;
        b: TagFunc<HTMLElement>;
        base: TagFunc<HTMLBaseElement>;
        bdi: TagFunc<HTMLElement>;
        bdo: TagFunc<HTMLElement>;
        blockquote: TagFunc<HTMLQuoteElement>;
        body: TagFunc<HTMLBodyElement>;
        br: TagFunc<HTMLBRElement>;
        button: TagFunc<HTMLButtonElement>;
        canvas: TagFunc<HTMLCanvasElement>;
        caption: TagFunc<HTMLTableCaptionElement>;
        cite: TagFunc<HTMLElement>;
        code: TagFunc<HTMLElement>;
        col: TagFunc<HTMLTableColElement>;
        colgroup: TagFunc<HTMLTableColElement>;
        data: TagFunc<HTMLDataElement>;
        datalist: TagFunc<HTMLDataListElement>;
        dd: TagFunc<HTMLElement>;
        del: TagFunc<HTMLModElement>;
        details: TagFunc<HTMLDetailsElement>;
        dfn: TagFunc<HTMLElement>;
        dialog: TagFunc<HTMLDialogElement>;
        div: TagFunc<HTMLDivElement>;
        dl: TagFunc<HTMLDListElement>;
        dt: TagFunc<HTMLElement>;
        em: TagFunc<HTMLElement>;
        embed: TagFunc<HTMLEmbedElement>;
        fieldset: TagFunc<HTMLFieldSetElement>;
        figcaption: TagFunc<HTMLElement>;
        figure: TagFunc<HTMLElement>;
        footer: TagFunc<HTMLElement>;
        form: TagFunc<HTMLFormElement>;
        h1: TagFunc<HTMLHeadingElement>;
        h2: TagFunc<HTMLHeadingElement>;
        h3: TagFunc<HTMLHeadingElement>;
        h4: TagFunc<HTMLHeadingElement>;
        h5: TagFunc<HTMLHeadingElement>;
        h6: TagFunc<HTMLHeadingElement>;
        head: TagFunc<HTMLHeadElement>;
        header: TagFunc<HTMLElement>;
        hgroup: TagFunc<HTMLElement>;
        hr: TagFunc<HTMLHRElement>;
        html: TagFunc<HTMLHtmlElement>;
        i: TagFunc<HTMLElement>;
        iframe: TagFunc<HTMLIFrameElement>;
        img: TagFunc<HTMLImageElement>;
        input: TagFunc<HTMLInputElement>;
        ins: TagFunc<HTMLModElement>;
        kbd: TagFunc<HTMLElement>;
        label: TagFunc<HTMLLabelElement>;
        legend: TagFunc<HTMLLegendElement>;
        li: TagFunc<HTMLLIElement>;
        link: TagFunc<HTMLLinkElement>;
        main: TagFunc<HTMLElement>;
        map: TagFunc<HTMLMapElement>;
        mark: TagFunc<HTMLElement>;
        menu: TagFunc<HTMLMenuElement>;
        meta: TagFunc<HTMLMetaElement>;
        meter: TagFunc<HTMLMeterElement>;
        nav: TagFunc<HTMLElement>;
        noscript: TagFunc<HTMLElement>;
        object: TagFunc<HTMLObjectElement>;
        ol: TagFunc<HTMLOListElement>;
        optgroup: TagFunc<HTMLOptGroupElement>;
        option: TagFunc<HTMLOptionElement>;
        output: TagFunc<HTMLOutputElement>;
        p: TagFunc<HTMLParagraphElement>;
        picture: TagFunc<HTMLPictureElement>;
        pre: TagFunc<HTMLPreElement>;
        progress: TagFunc<HTMLProgressElement>;
        q: TagFunc<HTMLQuoteElement>;
        rp: TagFunc<HTMLElement>;
        rt: TagFunc<HTMLElement>;
        ruby: TagFunc<HTMLElement>;
        s: TagFunc<HTMLElement>;
        samp: TagFunc<HTMLElement>;
        script: TagFunc<HTMLScriptElement>;
        search: TagFunc<HTMLElement>;
        section: TagFunc<HTMLElement>;
        select: TagFunc<HTMLSelectElement>;
        slot: TagFunc<HTMLSlotElement>;
        small: TagFunc<HTMLElement>;
        source: TagFunc<HTMLSourceElement>;
        span: TagFunc<HTMLSpanElement>;
        strong: TagFunc<HTMLElement>;
        style: TagFunc<HTMLStyleElement>;
        sub: TagFunc<HTMLElement>;
        summary: TagFunc<HTMLElement>;
        sup: TagFunc<HTMLElement>;
        table: TagFunc<HTMLTableElement>;
        tbody: TagFunc<HTMLTableSectionElement>;
        td: TagFunc<HTMLTableCellElement>;
        template: TagFunc<HTMLTemplateElement>;
        textarea: TagFunc<HTMLTextAreaElement>;
        tfoot: TagFunc<HTMLTableSectionElement>;
        th: TagFunc<HTMLTableCellElement>;
        thead: TagFunc<HTMLTableSectionElement>;
        time: TagFunc<HTMLTimeElement>;
        title: TagFunc<HTMLTitleElement>;
        tr: TagFunc<HTMLTableRowElement>;
        track: TagFunc<HTMLTrackElement>;
        u: TagFunc<HTMLElement>;
        ul: TagFunc<HTMLUListElement>;
        var: TagFunc<HTMLElement>;
        video: TagFunc<HTMLVideoElement>;
        wbr: TagFunc<HTMLElement>;
    } & NamespaceFunction;
    state: <T>(initVal?: T | undefined) => State<T>;
    derive: <T_1>(f: () => T_1, s?: State<T_1> | undefined, dom?: ChildDom) => State<T_1>;
    hydrate: <T_2 extends Node>(dom: T_2, updateFn: (dom: T_2) => T_2 | null | undefined) => void | T_2;
};
export default _default;
