function clearCheckout() {

        if (confirm(I18N["clear_checkout_warning"])) {

                document.pageForm.pageAction.value = "clearCheckout";
                document.pageForm.submit();

        }

}
