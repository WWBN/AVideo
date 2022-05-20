/**
Generate a cryptographically strong pseudo-random data

@param size - Size of the string

@returns A cryptographically strong pseudo-random data
*/

export function randomString(size: number): string;


/**
Generate a cryptographically strong pseudo-random alphanumeric data

@param size - Size of the string

@returns A cryptographically strong pseudo-random alphanumeric data
*/

export function randomAlphanumString(size: number): string;


/**
Return a random string of digits

@param size - Size of the digits

@returns A random string of digits
*/

export function randomDigits(size: number): string;


/**
Generate a buffer of random bits

@param bits - Number of bits

@returns A buffer of random bits
*/

export function randomBits(bits: number): Buffer;


/**
Generate a buffer of random bits

@param a - Data to compare
@param b - Data to compare

@returns A boolean comparing a and b
*/

export function fixedTimeComparison(a: string | Array<any>, b: string | Array<any>): boolean;
