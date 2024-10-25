var ChannelEngine = window.ChannelEngine || {};

document.addEventListener(
    'DOMContentLoaded',
    function () {
        (function ($) {
            $('.datepicker').datepicker({
                dateFormat: 'dd.mm.yy.'
            });
        }(window.jQuery));

        const getUrl = document.getElementById('ceStatusesUrl'),
            saveUrl = document.getElementById('ceStatusesSaveUrl'),
            link = document.getElementById('ceStatusesSave'),
            checkEnableStockSync = document.getElementById('ceEnabledStockSync'),
            enableShipmentInfoSync = document.getElementById('enableShipmentInfoSync'),
            enableOrderCancellationSync = document.getElementById('enableOrderCancellationSync'),
            enableOrdersByMerchantSync = document.getElementById('enableOrdersByMerchantSync'),
            enableOrdersByMarketplaceSync = document.getElementById('enableOrdersByMarketplaceSync'),
            enableReduceStock = document.getElementById('enableReduceStock'),
            startSyncDate = document.getElementById('startSyncDate'),
            getAccountUrl = document.getElementById('ceGetAccountName'),
            enableVatExcludedPrices = document.getElementById('enableVatExcludedPrices'),
            enableWCTaxCalculation = document.getElementById('enableWCTaxCalculation'),
            vatExcludedPricesMessage = document.getElementById('vatExcludedPricesMessage');

            ChannelEngine.disconnectService.getAccountName(getAccountUrl);

        enableReduceStock.checked = true;
        ChannelEngine.ajaxService.get(checkEnableStockSync.value, function (response) {
            if (!response.enabledStockSync) {
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
                    startSyncDate: startSyncDate.value,
                    enableVatExcludedPrices: enableVatExcludedPrices.checked,
                    enableWCTaxCalculation: enableWCTaxCalculation.checked,
                }
            );
        }

        enableOrdersByMerchantSync.onchange = () => {
            ChannelEngine.ajaxService.get(checkEnableStockSync.value, function (response) {
                if (!response.enabledStockSync || !(enableOrdersByMerchantSync.checked || enableOrdersByMarketplaceSync.checked)) {
                    enableReduceStock.checked = false;
                    enableReduceStock.setAttribute('disabled', 'true');
                } else {
                    enableReduceStock.removeAttribute('disabled');
                }
            });

            if (!enableOrdersByMerchantSync.checked) {
                enableShipmentInfoSync.setAttribute('disabled', 'true');
                enableShipmentInfoSync.checked = false;
                enableOrderCancellationSync.setAttribute('disabled', 'true');
                enableOrderCancellationSync.checked = false;
            } else {
                enableShipmentInfoSync.removeAttribute('disabled');
                enableOrderCancellationSync.removeAttribute('disabled');
            }
        }

        enableOrdersByMarketplaceSync.onchange = () => {
            ChannelEngine.ajaxService.get(checkEnableStockSync.value, function (response) {
                if (!response.enabledStockSync || !(enableOrdersByMerchantSync.checked || enableOrdersByMarketplaceSync.checked)) {
                    enableReduceStock.checked = false;
                    enableReduceStock.setAttribute('disabled', 'true');
                } else {
                    enableReduceStock.removeAttribute('disabled');
                }
            });

            if (!enableOrdersByMarketplaceSync.checked) {
                startSyncDate.setAttribute('disabled', 'true');
            } else {
                startSyncDate.removeAttribute('disabled');
            }
        }

        enableVatExcludedPrices.onchange = () => {
            if(enableVatExcludedPrices.checked) {
                vatExcludedPricesMessage.classList.remove('ce-hidden');
                enableWCTaxCalculation.setAttribute('disabled', 'true');
            } else {
                vatExcludedPricesMessage.classList.add('ce-hidden');
                enableWCTaxCalculation.removeAttribute('disabled',);
            }
        }

        document.getElementById('displayOrderFulfilledDateDiv').setAttribute('hidden', 'true');
        ChannelEngine.orderService.get(getUrl.value);
    }
);

document.getElementById('stepToProductSettings').addEventListener(
    'click',
    function (e) {
        e.preventDefault()
        const ajaxService = ChannelEngine.ajaxService,
            url = document.getElementById('ceSwitchOnboardingPage');
        ajaxService.post(
            url.value,
            {
                'page': 'product_configuration'
            },
            function (response) {
                if (response.success) {
                    const incoming = document.getElementById('ceIncomingOrders'),
                        saveUrl = document.getElementById('ceStatusesSaveForSwitchUrl'),
                        shipped = document.getElementById('ceShippedOrders'),
                        fulfilledByMp = document.getElementById('ceFulfilledByMp'),
                        enableShipmentInfoSync = document.getElementById('enableShipmentInfoSync'),
                        enableOrderCancellationSync = document.getElementById('enableOrderCancellationSync'),
                        enableOrdersByMerchantSync = document.getElementById('enableOrdersByMerchantSync'),
                        enableOrdersByMarketplaceSync = document.getElementById('enableOrdersByMarketplaceSync'),
                        enableReduceStock = document.getElementById('enableReduceStock'),
                        startSyncDate = document.getElementById('startSyncDate'),
                        enableVatExcludedPrices = document.getElementById('enableVatExcludedPrices'),
                        enableWCTaxCalculation = document.getElementById('enableWCTaxCalculation');
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
                            enableVatExcludedPrices: enableVatExcludedPrices.checked,
                            enableWCTaxCalculation: enableWCTaxCalculation.checked,
                            enableReduceStock: enableReduceStock.checked,
                            startSyncDate: startSyncDate.value
                        }
                    );

                    document.getElementById('displayOrderFulfilledDateDiv').removeAttribute('hidden');
                    window.location.reload();
                } else {
                    ChannelEngine.notificationService.addNotification(response.message);
                }
            }
        )
    }
);
