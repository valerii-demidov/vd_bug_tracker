/**
 * Created by ocz on 08.05.17.
 */
$( "#issue-main-tab" ).tabs({
    active: 0
});


/**
 * Issue comments
 */
$(".comment-action" ).each(function( index ) {
    $(this).click(function () {
        var actionUrl =  $(this).attr('href');
        $('.modal-container').load(actionUrl, function (result) {
            $('#myModal').modal({show:true});
        });

        return false;
    })
});
