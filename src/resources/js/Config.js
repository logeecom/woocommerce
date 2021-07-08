var ChannelEngine = window.ChannelEngine || {};

document.addEventListener(
    'DOMContentLoaded',
    function () {
        const accountDataUrl = document.getElementById('ce-account-data-url'),
            disconnectUrl = document.getElementById('ce-disconnect-url'),
            disableUrl = document.getElementById('ce-disable-url'),
            triggerSyncUrl = document.getElementById('ce-trigger-sync-url'),
            stockUrl = document.getElementById('ce-stock-url'),
            orderStatusesUrl = document.getElementById('ce-order-statuses-url'),
            saveUrl = document.getElementById('ce-save-url'),
            disableSwitch = document.getElementById('ce-disable-switch'),
            apiKey = document.getElementById('ceApiKey'),
            accountName = document.getElementById('ceAccountName'),
            saveBtn = document.getElementById('ce-save-config'),
            quantity = document.getElementById('ceStockQuantity'),
            incoming = document.getElementById('ceIncomingOrders'),
            shipped = document.getElementById('ceShippedOrders'),
            fulfilledByMp = document.getElementById('ceFulfilledByMp'),
            disconnectBtn = document.getElementById('ce-disconnect-btn'),
            syncNowBtn = document.getElementById('ce-sync-now');


        ChannelEngine.productService.get(stockUrl.value);
        ChannelEngine.orderService.get(orderStatusesUrl.value);
        ChannelEngine.triggerSyncService.checkStatus();

        syncNowBtn.onclick = function () {
            ChannelEngine.triggerSyncService.showModal(triggerSyncUrl.value);
        }

        disconnectBtn.onclick = function () {
            let header = document.getElementById('ce-disconnect-header-text'),
                btnText = document.getElementById('ce-disconnect-button-text'),
                text = document.getElementById('ce-disconnect-text');

            ChannelEngine.modalService.showModal(
                header.value,
                '<div>' +
                '<label>' + text.value + '</label>' +
                '</div>',
                btnText.value,
                disconnect
            );
        };

        disableSwitch.onchange = (event) => {
            let header = document.getElementById('ce-disable-header-text'),
                btnText = document.getElementById('ce-disable-button-text'),
                text = document.getElementById('ce-disable-text');

            if (!event.currentTarget.checked) {
                ChannelEngine.modalService.showModal(
                    header.value,
                    '<div>' +
                    '<label>' + text.value + '</label>' +
                    '</div>',
                    btnText.value,
                    disable
                );
            }
        }

        let disable = function () {
            ChannelEngine.ajaxService.get(
                disableUrl.value,
                function (response) {
                    if (response.success) {
                        window.location.assign(window.location.href.replace(window.location.search, '?page=channel-engine'));
                    }
                }
            );
        }

        let disconnect = function () {
            ChannelEngine.ajaxService.get(
                disconnectUrl.value,
                function (response) {
                    window.location.assign(window.location.href.replace(window.location.search, '?page=channel-engine'));
                }
            )
        }

        ChannelEngine.ajaxService.get(
            accountDataUrl.value,
            function (response) {
                apiKey.value = response.apiKey;
                accountName.value = response.accountName;
            }
        );

        saveBtn.onclick = function () {
            ChannelEngine.ajaxService.post(
                saveUrl.value,
                {
                    apiKey: apiKey.value,
                    accountName: accountName.value,
                    stockQuantity: quantity.value,
                    orderStatuses: {
                        incoming: incoming.value,
                        shipped: shipped.value,
                        fulfilledByMp: fulfilledByMp.value
                    }
                },
                function (response) {
                    ChannelEngine.notificationService.removeNotifications();
                    ChannelEngine.notificationService.addNotification(response.message, response.success);

                    if (response.success) {
                        ChannelEngine.triggerSyncService.showModal(triggerSyncUrl.value);
                    }
                }
            )
        }
    }
);