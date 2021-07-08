var ChannelEngine = window.ChannelEngine || {};

document.addEventListener(
    'DOMContentLoaded',
    function () {
        const ajaxService = ChannelEngine.ajaxService,
            url = document.getElementById('ce-check-status'),
            countUrl = document.getElementById('ce-total-count'),
            productProgress = document.getElementById('ce-product-progress'),
            productProgressBar = document.getElementById('ce-product-progress-bar'),
            productTotal = document.getElementById('ce-product-total'),
            productSynced = document.getElementById('ce-product-synced'),
            orderProgress = document.getElementById('ce-order-progress'),
            orderProgressBar = document.getElementById('ce-order-progress-bar'),
            orderTotal = document.getElementById('ce-order-total'),
            orderSynced = document.getElementById('ce-order-synced');

        getTotal();
        checkStatus();

        function checkStatus() {
            ajaxService.get(url.value, function (response) {
                if (response.product_sync.status === 'failed' || response.order_sync.status === 'failed') {
                    // display notification view
                }

                if (response.product_sync.status === 'completed' && response.order_sync.status === 'completed') {
                    window.location.reload();
                } else {
                    productProgress.innerHTML = response.product_sync.progress + '%';
                    productProgressBar.style.clipPath = 'inset(0 0 0 ' + response.product_sync.progress + '%)';
                    productProgressBar.innerHTML = response.product_sync.progress + '%';
                    productSynced.innerHTML = response.product_sync.synced;
                    orderProgress.innerHTML = response.order_sync.progress + '%';
                    orderProgressBar.style.clipPath = 'inset(0 0 0 ' + response.order_sync.progress + '%)';
                    orderProgressBar.innerHTML = response.order_sync.progress + '%';
                    orderSynced.innerHTML = response.order_sync.synced;

                    setTimeout(checkStatus, 1000);
                }
            });
        }

        function getTotal() {
            ajaxService.get(countUrl.value, function (response) {
                orderTotal.innerHTML = response.orders_total;
                productTotal.innerHTML = response.products_total;
            });
        }
    }
);