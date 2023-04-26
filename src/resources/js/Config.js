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
            syncNowBtn = document.getElementById('ce-sync-now'),
            enableStockSync = document.getElementById('enableStockSync'),
            enableShipmentInfoSync = document.getElementById('enableShipmentInfoSync'),
            enableOrderCancellationSync = document.getElementById('enableOrderCancellationSync'),
            enableOrdersByMerchantSync = document.getElementById('enableOrdersByMerchantSync'),
            enableOrdersByMarketplaceSync = document.getElementById('enableOrdersByMarketplaceSync'),
            productAttributesUrl = document.getElementById('ceProductAttributes'),
            brandMapping = document.getElementById('ceBrand'),
            colorMapping = document.getElementById('ceColor'),
            sizeMapping = document.getElementById('ceSize'),
            gtinMapping = document.getElementById('ceGtin'),
            cataloguePriceMapping = document.getElementById('ceCataloguePrice'),
            priceMapping = document.getElementById('cePrice'),
            purchasePriceMapping = document.getElementById('cePurchasePrice'),
            shippingTime = document.getElementById('ceShippingTime'),
            detailsMapping = document.getElementById('ceDetails'),
            categoryMapping = document.getElementById('ceCategory'),
            vendorProductNumberMapping = document.getElementById('ceVendorProductNumber'),
            extraDataUrl = document.getElementById('ceProductExtraData'),
            enableReduceStock = document.getElementById('enableReduceStock'),
            duplicatesText = document.getElementById('ce-extra-data-duplicates-text').value,
            duplicatesHeaderText = document.getElementById('ce-extra-data-duplicates-header').value,
            startSyncDate = document.getElementById('startSyncDate'),
            getAccountNameUrl = document.getElementById('ceGetAccountName'),
            exportProductsUrl = document.getElementById('ceExportProductsUrl');

        startSyncDate ? startSyncDate.remove(): false;
        ChannelEngine.productService.get(stockUrl.value);
        ChannelEngine.productService.getProductAttributes(productAttributesUrl.value);
        ChannelEngine.productService.getExtraDataMapping(extraDataUrl.value);
        ChannelEngine.orderService.get(orderStatusesUrl.value);
        ChannelEngine.triggerSyncService.checkStatus();
        ChannelEngine.disconnectService.getAccountName(getAccountNameUrl);
        ChannelEngine.productService.getExportProductsEnabled(exportProductsUrl.value);

        if( ! ( enableStockSync.checked || ( enableOrdersByMerchantSync.checked && enableOrdersByMarketplaceSync.checked ) ) ) {
            enableReduceStock.setAttribute('disabled', 'true');
        }

        syncNowBtn.onclick = function () {
            const orderCheckbox = document.getElementById('ce-order-sync-checkbox'),
                productCheckbox = document.getElementById('ce-product-sync-checkbox');
            orderCheckbox.checked = true;
            productCheckbox.checked = true
            ChannelEngine.triggerSyncService.triggerSync(triggerSyncUrl.value);
            orderCheckbox.checked = false;
            productCheckbox.checked = false
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
            let extraData = {},
                extraDataMapping = document.querySelectorAll('.ce-input-extra-data'),
                isValid = true;

            extraDataMapping.forEach(mapping => {
                if( mapping.id === 'hidden' ) {
                    return;
                }

                const elements = mapping.firstElementChild.children;
                const elValue = elements.item(1).value;
                if (!elValue || Object.keys(extraData).includes(elValue)) {
                    isValid = false;
                    ChannelEngine.modalService.showModal(duplicatesHeaderText,
                        '<div>' +
                        '<label>'+duplicatesText+'</label>' +
                        '</div>',
                        null,
                        null
                    );
                    return;
                }
                extraData[elValue] = elements.item(0).value;
            });

            if (!isValid) {
                return;
            }

            ChannelEngine.ajaxService.post(
                saveUrl.value,
                {
                    apiKey: apiKey.value,
                    accountName: accountName.value,
                    exportProducts: enabledExportProducts.checked ? 1 : 0,
                    stockQuantity: quantity.value,
                    enabledStockSync: enableStockSync.checked,
                    enableReduceStock: enableReduceStock.checked,
                    orderStatuses: {
                        incoming: incoming.value,
                        shipped: shipped.value,
                        fulfilledByMp: fulfilledByMp.value
                    },
                    orderSyncConfig: {
                        enableShipmentInfoSync: enableShipmentInfoSync.checked,
                        enableOrderCancellationSync: enableOrderCancellationSync.checked,
                        enableOrdersByMerchantSync: enableOrdersByMerchantSync.checked,
                        enableOrdersByMarketplaceSync: enableOrdersByMarketplaceSync.checked
                    },
                    attributeMappings: {
                        brand: brandMapping.value,
                        color: colorMapping.value,
                        size: sizeMapping.value,
                        gtin: gtinMapping.value,
                        cataloguePrice: cataloguePriceMapping.value,
                        price: priceMapping.value,
                        purchasePrice: purchasePriceMapping.value,
                        details: detailsMapping.value,
                        category: categoryMapping.value,
                        vendorProductNumber: vendorProductNumberMapping.value,
                        shippingTime: shippingTime.value,
                    },
                    extraDataMappings: extraData
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

        enableStockSync.onchange = function () {
            const quantity = document.getElementById('ceStockQuantity'),
                reduceStock = document.getElementById('enableReduceStock');

            if (quantity.hasAttribute('disabled')) {
                quantity.removeAttribute('disabled');
                if ( enableOrdersByMerchantSync.checked || enableOrdersByMarketplaceSync.checked ) {
                    reduceStock.removeAttribute('disabled');
                }
            } else {
                quantity.setAttribute('disabled', 'true');
                reduceStock.setAttribute('disabled', 'true');
                if (reduceStock.checked) {
                    reduceStock.checked = false;
                }
            }
        }

        enableOrdersByMerchantSync.onchange = function () {
            changeReduceStockVisibility();
            if ( ! enableOrdersByMerchantSync.checked ) {
                enableShipmentInfoSync.setAttribute('disabled', 'true');
                enableShipmentInfoSync.checked = false
                enableOrderCancellationSync.setAttribute('disabled', 'true');
                enableOrderCancellationSync.checked = false
            } else {
                enableShipmentInfoSync.removeAttribute('disabled');
                enableOrderCancellationSync.removeAttribute('disabled');
            }
        }

        enableOrdersByMarketplaceSync.onchange = function () {
            changeReduceStockVisibility();
        }

        let changeReduceStockVisibility = function () {
            if (!(enableOrdersByMerchantSync.checked || enableOrdersByMarketplaceSync.checked )) {
                enableReduceStock.setAttribute('disabled', 'true');
            }

            if ( enableStockSync.checked && (enableOrdersByMerchantSync.checked || enableOrdersByMarketplaceSync.checked )) {
                enableReduceStock.removeAttribute('disabled');
            }
        }

        let enabledExportProducts = document.getElementById('enableExportProducts');
        enabledExportProducts.addEventListener(
            'change',
            function () {
                let productCheckbox = document.getElementById('ce-product-sync-checkbox')

                if(enabledExportProducts.checked) {
                    productCheckbox.removeAttribute('disabled');
                    ChannelEngine.productService.enableProductSynchronizationFields();
                } else {
                    productCheckbox.setAttribute('disabled', 'true');
                    ChannelEngine.productService.disableProductSynchronizationFields()
                }
            }
        );
    }
);