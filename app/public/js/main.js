require.config({
    paths: {
        'jquery': '//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min',
        'jquery-ui': '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min',
        //'bootstrap': '//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min',
        'underscore': '//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.6.0/underscore-min',
        'mmenu': '../components/jQuery.mmenu/src/js/jquery.mmenu.min.all',
        'modernizr': '../components/modernizr/modernizr',
        'prism': '../components/prism/prism',
        'unveil': '../components/jquery-unveil/jquery.unveil.min',
        'forms': '../libs/Form/javascript/zebra_form.src'
    },
    shim: {
        'jquery': {
            exports: '$'
        },
        'jquery-ui': {
            deps: ['jquery']
        },
        'bootstrap': {
            deps: ['jquery']
        },
        'mmenu': {
            deps: ['jquery']
        },
        'unveil': {
            deps: ['jquery']
        },
        'forms': {
            deps: ['jquery']
        }
    },
    deps: ['app']
});