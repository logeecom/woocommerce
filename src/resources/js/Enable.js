var ChannelEngine = window.ChannelEngine || {};

document.addEventListener(
    'DOMContentLoaded',
    function () {
        const ajaxService = ChannelEngine.ajaxService,
            enableUrl = document.getElementById('ce-enable-plugin'),
            enableSwitch = document.getElementById('ce-enable-switch');

        let triggerModal = document.getElementById('ce-trigger-modal'),
            getOrderSyncConfigUrl = document.getElementById('ce-check-order-sync'),
            orderTooltip = document.getElementById('ce-order-sync-tooltip'),
            modal = triggerModal.children[0];


        modal.querySelectorAll('.ce-button__secondary').forEach(button => {
            button.addEventListener('click', () => {
                enablePlugin();
            });
        });

        modal.querySelectorAll('.ce-button__primary').forEach(syncButton => {
            syncButton.addEventListener('click', () => {
                triggerModal.style.display = "none";
                enablePlugin();
            });
        });

        modal.querySelectorAll('.ce-close-button').forEach(closeBtn => {
            closeBtn.addEventListener('click', () => {
                triggerModal.style.display = "none";
            });
        })

        ceSetHelp(".ce-modal-content");

        enableSwitch.checked = false;

        enableSwitch.onchange = (event) => {
            if (event.currentTarget.checked) {
                triggerModal.style.display = "block";
                ajaxService.get(getOrderSyncConfigUrl.value, function (response) {
                    if( ! response.enabled ) {
                        let order = document.getElementById('ce-order-sync-checkbox');
                        order.setAttribute('disabled', 'true');
                        orderTooltip.textContent = 'Order synchronization is disabled, because both order synchronization options are disabled in the configuration (fulfilled by merchant and by marketplace).';
                    }
                });
            }
        }

        function enablePlugin() {
            let productsChecked = document.getElementById('ce-product-sync-checkbox'),
                ordersChecked = document.getElementById('ce-order-sync-checkbox');

            ajaxService.post(enableUrl.value, {
                product_sync: productsChecked.checked,
                order_sync: ordersChecked.checked
            }, function (response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    enableSwitch.checked = false;
                    triggerModal.style.display = 'none';
                    ChannelEngine.notificationService.addNotification(response.message);
                }
            });
        }
    }
);