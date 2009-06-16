// Rules definitions for property editor UI
$().bind('repos-propedit-init', function(ev, rules) {
// -------- start of rules definitions ---------

// Status should be one of the following values. Not mandatory.
rules.add('cms:status', ['', 'In Work', 'Released', 'In Translation', 'Obsolete']);

// Arbotext properties should not be edited manually as they are set from Arbortext Editor.
//rules.add(/^abx:/, false);
rules.add(/^abx:/, 0); // make them invisible in editor

// --------- end of rules definitions ----------
});

