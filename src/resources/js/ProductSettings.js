var ChannelEngine = window.ChannelEngine || {};

document.addEventListener(
    'DOMContentLoaded',
    function () {
        const url = document.getElementById('ceProductSave'),
            link = document.getElementById('ceSave');

        link.onclick = () => {
            const quantity = document.getElementById('ceStockQuantity');

            ChannelEngine.notificationService.removeNotifications();
            ChannelEngine.productService.save(url.value, {quantity: quantity.value});
        }
    }
);