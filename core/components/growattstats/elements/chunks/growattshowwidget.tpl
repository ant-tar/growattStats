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
    border: 1px solid #d9dee5;
    border-radius: 6px;
    background: #fff;
    padding: 10px 12px;
}

.js-growattstats-widget .growattstats-widget-value {
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
        <div class="growattstats-widget-tile">
            <div class="growattstats-widget-value">[[+today_energy]] kWh</div>
            <div class="growattstats-widget-label">[[%growattstats_today_energy]]</div>
        </div>
        <div class="growattstats-widget-tile">
            <div class="growattstats-widget-value">[[+total_energy]] kWh</div>
            <div class="growattstats-widget-label">[[%growattstats_total_energy]]</div>
        </div>
    </div>

    <div class="growattstats-widget-chart">
        <div class="growattstats-widget-title">[[+plant_name]]</div>
        <div id="growattstats-container"></div>
    </div>
</div>
