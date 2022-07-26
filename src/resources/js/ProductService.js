if (!window.ChannelEngine) {
    window.ChannelEngine = {};
}

(function () {
    function ProductService() {
        this.get = function (url) {
            const ajaxService = ChannelEngine.ajaxService,
                quantity = document.getElementById('ceStockQuantity'),
                enabledStockSync = document.getElementById('enableStockSync');

            ajaxService.get(url, function (response) {
                quantity.value = response.stockQuantity;
                enabledStockSync.checked = response.enabledStockSync;

                if( !enabledStockSync.checked ) {
                    quantity.setAttribute('disabled', 'true');
                }
            });
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
                });
        };

        this.getProductAttributes = function (url) {
            const ajaxService = ChannelEngine.ajaxService;

            ajaxService.get(url, function (response) {
                const brandOptions = document.getElementById('ceBrand'),
                    colorOptions = document.getElementById('ceColor'),
                    sizeOptions = document.getElementById('ceSize'),
                    gtinOptions = document.getElementById('ceGtin'),
                    cataloguePriceOptions = document.getElementById('ceCataloguePrice'),
                    priceOptions = document.getElementById('cePrice'),
                    purchasePriceOptions = document.getElementById('cePurchasePrice'),
                    detailsOptions = document.getElementById('ceDetails'),
                    categoryOptions = document.getElementById('ceCategory'),
                    mappings = response.product_attributes;

                mappings.forEach(item => addMappings(item, brandOptions, response.brand));
                mappings.forEach(item => addMappings(item, colorOptions, response.color));
                mappings.forEach(item => addMappings(item, sizeOptions, response.size));
                mappings.forEach(item => addMappings(item, gtinOptions, response.gtin));
                mappings.forEach(item => addMappings(item, cataloguePriceOptions, response.catalogue_price));
                mappings.forEach(item => addMappings(item, priceOptions, response.price));
                mappings.forEach(item => addMappings(item, purchasePriceOptions, response.purchase_price));
                mappings.forEach(item => addMappings(item, detailsOptions, response.details));
                mappings.forEach(item => addMappings(item, categoryOptions, response.category));
            });

            function addMappings (item, parent, mapping) {
                const option = document.createElement('OPTION');

                option.innerHTML = item.label;
                option.value = item.value;
                if (item.value === mapping) {
                    option.selected = true;
                }

                parent.appendChild(option);
            }
        }

        this.getExtraDataMappingOptions = function (url, element, selected) {
            const ajaxService = ChannelEngine.ajaxService;

            ajaxService.get(url, function (response) {
                response.product_attributes.forEach(mapping => {
                    let option = document.createElement('option');
                    option.text = mapping.label;
                    option.value = mapping.value;
                    element.add(option);
                    if( selected === mapping.value ) {
                        element.value = mapping.value;
                    }
                });
            });
        }

        this.getExtraDataMapping = function (url) {
            const ajaxService = ChannelEngine.ajaxService;

            ajaxService.get(url, function (response) {
                Object.entries(response.extra_data_mapping).forEach(entry => {
                    const [key, value] = entry;
                    let element = ChannelEngine.productService.makeExtraDataForm(value);
                    element.children[0].children[1].value = key;
                });
            });
        }

        this.makeExtraDataForm = function (selected) {
            const newAttribute = document.getElementById('hidden'),
                clone = newAttribute.cloneNode(true),
                previous = document.querySelectorAll('.last').item(0),
                attribute = previous.getAttribute('class'),
                attributeUrl  = document.getElementById('ceProductAttributes');

            clone.removeAttribute('style');
            clone.removeAttribute('id');
            clone.setAttribute('class', attribute);

            if(previous.id === 'hidden') {
                previous.before(clone);
            } else {
                previous.after(clone);
            }

            previous.setAttribute('class', 'ce-input-extra-data');
            let removeAttributeList = document.querySelectorAll('.ce-button-remove-mapping');
            removeAttributeList.forEach(removeAttribute => {
                removeAttribute.addEventListener('click', function () {
                    if ( removeAttribute.parentNode.parentElement.getAttribute('class').includes('last')) {
                        let baseDiv = document.getElementById('hidden');
                        if (baseDiv.previousElementSibling.previousElementSibling.getAttribute('class') !== 'ce-extra-data-heading') {
                            baseDiv.previousElementSibling.previousElementSibling.setAttribute(
                                'class',
                                baseDiv.previousElementSibling.previousElementSibling.getAttribute('class') + ' last');
                        } else {
                            baseDiv.setAttribute('class', baseDiv.getAttribute('class') + ' last');
                        }
                    }
                    removeAttribute.parentNode.parentElement.remove();
                });
            });

            ChannelEngine.productService.getExtraDataMappingOptions(
                attributeUrl.value,
                clone.firstElementChild.firstElementChild,
                selected
            );

            return clone;
        }
    }

    ChannelEngine.productService = new ProductService();
})();