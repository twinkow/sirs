/**
 * With the first function we create a new button type called Click
 *
 * the function name must be addBtnAction<Your type name>
 * in our case it is addBtnActionClick
 *
 * in the other function we simply use the simple toolbar method with the new type
 *
 * you can easily extend it to complex scripts like the link wizard etc
 */

/**
 * Add button action for your toolbar button
 *
 * @param  {jQuery}   $btn  Button element to add the action to
 * @param  {Array}    props Associative array of button properties
 * @param  {string}   edid  ID of the editor textarea
 * @return {boolean}  If button should be appended
 */
function addBtnActionDigsig($btn, props, edid) {

    jQuery("body").append("<input type='file' style='display: none;' name='file' id='file'>");

    $btn.click(function() {
        jQuery('#file').trigger('click');
        return false;
    });

    return true;
}
