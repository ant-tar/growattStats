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
    border: 1px solid #e1e5ea;
    border-radius: 6px;
    overflow: hidden;
    background: #fff;
}

.js-growattstats .growattstats-card-head {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 92px;
    background: linear-gradient(90deg, #302b6b 0%, #2e5a90 100%);
}

.js-growattstats .growattstats-card-head img {
    width: 48px;
    height: 48px;
    display: block;
}

.js-growattstats .growattstats-card-body {
    padding: 14px 16px 16px;
    text-align: center;
}

.js-growattstats .growattstats-value {
    font-size: 1.35rem;
    font-weight: 700;
    line-height: 1.1;
    margin: 0 0 4px;
}

.js-growattstats .growattstats-label {
    font-size: 0.95rem;
    line-height: 1.35;
    color: #5f6468;
}

.js-growattstats .growattstats-label strong {
    color: #2e5a90;
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
        <div class="growattstats-card">
            <div class="growattstats-card-head">
                <img src="[[++assets_url]]components/growattstats/images/power.png" alt="">
            </div>
            <div class="growattstats-card-body">
                <div class="growattstats-value">[[+today_energy]] kWh</div>
                <div class="growattstats-label">[[%growattstats_today_energy]]</div>
            </div>
        </div>

        <div class="growattstats-card">
            <div class="growattstats-card-head">
                <img src="[[++assets_url]]components/growattstats/images/power.png" alt="">
            </div>
            <div class="growattstats-card-body">
                <div class="growattstats-value">[[+total_energy]] kWh</div>
                <div class="growattstats-label">[[%growattstats_total_energy]]</div>
            </div>
        </div>
    </div>

    <div class="growattstats-chart">
        <div class="growattstats-title">[[+plant_name]]</div>
        <div id="growattstats-container"></div>
        <p class="growattstats-note">
            [[%growattstats_chart_note]]
        </p>
    </div>
</div>
