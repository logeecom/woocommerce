var ChannelEngine = window.ChannelEngine || {};

document.addEventListener(
    'DOMContentLoaded',
    function () {
        let updateInfo = document.getElementById('ce-update-info'),
            createEndpoint = document.getElementById('ce-create-endpoint'),
            loader = document.getElementById('ce-loader'),
            page = document.getElementById('ce-track-and-trace-content'),
            checkShipmentSyncStatus = document.getElementById('ceSyncShipmentStatusUrl');

        ChannelEngine.ajaxService.get(checkShipmentSyncStatus.value, function (response) {
            if ( ! response.enableShipmentInfoSync) {
                let nodes = page.getElementsByTagName('*');
                for(let i = 0; i < nodes.length; i++) {
                    nodes[i].disabled = true;
                    nodes[i].style = 'opacity: 0.4';
                }
                updateInfo.setAttribute('disabled', 'true');
            }

            loader.style.display = 'none';
        });

        if (updateInfo) {
            updateInfo.addEventListener(
                'click',
                function (event) {
                    event.preventDefault();

                    loader.style.display = 'block';
                    page.style.display = 'none';

                    let trackAndTrace = document.getElementById('ce-track-and-trace'),
                        shippingMethod = document.getElementById('ce-shipping-methods'),
                        postId = document.getElementById('ce-post-id');

                    ChannelEngine.ajaxService.post(
                        createEndpoint.value,
                        {
                            trackAndTrace: trackAndTrace.value,
                            shippingMethod: shippingMethod.value,
                            postId: postId.value
                        },
                        function (response) {
                            if (!response.success) {
                                let error = document.getElementById('ce-shipment-error'),
                                    errorMessage = document.getElementById('ce-shipment-error-description');

                                errorMessage.innerHTML = response.message;
                                error.style.display = "";
                            }

                            loader.style.display = 'none';
                            page.style.display = 'flex';
                        }
                    );
                }
            )
        }
    }
);