export = createError;
/**
 *
 * @param {any} err - An Error
 * @param {string|Extensions} code - A string code or props to set on the error
 * @param {Extensions} [props] - Props to set on the error
 * @returns {Error & Extensions}
 */
declare function createError(err: any, code: string | Extensions, props?: Extensions | undefined): Error & Extensions;
declare namespace createError {
    export { Extensions, Err };
}
type Extensions = {
    [key: string]: any;
};
type Err = Error;
