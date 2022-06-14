var ChannelEngine = window.ChannelEngine || {};

document.addEventListener(
    'DOMContentLoaded',
    function () {
        (function($) {
            $('.datepicker').datepicker({
                dateFormat : 'dd.mm.yy.'
            });
        }(window.jQuery));

        const getUrl = document.getElementById('ceStatusesUrl'),
            saveUrl = document.getElementById('ceStatusesSaveUrl'),
            link = document.getElementById('ceStatusesSave'),
            checkEnableStockSync = document.getElementById('ceEnabledStockSync'),
            enableOrdersByMerchantSync = document.getElementById('enableOrdersByMerchantSync'),
            enableOrdersByMarketplaceSync = document.getElementById('enableOrdersByMarketplaceSync'),
            enableReduceStock = document.getElementById('enableReduceStock'),
            startSyncDate = document.getElementById('startSyncDate');

        enableReduceStock.checked = true;
        ChannelEngine.ajaxService.get(checkEnableStockSync.value, function (response) {
            if(!response.enabledStockSync) {
                enableReduceStock.checked = false;
                enableReduceStock.setAttribute('disabled', 'true');
            }
        });

        link.onclick = () => {
            const incoming = document.getElementById('ceIncomingOrders'),
                shipped = document.getElementById('ceShippedOrders'),
                fulfilledByMp = document.getElementById('ceFulfilledByMp'),
                enableShipmentInfoSync = document.getElementById('enableShipmentInfoSync'),
                enableOrderCancellationSync = document.getElementById('enableOrderCancellationSync');
            ChannelEngine.notificationService.removeNotifications();

            ChannelEngine.orderService.save(
                saveUrl.value,
                {
                    incoming: incoming.value,
                    shipped: shipped.value,
                    fulfilledByMp: fulfilledByMp.value,
                    enableShipmentInfoSync: enableShipmentInfoSync.checked,
                    enableOrderCancellationSync: enableOrderCancellationSync.checked,
                    enableOrdersByMerchantSync: enableOrdersByMerchantSync.checked,
                    enableOrdersByMarketplaceSync: enableOrdersByMarketplaceSync.checked,
                    enableReduceStock: enableReduceStock.checked,
                    startSyncDate: startSyncDate.value
                }
            );
        }

        enableOrdersByMerchantSync.onchange = () =>  {
            if( ! ( enableOrdersByMerchantSync.checked || enableOrdersByMarketplaceSync.checked ) ) {
                enableReduceStock.checked = false;
                enableReduceStock.setAttribute('disabled', 'true');
            } else {
                enableReduceStock.removeAttribute('disabled');
            }
        }

        enableOrdersByMarketplaceSync.onchange = () =>  {
            if( ! ( enableOrdersByMerchantSync.checked || enableOrdersByMarketplaceSync.checked ) ) {
                enableReduceStock.checked = false;
                enableReduceStock.setAttribute('disabled', 'true');
            } else {
                enableReduceStock.removeAttribute('disabled');
            }

            if ( !enableOrdersByMarketplaceSync.checked ) {
                startSyncDate.setAttribute('disabled', 'true');
            } else {
                startSyncDate.removeAttribute('disabled');
            }
        }

        ChannelEngine.orderService.get(getUrl.value);
    }
);