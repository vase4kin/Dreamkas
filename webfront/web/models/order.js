define(function(require, exports, module) {
    //requirements
    var Model = require('kit/core/model'),
        currentUserModel = require('models/currentUser');

    return Model.extend({
        __name__: module.id,
        urlRoot: function() {
            return LH.baseApiUrl + '/stores/' + currentUserModel.stores.at(0).id + '/orders'
        },
        name: null,
        defaults: {
            products: []
        },
        saveData: [
            'supplier',
            'products'
        ]
    });
});