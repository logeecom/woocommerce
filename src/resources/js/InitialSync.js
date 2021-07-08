var ChannelEngine = window.ChannelEngine || {};

document.addEventListener(
    'DOMContentLoaded',
    function () {
        const ajaxService = ChannelEngine.ajaxService,
            url = document.getElementById('ceInitialSyncUrl'),
            link = document.getElementById('ceStartSync');

        ChannelEngine.notificationService.removeNotifications();

        link.onclick = () => {
            ajaxService.get(
                url.value,
                function (response) {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        ChannelEngine.notificationService.addNotification(response.message);
                    }
                }
            );
        }
    }
);