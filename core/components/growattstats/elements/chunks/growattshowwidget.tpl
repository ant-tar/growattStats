<style>
.js-growattstats-widget,
.js-growattstats-widget * {
    box-sizing: border-box;
}

.js-growattstats-widget {
    width: 100%;
}

.js-growattstats-widget .growattstats-widget-top {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 12px;
}

.js-growattstats-widget .growattstats-widget-tile {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
    border: 1px solid #e7d8bc;
    border-left: 3px solid #d99a28;
    border-radius: 6px;
    background: #fffbf3;
    padding: 10px 12px;
}

.js-growattstats-widget .growattstats-widget-tile--total {
    border-color: #cedced;
    border-left-color: #4078b8;
    background: #f5f9ff;
}

.js-growattstats-widget .growattstats-widget-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 36px;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    color: #98600c;
    background: #fff0cc;
}

.js-growattstats-widget .growattstats-widget-tile--total .growattstats-widget-icon {
    color: #2c609c;
    background: #e3edfc;
}

.js-growattstats-widget .growattstats-widget-icon svg {
    display: block;
    width: 24px;
    height: 24px;
}

.js-growattstats-widget .growattstats-widget-reading {
    min-width: 0;
    overflow-wrap: anywhere;
}

.js-growattstats-widget .growattstats-widget-value {
    color: #1b2e45;
    font-variant-numeric: tabular-nums;
    font-size: 1.1rem;
    font-weight: 700;
    line-height: 1.15;
    margin: 0 0 4px;
}

.js-growattstats-widget .growattstats-widget-label {
    font-size: 0.9rem;
    color: #5f6468;
    line-height: 1.3;
}

.js-growattstats-widget .growattstats-widget-chart {
    border: 1px solid #d9dee5;
    border-radius: 6px;
    background: #fff;
    padding: 10px;
}

.js-growattstats-widget .growattstats-widget-title {
    margin: 0 0 10px;
    font-size: 0.95rem;
    font-weight: 600;
    color: #2c3e50;
}

#growattstats-container {
    width: 100%;
    min-height: 260px;
}

@media (max-width: 767px) {
    .js-growattstats-widget .growattstats-widget-top {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="js-growattstats-widget">
    <div class="growattstats-widget-top">
        <div class="growattstats-widget-tile growattstats-widget-tile--today">
            <span class="growattstats-widget-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <circle cx="12" cy="12" r="4"></circle>
                        <path d="M12 2v2m0 16v2M2 12h2m16 0h2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"></path>
                    </svg>
            </span>
            <div class="growattstats-widget-reading">
                <div class="growattstats-widget-value">[[+today_energy]] [[%growattstats_unit:htmlent]]</div>
                <div class="growattstats-widget-label">[[%growattstats_today_energy:htmlent]]</div>
            </div>
        </div>
        <div class="growattstats-widget-tile growattstats-widget-tile--total">
            <span class="growattstats-widget-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <path d="M4 20v-4m5 4v-7m5 7V10m5 10V7M3 11l6-5 5 1 6-5m-5 0h5v5"></path>
                    </svg>
            </span>
            <div class="growattstats-widget-reading">
                <div class="growattstats-widget-value">[[+total_energy]] [[%growattstats_unit:htmlent]]</div>
                <div class="growattstats-widget-label">[[%growattstats_total_energy:htmlent]]</div>
            </div>
        </div>
    </div>

    <div class="growattstats-widget-chart">
        <div class="growattstats-widget-title">[[+plant_name:htmlent]]</div>
        <div id="growattstats-container"></div>
    </div>
</div>
