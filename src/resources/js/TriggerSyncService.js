if (!window.ChannelEngine) {
    window.ChannelEngine = {};
}

(function () {
    function TriggerSyncService() {
        this.showModal = function (url) {
            let triggerModal = document.getElementById('ce-trigger-modal'),
                modal = triggerModal.children[0];

            triggerModal.style.display = "block";
            modal.querySelectorAll('.ce-button__secondary').forEach(closeButton => {
                closeButton.addEventListener('click', () => {
                    triggerModal.style.display = "none";
                });
            });

            modal.querySelectorAll('.ce-button__primary').forEach(syncButton => {
                syncButton.addEventListener('click', () => {
                    triggerModal.style.display = "none";
                    this.triggerSync(url);
                });
            });

            modal.querySelectorAll('.ce-close-button').forEach(closeBtn => {
                closeBtn.addEventListener('click', () => {
                    triggerModal.style.display = "none";
                });
            })

            ceSetHelp(".ce-modal-content");
        };

        this.triggerSync = function (url) {
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

        this.checkStatus = function () {
            let syncNowButton = document.getElementById('ce-sync-now'),
                inProgressButton = document.getElementById('ce-sync-in-progress'),
                checkStatusUrl = document.getElementById('ce-check-status-url');

            ChannelEngine.ajaxService.get(
                checkStatusUrl.value,
                function (response) {
                    if (response.in_progress) {
                        syncNowButton.style.display = "none";
                        inProgressButton.style.display = "inline-block";
                        setTimeout(ChannelEngine.triggerSyncService.checkStatus, 1000);
                    } else {
                        syncNowButton.style.display = "inline-block";
                        inProgressButton.style.display = "none";
                    }
                }
            );
        }
    }

    ChannelEngine.triggerSyncService = new TriggerSyncService();
})();