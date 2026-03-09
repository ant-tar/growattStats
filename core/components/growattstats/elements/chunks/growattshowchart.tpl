<style>
.js-growattstats .widget-left img { max-width: 48px; }
g.highcharts-label.highcharts-range-label { display: block !important; }
g.highcharts-label.highcharts-range-label > text { content: '/' !important; }
</style>

<div class="js-growattstats">
    <div class="row">

        <div class="col-xs-12 col-md-3">
            <div class="panel panel-blue panel-widget">
                <div class="row no-padding">
                    <div class="col-sm-3 col-lg-5 widget-left"
                         style="background:linear-gradient(90deg,#302b6b 0%,#2e5a90 100%)">
                        <img src="[[++assets_url]]components/growattstats/images/power.png" alt="">
                    </div>
                    <div class="col-sm-9 col-lg-6 widget-right">
                        <div class="large">[[+today_energy]] kWh</div>
                        <div class="text-muted">Generation <span class="blue-accent">Today</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-md-3">
            <div class="panel panel-orange panel-widget">
                <div class="row no-padding">
                    <div class="col-sm-3 col-lg-5 widget-left"
                         style="background:linear-gradient(90deg,#302b6b 0%,#2e5a90 100%)">
                        <img src="[[++assets_url]]components/growattstats/images/power.png" alt="">
                    </div>
                    <div class="col-sm-9 col-lg-6 widget-right">
                        <div class="large">[[+total_energy]] kWh</div>
                        <div class="text-muted"><span class="blue-accent">Total</span> Generation</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-md-3">
            <div class="panel panel-teal panel-widget">
                <div class="row no-padding">
                    <div class="col-sm-3 col-lg-5 widget-left"
                         style="background:linear-gradient(90deg,#302b6b 0%,#2e5a90 100%)">
                        <img src="[[++assets_url]]components/growattstats/images/revenue.png" alt="">
                    </div>
                    <div class="col-sm-9 col-lg-6 widget-right">
                        <div class="large">[[+today_revenue]] DKK</div>
                        <div class="text-muted">Revenue <span class="blue-accent">Today</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-md-3">
            <div class="panel panel-teal panel-widget">
                <div class="row no-padding">
                    <div class="col-sm-3 col-lg-5 widget-left"
                         style="background:linear-gradient(90deg,#302b6b 0%,#2e5a90 100%)">
                        <img src="[[++assets_url]]components/growattstats/images/revenue.png" alt="">
                    </div>
                    <div class="col-sm-9 col-lg-6 widget-right">
                        <div class="large">[[+total_revenue]] DKK</div>
                        <div class="text-muted"><span class="blue-accent">Total</span> Revenue</div>
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
                    <div id="growattstats-container" style="height:400px;min-width:310px"></div>
                </div>
            </div>
        </div>
    </div>
</div>
