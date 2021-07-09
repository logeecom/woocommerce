var ChannelEngine = window.ChannelEngine || {};

document.addEventListener(
    'DOMContentLoaded',
    function () {
        const ajaxService = ChannelEngine.ajaxService,
            url = document.getElementById('ce-check-status'),
            productProgress = document.getElementById('ce-product-progress'),
            productProgressBar = document.getElementById('ce-product-progress-bar'),
            productTotal = document.getElementById('ce-product-total'),
            productSynced = document.getElementById('ce-product-synced'),
            orderProgress = document.getElementById('ce-order-progress'),
            orderProgressBar = document.getElementById('ce-order-progress-bar'),
            orderTotal = document.getElementById('ce-order-total'),
            orderSynced = document.getElementById('ce-order-synced');

        checkStatus();

        function checkStatus() {
            ajaxService.get(url.value, function (response) {
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
                    orderTotal.innerHTML = response.order_sync.total;
                    productTotal.innerHTML = response.product_sync.total;

                    setTimeout(checkStatus, 1000);
                }
            });
        }
    }
);