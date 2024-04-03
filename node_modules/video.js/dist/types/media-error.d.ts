export default MediaError;
/**
 * An object containing an error type, as well as other information regarding the error.
 */
export type ErrorMetadata = {
    [key: string]: any;
    errorType: string;
};
/**
 * A Custom `MediaError` class which mimics the standard HTML5 `MediaError` class.
 *
 * @param {number|string|Object|MediaError} value
 *        This can be of multiple types:
 *        - number: should be a standard error code
 *        - string: an error message (the code will be 0)
 *        - Object: arbitrary properties
 *        - `MediaError` (native): used to populate a video.js `MediaError` object
 *        - `MediaError` (video.js): will return itself if it's already a
 *          video.js `MediaError` object.
 *
 * @see [MediaError Spec]{@link https://dev.w3.org/html5/spec-author-view/video.html#mediaerror}
 * @see [Encrypted MediaError Spec]{@link https://www.w3.org/TR/2013/WD-encrypted-media-20130510/#error-codes}
 *
 * @class MediaError
 */
declare function MediaError(value: number | string | any | MediaError): MediaError;
declare class MediaError {
    /**
     * A Custom `MediaError` class which mimics the standard HTML5 `MediaError` class.
     *
     * @param {number|string|Object|MediaError} value
     *        This can be of multiple types:
     *        - number: should be a standard error code
     *        - string: an error message (the code will be 0)
     *        - Object: arbitrary properties
     *        - `MediaError` (native): used to populate a video.js `MediaError` object
     *        - `MediaError` (video.js): will return itself if it's already a
     *          video.js `MediaError` object.
     *
     * @see [MediaError Spec]{@link https://dev.w3.org/html5/spec-author-view/video.html#mediaerror}
     * @see [Encrypted MediaError Spec]{@link https://www.w3.org/TR/2013/WD-encrypted-media-20130510/#error-codes}
     *
     * @class MediaError
     */
    constructor(value: number | string | any | MediaError);
    code: number;
    message: string;
    /**
     * An optional status code that can be set by plugins to allow even more detail about
     * the error. For example a plugin might provide a specific HTTP status code and an
     * error message for that code. Then when the plugin gets that error this class will
     * know how to display an error message for it. This allows a custom message to show
     * up on the `Player` error overlay.
     *
     * @type {Array}
     */
    status: any[];
    /**
     * An object containing an error type, as well as other information regarding the error.
     *
     * @typedef {{errorType: string, [key: string]: any}} ErrorMetadata
     */
    /**
     * An optional object to give more detail about the error. This can be used to give
     * a higher level of specificity to an error versus the more generic MediaError codes.
     * `metadata` expects an `errorType` string that should align with the values from videojs.Error.
     *
     * @type {ErrorMetadata}
     */
    metadata: ErrorMetadata;
    /**
     * W3C error code for any custom error.
     *
     * @member MediaError.MEDIA_ERR_CUSTOM
     * @constant {number}
     * @default 0
     */
    MEDIA_ERR_CUSTOM: number;
    /**
     * W3C error code for media error aborted.
     *
     * @member MediaError.MEDIA_ERR_ABORTED
     * @constant {number}
     * @default 1
     */
    MEDIA_ERR_ABORTED: number;
    /**
     * W3C error code for any network error.
     *
     * @member MediaError.MEDIA_ERR_NETWORK
     * @constant {number}
     * @default 2
     */
    MEDIA_ERR_NETWORK: number;
    /**
     * W3C error code for any decoding error.
     *
     * @member MediaError.MEDIA_ERR_DECODE
     * @constant {number}
     * @default 3
     */
    MEDIA_ERR_DECODE: number;
    /**
     * W3C error code for any time that a source is not supported.
     *
     * @member MediaError.MEDIA_ERR_SRC_NOT_SUPPORTED
     * @constant {number}
     * @default 4
     */
    MEDIA_ERR_SRC_NOT_SUPPORTED: number;
    /**
     * W3C error code for any time that a source is encrypted.
     *
     * @member MediaError.MEDIA_ERR_ENCRYPTED
     * @constant {number}
     * @default 5
     */
    MEDIA_ERR_ENCRYPTED: number;
}
declare namespace MediaError {
    /**
     * *
     */
    type errorTypes = any[];
    const errorTypes: string[];
    const defaultMessages: any[];
    const MEDIA_ERR_CUSTOM: number;
    const MEDIA_ERR_ABORTED: number;
    const MEDIA_ERR_NETWORK: number;
    const MEDIA_ERR_DECODE: number;
    const MEDIA_ERR_SRC_NOT_SUPPORTED: number;
    const MEDIA_ERR_ENCRYPTED: number;
}
//# sourceMappingURL=media-error.d.ts.map