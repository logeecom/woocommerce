var ChannelEngine = window.ChannelEngine || {};

document.addEventListener(
    'DOMContentLoaded',
    function () {
        const ajaxService = ChannelEngine.ajaxService,
            enableUrl = document.getElementById('ce-enable-plugin'),
            enableSwitch = document.getElementById('ce-enable-switch'),
            triggerUrl = document.getElementById('ce-trigger-sync-url');

        enableSwitch.checked = false;

        enableSwitch.onchange = (event) => {
            if (event.currentTarget.checked) {
                let triggerModal = document.getElementById('ce-trigger-modal'),
                    modal = triggerModal.children[0];

                triggerModal.style.display = "block";
                modal.querySelectorAll('.ce-button__secondary').forEach(button => {
                    button.addEventListener('click', () => {
                        enablePlugin();
                    });
                });

                modal.querySelectorAll('.ce-button__primary').forEach(syncButton => {
                    syncButton.addEventListener('click', () => {
                        triggerModal.style.display = "none";
                        triggerSync(triggerUrl.value);
                        enablePlugin();
                    });
                });

                modal.querySelectorAll('.ce-close-button').forEach(closeBtn => {
                    closeBtn.addEventListener('click', () => {
                        triggerModal.style.display = "none";
                    });
                })

                ceSetHelp(".ce-modal-content");
            }
        }

        function enablePlugin() {
            ajaxService.get(enableUrl.value, function () {
                window.location.reload();
            });
        }

        function triggerSync(url) {
            let productsChecked = document.getElementById('ce-product-sync-checkbox'),
                ordersChecked = document.getElementById('ce-order-sync-checkbox');

            ChannelEngine.ajaxService.post(
                url,
                {
                    product_sync: productsChecked.checked,
                    order_sync: ordersChecked.checked
                },
                function (response) {
                    if (!response.success) {
                        ChannelEngine.notificationService.addNotification(response.message);
                    } else {
                        ChannelEngine.triggerSyncService.checkStatus();
                    }
                }
            );
        }
    }
);