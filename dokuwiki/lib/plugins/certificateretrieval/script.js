    /**
     * Add button action for your toolbar button
     *
     * @param  {jQuery}   $btn  Button element to add the action to
     * @param  {Array}    props Associative array of button properties
     * @param  {string}   edid  ID of the editor textarea
     * @return {boolean}  If button should be appended
     */
    function addBtnActionClick($btn, props, edid) {
        $btn.click(function() {
            window.location="/sirs/common/dokuwikicertificaterequest.php?DokuWiki=cert";
        });
     
        return true;
    }


    // add a new toolbar button, but first check if there is a toolbar
    if (window.toolbar != undefined) {
        window.toolbar[window.toolbar.length] = {
            "type":"Click", // we have a new type that links to the function
            "title":"Hey Click me!",
            "icon":"../../images/open.png", 
        };
    }