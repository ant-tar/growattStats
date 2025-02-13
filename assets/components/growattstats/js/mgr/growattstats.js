var growattStats = function (config) {
    config = config || {};
    growattStats.superclass.constructor.call(this, config);
};
Ext.extend(growattStats, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}
});
Ext.reg('growattstats', growattStats);

growattStats = new growattStats();