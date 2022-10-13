var ChannelEngine = window.ChannelEngine || {};

document.addEventListener(
    'DOMContentLoaded',
    function () {
        const ajaxService = ChannelEngine.ajaxService,
            url = document.getElementById('ceInitialSyncUrl'),
            link = document.getElementById('ceStartSync'),
            getAccountUrl = document.getElementById('ceGetAccountName');

        ChannelEngine.disconnectService.getAccountName(getAccountUrl);
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

document.getElementById('stepToProductSettings').addEventListener(
    'click',
    function (e) {
        e.preventDefault();
        const ajaxService = ChannelEngine.ajaxService,
            url = document.getElementById('ceSwitchOnboardingPage');
        ajaxService.post(
            url.value,
            {
                'page': 'product_configuration'
            },
            function (response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    ChannelEngine.notificationService.addNotification(response.message);
                }
            }
        )
    }
);

document.getElementById('stepToOrderSettings').addEventListener(
    'click',
    function (e) {
        e.preventDefault();
        const ajaxService = ChannelEngine.ajaxService,
            url = document.getElementById('ceSwitchOnboardingPage');
        ajaxService.post(
            url.value,
            {
                'page': 'order_status_mapping'
            },
            function (response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    ChannelEngine.notificationService.addNotification(response.message);
                }
            }
        )
    }
);