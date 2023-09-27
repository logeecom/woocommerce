var ChannelEngine = window.ChannelEngine || {};

document.addEventListener(
    'DOMContentLoaded',
    function () {
        const url = document.getElementById('ceProductSave'),
            attributeUrl = document.getElementById('ceProductAttributes'),
            link = document.getElementById('ceSave'),
            stockUrl = document.getElementById('ce-stock-url'),
            extraDataUrl = document.getElementById('ceProductExtraData'),
            getAccountUrl = document.getElementById('ceGetAccountName'),
            exportProductsUrl = document.getElementById('ceExportProductsUrl'),
            threeLevelSyncUrl = document.getElementById('ceThreeLevelSyncUrl');

        document.getElementById('three-level-sync-warning').setAttribute('hidden',true);
        ChannelEngine.disconnectService.getAccountName(getAccountUrl);
        ChannelEngine.productService.get(stockUrl.value);
        ChannelEngine.productService.getExtraDataMapping(extraDataUrl.value);
        ChannelEngine.productService.getExportProductsEnabled(exportProductsUrl.value);
        ChannelEngine.productService.getThreeLevelSyncSettings(threeLevelSyncUrl.value);

        link.onclick = () => {
            const quantity = document.getElementById('ceStockQuantity'),
                enabledStockSync = document.getElementById('enableStockSync'),
                enabledThreeLevelSync = document.getElementById('enableThreeLevelSync'),
                threeLevelSyncAttribute= document.getElementById('ceThreeLevelSyncAttribute'),
                exportProducts = document.getElementById('enableExportProducts').checked ? 1 : 0,
                brandMapping = document.getElementById('ceBrand'),
                colorMapping = document.getElementById('ceColor'),
                sizeMapping = document.getElementById('ceSize'),
                gtinMapping = document.getElementById('ceGtin'),
                cataloguePriceMapping = document.getElementById('ceCataloguePrice'),
                priceMapping = document.getElementById('cePrice'),
                purchasePriceMapping = document.getElementById('cePurchasePrice'),
                shippingTimeMapping = document.getElementById('ceShippingTime'),
                detailsMapping = document.getElementById('ceDetails'),
                categoryMapping = document.getElementById('ceCategory'),
                vendorProductNumberMapping = document.getElementById('ceVendorProductNumber'),
                extraDataMappings = document.querySelectorAll('.ce-input-extra-data'),
                duplicatesText = document.getElementById('ce-extra-data-duplicates-text').value,
                duplicatesHeaderText = document.getElementById('ce-extra-data-duplicates-header').value;

            let extraData = {},
                isExtraDataValid = true;
            extraDataMappings.forEach(mapping => {
                if (mapping.id === 'hidden') {
                    return;
                }

                const elements = mapping.firstElementChild.children;
                const elValue = elements.item(1).value;
                if (!elValue || Object.keys(extraData).includes(elValue)) {
                    isExtraDataValid = false;
                    ChannelEngine.modalService.showModal(duplicatesHeaderText,
                        '<div>' +
                        '<label>' + duplicatesText + '</label>' +
                        '</div>',
                        null,
                        null
                    );
                    return;
                }
                extraData[elValue] = elements.item(0).value;
                extraData[elements.item(1).value] = elements.item(0).value;
            });

            if (!isExtraDataValid) {
                return;
            }

            ChannelEngine.notificationService.removeNotifications();
            ChannelEngine.productService.save(url.value, {
                exportProducts: exportProducts,
                quantity: quantity.value,
                enabledStockSync: enabledStockSync.checked,
                threeLevelSyncStatus: enabledThreeLevelSync.checked,
                threeLevelSyncAttribute: threeLevelSyncAttribute.value,
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
                    shippingTime: shippingTimeMapping.value
                },
                extraDataMappings: extraData
            });
        }

        ChannelEngine.productService.getProductAttributes(attributeUrl.value);
    }
);

document.getElementById('enableStockSync').addEventListener(
    'change',
    function () {
        const quantity = document.getElementById('ceStockQuantity');

        if (quantity.hasAttribute('disabled')) {
            quantity.removeAttribute('disabled');
        } else {
            quantity.setAttribute('disabled', 'true');
        }
    }
);

let enabledExportProducts = document.getElementById('enableExportProducts');
enabledExportProducts.addEventListener(
    'change',
    function () {
        if(enabledExportProducts.checked) {
            ChannelEngine.productService.enableProductSynchronizationFields();
        } else {
            ChannelEngine.productService.disableProductSynchronizationFields()
        }
    }
);

enabledThreeLevelSync = document.getElementById('enableThreeLevelSync')
document.getElementById('enableThreeLevelSync').addEventListener(
    'change',
    function () {
        if(enabledThreeLevelSync.checked) {
            ChannelEngine.productService.enableThreeLevelSyncOption();
        } else {
            ChannelEngine.productService.disableThreeLevelSyncOption()
        }
    }
);
