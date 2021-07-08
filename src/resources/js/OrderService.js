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
                        mappings = response.order_statuses;

                    mappings.forEach(item => addMappings(item, incomingOptions, response.incoming));
                    mappings.forEach(item => addMappings(item, shippedOptions, response.shipped));
                    mappings.forEach(item => addMappings(item, fulfilledByMpOptions, response.fulfilledByMp));
                }
            );

            function addMappings (item, parent, mapping) {
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