if (!window.ChannelEngine) {
    window.ChannelEngine = {};
}

(function () {
    function ProductService() {
        this.get = function (url) {
            const ajaxService = ChannelEngine.ajaxService,
                quantity = document.getElementById('ceStockQuantity');

            ajaxService.get(url, function (response) {
                quantity.value = response.stockQuantity;
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
    }

    ChannelEngine.productService = new ProductService();
})();