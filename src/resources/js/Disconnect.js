var ChannelEngine = window.ChannelEngine || {};

document.getElementById('ceDisconnectLink').addEventListener(
    'click',
    function (e) {
        e.preventDefault()
        let disconnectUrl = document.getElementById('ce-disconnect-url');

        let disconnect = function () {
            ChannelEngine.ajaxService.get(
                disconnectUrl.value,
                function (response) {
                    window.location.assign(window.location.href.replace(window.location.search, '?page=channel-engine'));
                }
            )
        }

        ChannelEngine.modalService.showModal(
            'Disconnect account',
            '<div>' +
            '<label>' + 'You are about to disconnect your ChannelEngine account.' + '</label>' +
            '</div>',
            'Disconnect',
            disconnect
        );
    }
);