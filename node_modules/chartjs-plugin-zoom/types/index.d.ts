import { Plugin, ChartType, Chart, Scale, UpdateMode, ScaleTypeRegistry, ChartTypeRegistry } from 'chart.js';
import { LimitOptions, ZoomPluginOptions } from './options';

type Point = { x: number, y: number };
type DistributiveArray<T> = [T] extends [unknown] ? Array<T> : never

export type PanAmount = number | Partial<Point>;
export type ScaleRange = { min: number, max: number };
export type ZoomAmount = number | Partial<Point> & { focalPoint?: Point };

declare module 'chart.js' {
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  interface PluginOptionsByType<TType extends ChartType> {
    zoom: ZoomPluginOptions;
  }

  enum UpdateModeEnum {
    zoom = 'zoom'
  }

  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  interface Chart<TType extends keyof ChartTypeRegistry = keyof ChartTypeRegistry, TData = DistributiveArray<ChartTypeRegistry[TType]['defaultDataPoint']>, TLabel = unknown> {
    pan(pan: PanAmount, scales?: Scale[], mode?: UpdateMode): void;
    zoom(zoom: ZoomAmount, mode?: UpdateMode): void;
    zoomRect(p0: Point, p1: Point, mode?: UpdateMode): void;
    zoomScale(id: string, range: ScaleRange, mode?: UpdateMode): void;
    resetZoom(mode?: UpdateMode): void;
    getZoomLevel(): number;
    getInitialScaleBounds(): Record<string, {min: number | undefined, max: number | undefined}>;
    getZoomedScaleBounds(): Record<string, ScaleRange | undefined>;
    isZoomedOrPanned(): boolean;
    isZoomingOrPanning(): boolean;
  }
}

export type ZoomFunction = (scale: Scale, zoom: number, center: Point, limits: LimitOptions) => boolean;
export type ZoomRectFunction = (scale: Scale, from: number, to: number, limits: LimitOptions) => boolean;
export type PanFunction = (scale: Scale, delta: number, limits: LimitOptions) => boolean;

type ScaleFunctions<T> = {
  [scaleType in keyof ScaleTypeRegistry]?: T | undefined;
} & {
  default: T;
};

declare const Zoom: Plugin & {
  zoomFunctions: ScaleFunctions<ZoomFunction>;
  zoomRectFunctions: ScaleFunctions<ZoomRectFunction>;
  panFunctions: ScaleFunctions<PanFunction>;
};

export default Zoom;

export function pan(chart: Chart, amount: PanAmount, scales?: Scale[], mode?: UpdateMode): void;
export function zoom(chart: Chart, amount: ZoomAmount, mode?: UpdateMode): void;
export function zoomRect(chart: Chart, p0: Point, p1: Point, mode?: UpdateMode): void;
export function zoomScale(chart: Chart, scaleId: string, range: ScaleRange, mode?: UpdateMode): void;
export function resetZoom(chart: Chart, mode?: UpdateMode): void;
export function getZoomLevel(chart: Chart): number;
export function getInitialScaleBounds(chart: Chart): Record<string, {min: number | undefined, max: number | undefined}>;
export function getZoomedScaleBounds(chart: Chart): Record<string, ScaleRange | undefined>;
export function isZoomedOrPanned(chart: Chart): boolean;
export function isZoomingOrPanning(chart: Chart): boolean;
