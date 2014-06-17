var View = View || {};

View.PageContent_PageWithHeader = Backbone.View.extend({
    initialize: function(options) {
        this.original_model = options.page;
        this.model = options.page.clone();
        this.language = options.language;
        this.route = options.route;
        this.original_model.url = Routing.generate('concerto_cms_core_content_rest', {path: this.model.id});

        this.render();
        this.listenToOnce(this.model, "change", this.onChange);

    },

    bindings: {
        '#content_title': 'title',
        '#content_content': 'content',
        '#content_header': 'header'
    },

    render: function() {
        var editor,
            that = this;
        this.el.innerHTML = window.JST["PageContent_PageWithHeader.html.twig"].render({
            page: this.model,
            route: this.route,
            language: this.language
        });
        editor = this.$("#content_content").ckeditor({
            customConfig: ''
        }).data("ckeditorInstance");
        editor.on( 'change', function(e) {
            that.$("#content_content").trigger("change");
        });

        editor = this.$("#content_header").ckeditor({
            customConfig: ''
        }).data("ckeditorInstance");
        editor.on( 'change', function(e) {
            that.$("#content_header").trigger("change");
        });

        this.stickit();
    },
    events: {
        "click button.save": "save"
    },
    onChange: function() {
        this.$("button.save").removeAttr("disabled").removeClass("disabled");
    },
    save: function() {
        this.$("button.save").attr("disabled", "disabled").addClass("disabled");
        this.original_model.set(this.model.attributes).save();
        this.listenToOnce(this.model, "change", this.onChange);
    }

});