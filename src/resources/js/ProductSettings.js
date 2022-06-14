var ChannelEngine = window.ChannelEngine || {};

document.addEventListener(
    'DOMContentLoaded',
    function () {
        const url = document.getElementById('ceProductSave'),
            attributeUrl = document.getElementById('ceProductAttributes'),
            link = document.getElementById('ceSave');

        link.onclick = () => {
            const quantity = document.getElementById('ceStockQuantity'),
                enabledStockSync = document.getElementById('enableStockSync'),
                brandMapping = document.getElementById('ceBrand'),
                colourMapping = document.getElementById('ceColour'),
                sizeMapping = document.getElementById('ceSize'),
                gtinMapping = document.getElementById('ceGtin'),
                cataloguePriceMapping = document.getElementById('ceCataloguePrice'),
                priceMapping = document.getElementById('cePrice'),
                purchasePriceMapping = document.getElementById('cePurchasePrice'),
                detailsMapping = document.getElementById('ceDetails'),
                categoryMapping = document.getElementById('ceCategory'),
                extraDataMappings = document.querySelectorAll('.ce-input-extra-data');

            let extraData = {};
            extraDataMappings.forEach(mapping => {
                if( mapping.id === 'hidden' ) {
                    return;
                }

                const elements = mapping.firstElementChild.children;
                extraData[elements.item(1).value] = elements.item(0).value;
            });

            ChannelEngine.notificationService.removeNotifications();
            ChannelEngine.productService.save(url.value, {
                quantity: quantity.value,
                enabledStockSync: enabledStockSync.checked,
                attributeMappings: {
                    brand: brandMapping.value,
                    colour: colourMapping.value,
                    size: sizeMapping.value,
                    gtin: gtinMapping.value,
                    cataloguePrice: cataloguePriceMapping.value,
                    price: priceMapping.value,
                    purchasePrice: purchasePriceMapping.value,
                    details: detailsMapping.value,
                    category: categoryMapping.value
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