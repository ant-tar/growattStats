<style>
.js-growattstats,
.js-growattstats * {
    box-sizing: border-box;
}

.js-growattstats {
    width: 100%;
}

.js-growattstats .growattstats-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 16px;
}

.js-growattstats .growattstats-card {
    min-width: 0;
    padding: 18px;
    border: 1px solid #e7d8bc;
    border-top: 3px solid #d99a28;
    border-radius: 10px;
    background: #fffbf3;
}

.js-growattstats .growattstats-card--total {
    border-color: #cedced;
    border-top-color: #4078b8;
    background: #f5f9ff;
}

.js-growattstats .growattstats-card-head {
    display: flex;
    align-items: center;
    gap: 12px;
}

.js-growattstats .growattstats-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 44px;
    width: 44px;
    height: 44px;
    border-radius: 12px;
    color: #98600c;
    background: #fff0cc;
}

.js-growattstats .growattstats-card--total .growattstats-icon {
    color: #2c609c;
    background: #e3edfc;
}

.js-growattstats .growattstats-icon svg {
    display: block;
    width: 26px;
    height: 26px;
}

.js-growattstats .growattstats-card-body {
    padding-top: 18px;
}

.js-growattstats .growattstats-value {
    margin: 0;
    color: #1b2e45;
    font-size: clamp(1.15rem, 2vw, 1.5rem);
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    line-height: 1.3;
    overflow-wrap: anywhere;
}

.js-growattstats .growattstats-unit {
    color: #5b6879;
    font-size: 0.8rem;
    font-weight: 400;
    white-space: nowrap;
}

.js-growattstats .growattstats-label {
    color: #5b6879;
    font-size: 0.85rem;
    line-height: 1.4;
}

.js-growattstats .growattstats-label strong {
    display: block;
    color: #795011;
    font-size: 1rem;
    font-weight: 600;
}

.js-growattstats .growattstats-card--total .growattstats-label strong {
    color: #2c609c;
}

.js-growattstats .growattstats-chart {
    border: 1px solid #e1e5ea;
    border-radius: 6px;
    background: #fff;
    padding: 12px;
}

.js-growattstats .growattstats-title {
    margin: 0 0 12px;
    font-size: 1rem;
    font-weight: 600;
    color: #2c3e50;
}

.js-growattstats .growattstats-note {
    margin: 12px 0 0;
    color: #6c757d;
    font-size: 0.95rem;
    line-height: 1.5;
}

#growattstats-container {
    width: 100%;
    min-height: 400px;
}

@media (max-width: 767px) {
    .js-growattstats .growattstats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="js-growattstats">
    <div class="growattstats-grid">
        <div class="growattstats-card growattstats-card--today">
            <div class="growattstats-card-head">
                <span class="growattstats-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <circle cx="12" cy="12" r="4"></circle>
                        <path d="M12 2v2m0 16v2M2 12h2m16 0h2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"></path>
                    </svg>
                </span>
                <div class="growattstats-label">Generation<strong>Today</strong></div>
            </div>
            <div class="growattstats-card-body">
                <div class="growattstats-value">[[+today_energy]] <span class="growattstats-unit">kWh</span></div>
            </div>
        </div>

        <div class="growattstats-card growattstats-card--total">
            <div class="growattstats-card-head">
                <span class="growattstats-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <path d="M4 20v-4m5 4v-7m5 7V10m5 10V7M3 11l6-5 5 1 6-5m-5 0h5v5"></path>
                    </svg>
                </span>
                <div class="growattstats-label">Generation<strong>All time</strong></div>
            </div>
            <div class="growattstats-card-body">
                <div class="growattstats-value">[[+total_energy]] <span class="growattstats-unit">kWh</span></div>
            </div>
        </div>
    </div>

    <div class="growattstats-chart">
        <div class="growattstats-title">[[+plant_name:htmlent]]</div>
        <div id="growattstats-container"></div>
        <p class="growattstats-note">
            The graph shows daily electricity production from the solar installation at the configured plant.
            Use the range selector or drag the navigator to inspect the history.
        </p>
    </div>
</div>
