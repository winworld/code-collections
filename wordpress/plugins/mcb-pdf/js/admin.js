(function (window, wp) {

    // just to keep it cleaner - we refer to our link by id for speed of lookup on DOM.
    var button_id = 'mcb_pdf_print';
    // set up the link
    var href = window.location.href + '&output=pdf';    

    var button = '<a href="' + href + '" id="' + button_id + '" class="components-button is-primary">Download PDF</a>';
    
    // check if gutenberg's editor root element is present.
    var editorEl = document.getElementById('editor');
    if (!editorEl) { // do nothing if there's no gutenberg root element on page.
        return;
    }

    var unsubscribe = wp.data.subscribe(function () {
        setTimeout(function () {
            if (!document.getElementById(button_id)) {
                var toolbalEl = editorEl.querySelector('.edit-post-header__settings');
                if (toolbalEl instanceof HTMLElement) {
                    toolbalEl.insertAdjacentHTML('afterbegin', button);
                }
            }
        }, 1)
    });
    // unsubscribe is a function - it's not used right now 
    // but in case you'll need to stop this link from being reappeared at any point you can just call unsubscribe();

})(window, wp)