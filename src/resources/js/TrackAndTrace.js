var ChannelEngine = window.ChannelEngine || {};

document.addEventListener(
    'DOMContentLoaded',
    function () {
        let updateInfo = document.getElementById('ce-update-info'),
            createEndpoint = document.getElementById('ce-create-endpoint'),
            loader = document.getElementById('ce-loader'),
            page = document.getElementById('ce-track-and-trace-content');

        loader.style.display = 'none';
        page.style.display = 'flex';

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