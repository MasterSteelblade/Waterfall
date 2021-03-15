function renderBox(type, message, title = '', allowClose = false) {
    /**
     * Supported types include:
     * - danger
     * - success
     * - info
     * - warn
     * - primary
     * - secondary
     */
    if (type == 'error') {
        // Special case because I keep fucking it up.
        type = 'danger';
    }
    var string = '<div class="alert alert-'+ type + ' w-100" role="alert">';
    if (title != '') {
        string = string + '<h4>' + title + '</h4><p>';
    }
    string = string + message;
    if (title != '') {
        string = string + '</p>';
    }
    if (allowClose == true) {
        string = string + '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
    }
    return string;
}