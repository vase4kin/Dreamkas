define(function(require, exports, module) {
    //requirements
    var Page = require('kit/page/page');

    return Page.extend({
        template: require('ejs!./template.ejs')
    });
});