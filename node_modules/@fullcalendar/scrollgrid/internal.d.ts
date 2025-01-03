import { CssDimValue } from '@fullcalendar/core';
import { BaseComponent, ScrollGridProps, ScrollGridSectionConfig, ScrollGridChunkConfig, ColProps } from '@fullcalendar/core/internal';
import { VNode } from '@fullcalendar/core/preact';

interface ScrollGridState {
    shrinkWidths: number[];
    forceYScrollbars: boolean;
    forceXScrollbars: boolean;
    scrollerClientWidths: {
        [index: string]: number;
    };
    scrollerClientHeights: {
        [index: string]: number;
    };
    sectionRowMaxHeights: number[][][];
}
interface ColGroupStat {
    hasShrinkCol: boolean;
    totalColWidth: number;
    totalColMinWidth: number;
    allowXScrolling: boolean;
    width?: CssDimValue;
    cols: ColProps[];
}
declare class ScrollGrid extends BaseComponent<ScrollGridProps, ScrollGridState> {
    private compileColGroupStats;
    private renderMicroColGroups;
    private clippedScrollerRefs;
    private scrollerElRefs;
    private chunkElRefs;
    private getStickyScrolling;
    private getScrollSyncersBySection;
    private getScrollSyncersByColumn;
    private scrollSyncersBySection;
    private scrollSyncersByColumn;
    private rowUnstableMap;
    private rowInnerMaxHeightMap;
    private anyRowHeightsChanged;
    private lastSizingDate;
    private recentSizingCnt;
    state: ScrollGridState;
    render(): VNode;
    renderSection(sectionConfig: ScrollGridSectionConfig, sectionIndex: number, colGroupStats: ColGroupStat[], microColGroupNodes: VNode[], sectionRowMaxHeights: number[][][], isHeader: boolean): VNode;
    renderChunk(sectionConfig: ScrollGridSectionConfig, sectionIndex: number, colGroupStat: ColGroupStat | undefined, microColGroupNode: VNode | undefined, chunkConfig: ScrollGridChunkConfig, chunkIndex: number, rowHeights: number[], isHeader: boolean): VNode;
    componentDidMount(): void;
    componentDidUpdate(prevProps: ScrollGridProps, prevState: ScrollGridState): void;
    componentWillUnmount(): void;
    handleSizing: (isForcedResize: boolean, sectionRowMaxHeightsChanged?: boolean) => void;
    allowSizing(): boolean;
    handleRowHeightChange: (rowEl: HTMLTableRowElement, isStable: boolean) => void;
    computeShrinkWidths(): number[];
    private computeSectionRowMaxHeights;
    computeScrollerDims(): {
        forceYScrollbars: boolean;
        forceXScrollbars: boolean;
        scrollerClientWidths: {
            [index: string]: number;
        };
        scrollerClientHeights: {
            [index: string]: number;
        };
    };
    updateStickyScrolling(): void;
    updateScrollSyncers(): void;
    destroyScrollSyncers(): void;
    getChunkConfigByIndex(index: number): ScrollGridChunkConfig;
    forceScrollLeft(col: number, scrollLeft: number): void;
    forceScrollTop(sectionI: number, scrollTop: number): void;
    _handleChunkEl(chunkEl: HTMLTableCellElement | null, key: string): void;
    _handleScrollerEl(scrollerEl: HTMLElement | null, key: string): void;
    getDims(): number[];
}

export { ScrollGrid };
