if (!window.ChannelEngine) {
    window.ChannelEngine = {};
}

(function () {
    function TriggerSyncService() {
        this.areEventsBinded = false;
        this.showModal = function (url) {
            let triggerModal = document.getElementById('ce-trigger-modal'),
                modal = triggerModal.children[0],
                orderCheckbox = document.getElementById('ce-order-sync-checkbox'),
                orderTooltip = document.getElementById('ce-order-sync-tooltip'),
                enableOrdersByMarketplaceSync = document.getElementById('enableOrdersByMarketplaceSync'),
                enableOrdersByMerchantSync = document.getElementById('enableOrdersByMerchantSync');

            if( ! ( enableOrdersByMerchantSync.checked || enableOrdersByMarketplaceSync.checked ) ) {
                orderCheckbox.setAttribute('disabled', 'true');
                orderCheckbox.checked = false;
                orderTooltip.textContent = 'Order synchronization is disabled, because both order synchronization options are disabled in the configuration (fulfilled by merchant and by marketplace).';
            } else {
                orderCheckbox.removeAttribute('disabled');
                orderTooltip.textContent = 'The integration will synchronize new and closed orders (fulfilled by the merchant and fulfilled by the marketplace) from ChannelEngine into the shop.';
            }

            triggerModal.style.display = "block";

            if (!this.areEventsBinded) {
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

                this.areEventsBinded = true;
            }

            ceSetHelp(".ce-modal-content");
        };

        this.triggerSync = function (url) {
            ChannelEngine.ajaxService.post(
                url,
                {},
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