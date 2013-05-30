define(function() {
    return Backbone.Model.extend({
        url: function() {
            var url = Backbone.Model.prototype.url.call(this).split('.json').join('');
            return url + ".json";
        },
        save: function(attributes, options) {
            return Backbone.Model.prototype.save.call(this, attributes, _.extend({
                wait: true,
                isSave: true
            }, options));
        },
        destroy: function(options){
            Backbone.Model.prototype.destroy.call(this, _.extend({
                wait: true
            }, options))
        },
        toJSON: function(options) {
            options = options || {};

            var toJSON = Backbone.Model.prototype.toJSON;

            if (options.isSave) {
                var data = {};
                data[this.modelName] = _.pick(toJSON.apply(this, arguments), this.saveFields);

                return data;
            }

            return toJSON.apply(this, arguments);
        }
    })
});