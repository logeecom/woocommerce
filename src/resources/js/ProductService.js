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

                if (!enabledStockSync.checked) {
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
                    standardAttributesLabel = document.getElementById('ce-standard-attributes-label').value,
                    customAttributesLabel = document.getElementById('ce-custom-attributes-label').value,
                    standardAttributes = response.product_attributes.standard,
                    customAttributes = response.product_attributes.custom;

                addMapping(standardAttributesLabel, standardAttributes, brandOptions, response.brand);
                addMapping(customAttributesLabel, customAttributes, brandOptions, response.brand);
                addMapping(standardAttributesLabel, standardAttributes, colorOptions, response.color);
                addMapping(customAttributesLabel, customAttributes, colorOptions, response.color);
                addMapping(standardAttributesLabel, standardAttributes, sizeOptions, response.size);
                addMapping(customAttributesLabel, customAttributes, sizeOptions, response.size);
                addMapping(standardAttributesLabel, standardAttributes, gtinOptions, response.gtin);
                addMapping(customAttributesLabel, customAttributes, gtinOptions, response.gtin);
                addMapping(standardAttributesLabel, standardAttributes, cataloguePriceOptions, response.catalogue_price);
                addMapping(customAttributesLabel, customAttributes, cataloguePriceOptions, response.catalogue_price);
                addMapping(standardAttributesLabel, standardAttributes, priceOptions, response.price);
                addMapping(customAttributesLabel, customAttributes, priceOptions, response.price);
                addMapping(standardAttributesLabel, standardAttributes, purchasePriceOptions, response.purchase_price);
                addMapping(customAttributesLabel, customAttributes, purchasePriceOptions, response.purchase_price);
                addMapping(standardAttributesLabel, standardAttributes, detailsOptions, response.details);
                addMapping(customAttributesLabel, customAttributes, detailsOptions, response.details);
                addMapping(standardAttributesLabel, standardAttributes, categoryOptions, response.category);
                addMapping(customAttributesLabel, customAttributes, categoryOptions, response.category);
            });
        }

        this.getExtraDataMappingOptions = function (url, element, selected) {
            const ajaxService = ChannelEngine.ajaxService,
                standardAttributesLabel = document.getElementById('ce-standard-attributes-label').value,
                customAttributesLabel = document.getElementById('ce-custom-attributes-label').value;

            ajaxService.get(url, function (response) {
                addMapping(standardAttributesLabel, response.product_attributes.standard, element, selected);
                addMapping(customAttributesLabel, response.product_attributes.custom, element, selected);
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
                attributeUrl = document.getElementById('ceProductAttributes');

            clone.removeAttribute('style');
            clone.removeAttribute('id');
            clone.setAttribute('class', attribute);

            if (previous.id === 'hidden') {
                previous.before(clone);
            } else {
                previous.after(clone);
            }

            previous.setAttribute('class', 'ce-input-extra-data');
            let removeAttributeList = document.querySelectorAll('.ce-button-remove-mapping');
            removeAttributeList.forEach(removeAttribute => {
                removeAttribute.addEventListener('click', function () {
                    if (removeAttribute.parentNode.parentElement.getAttribute('class').includes('last')) {
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

        function addMapping(attributesTypeLabel, attributes, parent, mapping) {
            const group = document.createElement('OPTGROUP');
            group.label = attributesTypeLabel;
            attributes.forEach(item => addOption(item, group, mapping));
            parent.appendChild(group);
        }

        function addOption(item, parent, mapping) {
            const option = document.createElement('OPTION');

            option.innerHTML = item.label;
            option.value = item.value;
            if (item.value === mapping) {
                option.selected = true;
            }

            parent.appendChild(option);
        }
    }

    ChannelEngine.productService = new ProductService();
})();