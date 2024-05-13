export declare function setCookie(name: string, value: string, days?: number): string;
export declare function getAllCookies(): {
    [name: string]: string;
};
export declare function getCookie(name: string): string;
export declare function deleteCookie(name: string): void;
