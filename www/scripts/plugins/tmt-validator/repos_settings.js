// --- these are temporarily moved to the bottom of the validator js ----
// Disallow characters that are invalid in any operating system's filenames
// "&" is confused with query string separator in svnindex, so it shouldn't be encouraged
//tmt_globalPatterns.reposFilename = new RegExp("^[^&:\"'´<>£@¤#~¨\\?\\*\\$\\|\\(\\)\\[\\]\\t\\n\\/\\\\]+$");
// should be practically no restrictions on log message characters
//tmt_globalPatterns.reposLogmessage = new RegExp("^[^\\f\\v\\0]+$");
