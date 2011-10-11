
if (!("console" in window) || !("firebug" in console))
{
    var names = ["log", "debug", "info", "warn", "error", "assert", "dir", "dirxml",
    "group", "groupEnd", "time", "timeEnd", "count", "trace", "profile", "profileEnd"];

    if (!window.console) { // to avoid error "setting a property that has only a getter" in iframe
    window.console = {};
    for (var i = 0; i < names.length; ++i)
        window.console[names[i]] = function() {}
    }
}
