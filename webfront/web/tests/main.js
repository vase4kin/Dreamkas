var tests = Object.keys(window.__karma__.files).filter(function (file) {
    return /.spec\.js$/.test(file);
});

require.config({
    baseUrl: '/base',
    deps: tests,
    callback: window.__karma__.start
});