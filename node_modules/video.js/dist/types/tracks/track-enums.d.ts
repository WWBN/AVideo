/**
 * All possible `VideoTrackKind`s
 */
export type VideoTrackKind = any;
export namespace VideoTrackKind {
    let alternative: string;
    let captions: string;
    let main: string;
    let sign: string;
    let subtitles: string;
    let commentary: string;
}
/**
 * All possible `AudioTrackKind`s
 */
export type AudioTrackKind = any;
/**
 * All possible `AudioTrackKind`s
 *
 * @see https://html.spec.whatwg.org/multipage/embedded-content.html#dom-audiotrack-kind
 * @typedef AudioTrack~Kind
 * @enum
 */
export const AudioTrackKind: {
    alternative: string;
    descriptions: string;
    main: string;
    'main-desc': string;
    translation: string;
    commentary: string;
};
/**
 * All possible `TextTrackKind`s
 */
export type TextTrackKind = any;
export namespace TextTrackKind {
    let subtitles_1: string;
    export { subtitles_1 as subtitles };
    let captions_1: string;
    export { captions_1 as captions };
    export let descriptions: string;
    export let chapters: string;
    export let metadata: string;
}
/**
 * All possible `TextTrackMode`s
 */
export type TextTrackMode = any;
export namespace TextTrackMode {
    let disabled: string;
    let hidden: string;
    let showing: string;
}
/**
 * ~Kind
 */
export type VideoTrack = any;
/**
 * ~Kind
 */
export type AudioTrack = any;
/**
 * ~Kind
 */
export type TextTrack = any;
//# sourceMappingURL=track-enums.d.ts.map