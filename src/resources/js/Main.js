(function () {
    'use strict';

    function onWindowScroll() {
        const elem = document.querySelector('.channel-engine header');
        if (window.scrollY === 0) {
            elem.classList.remove('scrolling');
        } else {
            elem.classList.add('scrolling');
        }
    }

    document.addEventListener('scroll', onWindowScroll);

    window.ceSetHelp = function (parent) {
        document.querySelectorAll(parent + ' .ce-help').forEach(item => {
            item.addEventListener('mouseover', () => {
                item.classList.add('active');
            });

            item.addEventListener('mouseout', () => {
                item.classList.remove('active');
            });
        });
    }

    ceSetHelp(".channel-engine");

    document.addEventListener(
        'DOMContentLoaded',
        function () {
            ChannelEngine.notificationService.removeNotificationsAndUpdates();
        }
    );

    window.addEventListener(
        'load',
        function () {
            ChannelEngine.loader.hide();
        }
    );
})();
