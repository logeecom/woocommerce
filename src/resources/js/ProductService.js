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
                    shippingTimeOptions = document.getElementById('ceShippingTime'),
                    detailsOptions = document.getElementById('ceDetails'),
                    categoryOptions = document.getElementById('ceCategory'),
                    vendorProductNumberOptions = document.getElementById('ceVendorProductNumber'),
                    standardAttributesLabel = document.getElementById('ce-standard-attributes-label').value,
                    customAttributesLabel = document.getElementById('ce-custom-attributes-label').value,
                    otherFieldsLabel = document.getElementById('ce-other-fields-label').value,
                    standardAttributes = response.product_attributes.standard,
                    customAttributes = response.product_attributes.custom,
                    otherFields = response.product_attributes.other;

                addMapping(standardAttributesLabel, standardAttributes, brandOptions, response.brand);
                addMapping(customAttributesLabel, customAttributes, brandOptions, response.brand);
                addMapping(otherFieldsLabel, otherFields, brandOptions, response.brand);

                addMapping(standardAttributesLabel, standardAttributes, colorOptions, response.color);
                addMapping(customAttributesLabel, customAttributes, colorOptions, response.color);
                addMapping(otherFieldsLabel, otherFields, colorOptions, response.color);

                addMapping(standardAttributesLabel, standardAttributes, sizeOptions, response.size);
                addMapping(customAttributesLabel, customAttributes, sizeOptions, response.size);
                addMapping(otherFieldsLabel, otherFields, sizeOptions, response.size);

                addMapping(standardAttributesLabel, standardAttributes, gtinOptions, response.gtin);
                addMapping(customAttributesLabel, customAttributes, gtinOptions, response.gtin);
                addMapping(otherFieldsLabel, otherFields, gtinOptions, response.gtin);

                addMapping(standardAttributesLabel, standardAttributes, cataloguePriceOptions, response.catalogue_price);
                addMapping(customAttributesLabel, customAttributes, cataloguePriceOptions, response.catalogue_price);
                addMapping(otherFieldsLabel, otherFields, cataloguePriceOptions, response.catalogue_price);

                addMapping(standardAttributesLabel, standardAttributes, priceOptions, response.price);
                addMapping(customAttributesLabel, customAttributes, priceOptions, response.price);
                addMapping(otherFieldsLabel, otherFields, priceOptions, response.price);

                addMapping(standardAttributesLabel, standardAttributes, purchasePriceOptions, response.purchase_price);
                addMapping(customAttributesLabel, customAttributes, purchasePriceOptions, response.purchase_price);
                addMapping(otherFieldsLabel, otherFields, purchasePriceOptions, response.purchase_price);

                addMapping(standardAttributesLabel, standardAttributes, shippingTimeOptions, response.shipping_time);
                addMapping(customAttributesLabel, customAttributes, shippingTimeOptions, response.shipping_time);
                addMapping(otherFieldsLabel, otherFields, shippingTimeOptions, response.shipping_time);

                addMapping(standardAttributesLabel, standardAttributes, detailsOptions, response.details);
                addMapping(customAttributesLabel, customAttributes, detailsOptions, response.details);
                addMapping(otherFieldsLabel, otherFields, detailsOptions, response.details);

                addMapping(standardAttributesLabel, standardAttributes, categoryOptions, response.category);
                addMapping(customAttributesLabel, customAttributes, categoryOptions, response.category);
                addMapping(otherFieldsLabel, otherFields, categoryOptions, response.category);

                addMapping(standardAttributesLabel, standardAttributes, vendorProductNumberOptions, response.vendor_product_number);
                addMapping(customAttributesLabel, customAttributes, vendorProductNumberOptions, response.vendor_product_number);
                addMapping(otherFieldsLabel, otherFields, vendorProductNumberOptions, response.vendor_product_number);

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

        this.getExportProductsEnabled = function (url) {
            const ajaxService = ChannelEngine.ajaxService;
            let enabledExportProducts = document.getElementById('enableExportProducts');

            ajaxService.get(url, function (response) {
                enabledExportProducts.checked = response.exportProducts;
                let productCheckbox = document.getElementById('ce-product-sync-checkbox')

                if (response.exportProducts) {
                    if (productCheckbox) {
                        productCheckbox.removeAttribute('disabled');
                    }

                    ChannelEngine.productService.enableProductSynchronizationFields();
                } else {
                    if (productCheckbox) {
                        productCheckbox.setAttribute('disabled', 'true');
                    }

                    ChannelEngine.productService.disableProductSynchronizationFields()
                }
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
                    const addNewAttribute = document.getElementById('ceAddNewAttribute');
                    if (addNewAttribute.getAttribute('disabled')) {
                        return;
                    }

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

        this.disableProductSynchronizationFields = function () {
            disableStockSynchronizationFields();
            disableAttributeMappingFields();
            disableExtraDataFields();
        }

        this.enableProductSynchronizationFields = function () {
            enableStockSynchronizationFields();
            enableAttributeMappingFields();
            enableExtraDataFields();
        }

        let disableStockSynchronizationFields = function () {
            document.getElementById('enableStockSync').setAttribute('disabled', 'true');
            document.getElementById('ceStockQuantity').setAttribute('disabled', 'true');
            document.getElementById('ceStockQuantity').classList.add('ce-disabled');
            document.getElementById('psc').classList.add('ce-disabled-span');
        }

        let enableStockSynchronizationFields = function () {
            document.getElementById('enableStockSync').removeAttribute('disabled');
            document.getElementById('ceStockQuantity').removeAttribute('disabled');
            document.getElementById('ceStockQuantity').classList.remove('ce-disabled');
            document.getElementById('psc').classList.remove('ce-disabled-span');
        }

        let disableAttributeMappingFields = function () {
            document.getElementById('ceBrand').setAttribute('disabled', 'true');
            document.getElementById('ceColor').setAttribute('disabled', 'true');
            document.getElementById('ceSize').setAttribute('disabled', 'true');
            document.getElementById('ceGtin').setAttribute('disabled', 'true');
            document.getElementById('ceCataloguePrice').setAttribute('disabled', 'true');
            document.getElementById('cePrice').setAttribute('disabled', 'true');
            document.getElementById('cePurchasePrice').setAttribute('disabled', 'true');
            document.getElementById('ceShippingTime').setAttribute('disabled', 'true');
            document.getElementById('ceDetails').setAttribute('disabled', 'true');
            document.getElementById('ceCategory').setAttribute('disabled', 'true');
            document.getElementById('ceVendorProductNumber').setAttribute('disabled', 'true');
        }

        let enableAttributeMappingFields = function () {
            document.getElementById('ceBrand').removeAttribute('disabled');
            document.getElementById('ceColor').removeAttribute('disabled');
            document.getElementById('ceSize').removeAttribute('disabled');
            document.getElementById('ceGtin').removeAttribute('disabled');
            document.getElementById('ceCataloguePrice').removeAttribute('disabled');
            document.getElementById('cePrice').removeAttribute('disabled');
            document.getElementById('cePurchasePrice').removeAttribute('disabled');
            document.getElementById('ceShippingTime').removeAttribute('disabled');
            document.getElementById('ceDetails').removeAttribute('disabled');
            document.getElementById('ceCategory').removeAttribute('disabled');
            document.getElementById('ceVendorProductNumber').removeAttribute('disabled');
        }

        let disableExtraDataFields = function () {
            document.getElementById('ceAddNewAttribute').setAttribute('disabled', 'true');
            const extraDataMappings = document.querySelectorAll('.ce-input-extra-data');

            extraDataMappings.forEach(extraData => {
                let elements = extraData.firstElementChild.children;
                elements.item(0).setAttribute('disabled', 'true');
                elements.item(1).setAttribute('disabled', 'true');
                elements.item(1).classList.add('ce-disabled');
            })
        }

        let enableExtraDataFields = function () {
            document.getElementById('ceAddNewAttribute').removeAttribute('disabled');
            const extraDataMappings = document.querySelectorAll('.ce-input-extra-data');

            extraDataMappings.forEach(extraData => {
                let elements = extraData.firstElementChild.children;
                elements.item(0).removeAttribute('disabled');
                elements.item(1).removeAttribute('disabled');
                elements.item(1).classList.remove('ce-disabled');
            })
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