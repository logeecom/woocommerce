if (!window.ChannelEngine) {
    window.ChannelEngine = {};
}

(function () {
    function OrderService() {
        this.get = function (url) {
            const ajaxService = ChannelEngine.ajaxService;

            ajaxService.get(
                url,
                function (response) {
                    const incomingOptions = document.getElementById('ceIncomingOrders'),
                        shippedOptions = document.getElementById('ceShippedOrders'),
                        fulfilledByMpOptions = document.getElementById('ceFulfilledByMp'),
                        checkEnableStockSync = document.getElementById('ceEnabledStockSync'),
                        enableShipmentInfoSync = document.getElementById('enableShipmentInfoSync'),
                        enableOrderCancellationSync = document.getElementById('enableOrderCancellationSync'),
                        enableOrdersByMerchantSync = document.getElementById('enableOrdersByMerchantSync'),
                        enableOrdersByMarketplaceSync = document.getElementById('enableOrdersByMarketplaceSync'),
                        enableReduceStock = document.getElementById('enableReduceStock'),
                        enableVatExcludedPrices = document.getElementById('enableVatExcludedPrices'),
                        marketplaceOrdersFromDate = document.getElementById('startSyncDate'),
                        mappings = response.order_statuses,
                        orderFulfilledByMarketplaceDate = document.getElementById('displayOrderFulfilledDate'),
                        displayDate = document.getElementById('displayDate');

                    if (response.enableOrdersByMarketplaceSync && !document.getElementById('displayOrderFulfilledDateDiv').getAttribute('disabled')) {
                        displayDate.innerHTML = response.ordersByMarketplaceFromDate;
                        displayDate.removeAttribute('hidden');
                        orderFulfilledByMarketplaceDate.removeAttribute('hidden');
                    } else {
                        orderFulfilledByMarketplaceDate.setAttribute('hidden', 'true');
                        displayDate.setAttribute('hidden', 'true');
                    }

                    mappings.forEach(item => addMappings(item, incomingOptions, response.incoming));
                    mappings.forEach(item => addMappings(item, shippedOptions, response.shipped));
                    mappings.forEach(item => addMappings(item, fulfilledByMpOptions, response.fulfilledByMp));

                    enableShipmentInfoSync.checked = response.enableShipmentInfoSync;
                    enableOrderCancellationSync.checked = response.enableOrderCancellationSync;
                    enableOrdersByMerchantSync.checked = response.enableOrdersByMerchantSync;
                    enableOrdersByMarketplaceSync.checked = response.enableOrdersByMarketplaceSync;
                    enableReduceStock.checked = response.enableReduceStock;
                    enableVatExcludedPrices.checked = response.enableVatExcludedPrices;

                    if (marketplaceOrdersFromDate) {
                        marketplaceOrdersFromDate.value = response.ordersByMarketplaceFromDate;

                        if (!response.enableOrdersByMarketplaceSync) {
                            marketplaceOrdersFromDate.setAttribute('disabled', 'true');
                        }
                    }

                    if (!response.enableOrdersByMerchantSync) {
                        enableShipmentInfoSync.setAttribute('disabled', 'true');
                        enableOrderCancellationSync.setAttribute('disabled', 'true');
                    }

                    ChannelEngine.ajaxService.get(checkEnableStockSync.value, function (response) {
                        if (!response.enabledStockSync) {
                            enableReduceStock.checked = false;
                            enableReduceStock.setAttribute('disabled', 'true');
                        }
                    });

                    ChannelEngine.loader.force()
                }
            );

            function addMappings(item, parent, mapping) {
                const option = document.createElement('OPTION');

                option.innerHTML = item.label;
                option.value = item.value;
                if (item.value === mapping.value) {
                    option.selected = true;
                }

                parent.appendChild(option);
            }
        };

        this.save = function (url, data) {
            const ajaxService = ChannelEngine.ajaxService;
            ChannelEngine.notificationService.removeNotifications();

            ajaxService.post(
                url,
                data,
                function (response) {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        ChannelEngine.notificationService.addNotification(response.message);
                    }
                }
            );
        };
    }

    ChannelEngine.orderService = new OrderService();
})();