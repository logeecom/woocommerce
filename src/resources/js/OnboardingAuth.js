var ChannelEngine = window.ChannelEngine || {};

document.addEventListener(
    'DOMContentLoaded',
    function () {
        const ajaxService = ChannelEngine.ajaxService,
            url = document.getElementById('ceAuthUrl'),
            link = document.getElementById('ceAuth');

        link.onclick = () => {
            const apiKey = document.getElementById('ceApiKey'),
                accountName = document.getElementById('ceAccountName');

            ChannelEngine.notificationService.removeNotifications();

            ajaxService.post(
                url.value,
                {
                    apiKey: apiKey.value,
                    accountName: accountName.value
                },
                function (response) {
                    if (response.success) {
                        window.location.reload();
                    } else {
                        ChannelEngine.notificationService.addNotification(response.message);
                    }
                });
        }
    }
);