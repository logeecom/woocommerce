if (!window.ChannelEngine) {
    window.ChannelEngine = {};
}

(function () {
    function ModalService() {
        this.showModal = function (header, content, buttonText, callback) {
            let node = document.createElement('div'),
                modal = document.getElementById('ce-modal'),
                modalContent = modal.getElementsByTagName('MAIN')[0],
                modalHeader = modal.getElementsByTagName('H3')[0],
                modalButton = modal.getElementsByTagName('BUTTON')[0],
                closeButton = document.getElementsByClassName('ce-close-modal')[0],
                disableSwitch = document.getElementById('ce-disable-switch');
            modalHeader.innerHTML = header;
            modalButton.onclick = callback;
            modalButton.innerHTML = buttonText;
            closeButton.onclick = function () {
                if (disableSwitch) {
                    disableSwitch.checked = true;
                }
                modal.style.display = "none";
            }
            node.innerHTML = content;
            modalContent.removeChild(modalContent.firstChild);
            modalContent.append(node);
            modal.style.display = "block";
        };
    }

    ChannelEngine.modalService = new ModalService();
})();