/*
 * modified from 'admin' sprinkle -> widgets/users.js and pages/user.js
 * we're not using any AJAX here, so what should we put in the ajaxPArams below? :(
 */

$(document).ready(function() {
    // Control buttons
    bindUserButtons($("#self-termination"), { });
});

/**
 * Link user action buttons, for example in a table or on a specific user's page.
 * @param {module:jQuery} el jQuery wrapped element to target.
 * @param {{delete_redirect: string}} options Options used to modify behaviour of button actions.
 */
function bindUserButtons(el, options) {
    if (!options) options = {};

    /**
     * Buttons that launch a modal dialog
     */

    // Delete user button
    el.find('.js-user-delete').click(function(e) {
        e.preventDefault();

        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/users/confirm-termination",
            ajaxParams: {
		    // no params used by this URL
                //user_name: $(this).data('user_name')  
            },
            msgTarget: $("#alerts-page")
        });

        $("body").on('renderSuccess.ufModal', function() {
            var modal = $(this).ufModal('getModal');
            var form = modal.find('.js-form');

            form.ufForm()
                .on("submitSuccess.ufForm", function() {
                    // Navigate or reload page on success
                    if (options.delete_redirect) window.location.href = options.delete_redirect;
                    else window.location.reload();
                });
        });
    });

}