if (!window.ChannelEngine) {
    window.ChannelEngine = {};
}

(function () {
    function DisconnectService() {

        this.getAccountName = function (url) {
            const ajaxService = ChannelEngine.ajaxService;
            ajaxService.get(
                url.value,
                function (response) {
                    let accountName = document.getElementById('ceAccountNameHeader');
                    accountName.innerText = response.accountName;
                });
        }
    }

    ChannelEngine.disconnectService = new DisconnectService();
})();