var ChannelEngine = window.ChannelEngine || {};

document.addEventListener(
    'DOMContentLoaded',
    function () {
        const getUrl = document.getElementById('ceStatusesUrl'),
            saveUrl = document.getElementById('ceStatusesSaveUrl'),
            link = document.getElementById('ceStatusesSave');

        link.onclick = () => {
            const incoming = document.getElementById('ceIncomingOrders'),
                shipped = document.getElementById('ceShippedOrders'),
                fulfilledByMp = document.getElementById('ceFulfilledByMp');
            ChannelEngine.notificationService.removeNotifications();

            ChannelEngine.orderService.save(
                saveUrl.value,
                {
                    incoming: incoming.value,
                    shipped: shipped.value,
                    fulfilledByMp: fulfilledByMp.value
                }
            );
        }

        ChannelEngine.orderService.get(getUrl.value);
    }
);