import { TourStep } from "./steps";
import { Tour } from "./tour";
export declare const appendMockSteps: (targetElement?: HTMLElement) => HTMLElement[];
export declare const getMockPartialSteps: () => Partial<TourStep>[];
export declare const getMockSteps: () => TourStep[];
export declare const getMockTour: (targetElement?: HTMLElement) => Tour;
