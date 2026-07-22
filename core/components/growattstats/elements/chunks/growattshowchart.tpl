<style>
.js-growattstats .widget-left img {
    max-width: 48px;
}

#growattstats-container {
    min-height: 400px;
}
</style>

<div class="js-growattstats">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <div class="panel panel-blue panel-widget">
                <div class="row no-padding">
                    <div class="col-sm-3 col-lg-5 widget-left" style="background:linear-gradient(90deg,#302b6b 0%,#2e5a90 100%)">
                        <img src="[[++assets_url]]components/growattstats/images/power.png" alt="">
                    </div>
                    <div class="col-sm-9 col-lg-6 widget-right">
                        <div class="large">[[+today_energy]] kWh</div>
                        <div class="text-muted">Generation <span class="blue-accent">Today</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-md-6">
            <div class="panel panel-orange panel-widget">
                <div class="row no-padding">
                    <div class="col-sm-3 col-lg-5 widget-left" style="background:linear-gradient(90deg,#302b6b 0%,#2e5a90 100%)">
                        <img src="[[++assets_url]]components/growattstats/images/power.png" alt="">
                    </div>
                    <div class="col-sm-9 col-lg-6 widget-right">
                        <div class="large">[[+total_energy]] kWh</div>
                        <div class="text-muted"><span class="blue-accent">Total</span> Generation</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">[[+plant_name]]</div>
                <div class="panel-body">
                    <div id="growattstats-container"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <p class="text-muted" style="margin: 0 0 24px;">
                The graph shows daily electricity production from the solar installation in Gamst.
                Use the range selector or drag the navigator to inspect the history.
            </p>
        </div>
    </div>
</div>
