var ChannelEngine = window.ChannelEngine || {};

document.addEventListener(
    'DOMContentLoaded',
    function () {
        const url = document.getElementById('ce-transactions-get'),
            table = document.getElementById('ce-table-body'),
            logsFrom = document.getElementById('ce-logs-from'),
            logsTo = document.getElementById('ce-logs-to'),
            logsTotal = document.getElementById('ce-logs-total'),
            viewDetailsTranslation = document.getElementById('ce-view-details-translation'),
            startTranslation = document.getElementById('ce-start-translation'),
            completedTranslation = document.getElementById('ce-completed-translation'),
            pagination = document.getElementsByClassName('ce-pagination-pages')[0],
            nextPage = document.getElementsByClassName('ce-button__next')[0],
            prevPage = document.getElementsByClassName('ce-button__prev')[0],
            pageSize = document.getElementById('ce-page-size'),
            detailsUrl = document.getElementById('ce-details-get');

        getPage(1);

        function getPage(page) {
            let taskType = '',
                status = '',
                current = document.getElementsByClassName('ce-current')[0];

            switch (current.id) {
                case 'ce-product-link':
                    taskType = 'ProductSync';
                    break;
                case 'ce-order-link':
                    taskType = 'OrderSync';
                    break;
                case 'ce-errors-link':
                    status = true;
            }

            ChannelEngine.ajaxService.get(
                url.value + '&page=' + page
                + '&page_size=' + pageSize.value + '&task_type=' + taskType + '&status=' + status,
                renderPage
            );
        }

        function renderPage(response) {
            removeData();
            logsTotal.innerHTML = response.numberOfLogs;
            logsFrom.innerHTML = response.from;
            logsTo.innerHTML = response.to;

            renderFilters(response);
            renderPagination(response);

            pageSize.onchange = function () {
                getPage(1);
            }

            if (response.logs.length === 0) {
                let row = document.createElement('TR'),
                    noResults = document.getElementById('ce-no-results');

                row.innerHTML = noResults.value;
                row.style.display = "block";
                row.style.padding = "0.75rem";
                table.append(row);

                return;
            }

            response.logs.forEach(item => addRow(item));
        }

        function renderPagination(response) {
            nextPage.onclick = function () {
                getPage(response.currentPage + 1);
            }

            prevPage.onclick = function () {
                getPage(response.currentPage - 1);
            }

            prevPage.disabled = response.currentPage === 1;
            nextPage.disabled = response.currentPage === response.numberOfPages || response.numberOfPages === 0;

            if (response.numberOfPages < 8) {
                for (let i = 1; i <= response.numberOfPages; i++) {
                    renderPaginationButton(i, response);
                }
            } else {
                if (response.currentPage === 1) {
                    for (let i = response.currentPage; i <= response.currentPage + 2; i++) {
                        renderPaginationButton(i, response);
                    }
                } else if (response.currentPage < response.numberOfPages - 4) {
                    for (let i = response.currentPage - 1; i <= response.currentPage + 1; i++) {
                        renderPaginationButton(i, response);
                    }
                } else {
                    for (let i = response.numberOfPages - 5; i <= response.numberOfPages - 3; i++) {
                        renderPaginationButton(i, response);
                    }
                }

                renderPaginationButton('...', response);

                for (let i = response.numberOfPages - 2; i <= response.numberOfPages; i++) {
                    renderPaginationButton(i, response);
                }
            }
        }

        function renderPaginationButton(i, response) {
            let paginationBtn = document.createElement('BUTTON');
            paginationBtn.innerHTML = i;
            paginationBtn.classList.add('ce-button', 'ce-button__page');
            if (i !== '...') {
                paginationBtn.onclick = function () {
                    getPage(i);
                }
            }

            if (i === response.currentPage) {
                paginationBtn.classList.add('ce-active');
            }

            pagination.insertBefore(paginationBtn, nextPage);
        }

        function addRow(log) {
            let row = document.createElement('TR'),
                taskType = document.createElement('TD'),
                status = document.createElement('TD'),
                statusSpan = document.createElement('SPAN'),
                compact = document.createElement('TD'),
                viewButton = document.createElement('BUTTON'),
                startTime = document.createElement('TD'),
                timeCompleted = document.createElement('TD'),
                viewDetails = document.createElement('TD'),
                button = document.createElement('BUTTON');


            taskType.innerHTML = log.taskType;
            status.classList.add('text-center');
            statusSpan.classList.add('ce-status');

            if (log.status === 'Completed') {
                statusSpan.classList.add('ce-status__success');
            }

            if (['Pending'].includes(log.status)) {
                statusSpan.classList.add('ce-status__info');
            }

            if (['Has errors'].includes(log.status)) {
                statusSpan.classList.add('ce-status__error');
            }

            if (['Partially completed'].includes(log.status)) {
                statusSpan.classList.add('ce-status__warning');
            }

            statusSpan.innerHTML = log.status;
            status.append(statusSpan);

            compact.classList.add('ce-table-compact-view')
            compact.append(getCompactView(log));
            if (log.hasDetails) {
                viewButton.classList.add('ce-open-modal', 'ce-button', 'ce-button__primary');
                viewButton.innerHTML = viewDetailsTranslation.value;
                viewButton.onclick = function () {
                    showDetails(log.id);
                }
                compact.append(viewButton);
            }
            startTime.innerHTML = log.startTime;
            startTime.classList.add('text-center', 'ce-table-full-view');
            timeCompleted.innerHTML = log.completedTime;
            timeCompleted.classList.add('text-center', 'ce-table-full-view');
            row.append(taskType);
            row.append(status);
            row.append(compact);
            row.append(startTime);
            row.append(timeCompleted);
            viewDetails.classList.add('text-center', 'ce-table-full-view');

            if (log.hasDetails) {
                button.innerHTML = viewDetailsTranslation.value;
                button.classList.add('ce-open-modal', 'ce-button', 'ce-button__link');
                button.onclick = function () {
                    showDetails(log.id);
                }
                viewDetails.append(button);
            }
            row.append(viewDetails);

            table.append(row);
        }

        function getCompactView(log) {
            let table = document.createElement('DL'),
                startTime = document.createElement('DT'),
                start = document.createElement('DD'),
                timeCompleted = document.createElement('DT'),
                completed = document.createElement('DD');

            startTime.innerHTML = startTranslation.value;
            start.innerHTML = log.startTime;
            timeCompleted.innerHTML = completedTranslation.value;
            completed.innerHTML = log.completedTime;

            table.append(startTime);
            table.append(start);
            table.append(timeCompleted);
            table.append(completed);

            return table;
        }

        function showDetails(logId) {
            let modalHeader = document.getElementById('ce-modal-header'),
                buttonText = document.getElementById('ce-modal-button-text');

            ChannelEngine.ajaxService.get(
                detailsUrl.value + '&log_id=' + logId,
                function (response) {
                    ChannelEngine.modalService.showModal(
                        modalHeader.value,
                        ChannelEngine.details.getContent(response),
                        buttonText.value,
                        closeModal
                    );

                    ChannelEngine.details.addListeners();
                }
            )
        }

        function closeModal() {
            let modal = document.getElementById('ce-modal');

            modal.style.display = "none";
        }

        function removeData() {
            let numberOfChildren = pagination.children.length;

            for (let i = numberOfChildren - 2; i > 0; i--) {
                pagination.removeChild(pagination.children[i]);
            }

            table.innerHTML = '';
        }

        function renderFilters(response) {
            let productLink = document.getElementById('ce-product-link'),
                orderLink = document.getElementById('ce-order-link'),
                errorsLink = document.getElementById('ce-errors-link');

            productLink.onclick = function () {
                addClassToCurrentFilter('ProductSync');
                getPage(1);
            }

            orderLink.onclick = function () {
                addClassToCurrentFilter('OrderSync');
                getPage(1);
            }

            errorsLink.onclick = function () {
                addClassToCurrentFilter('Errors');
                getPage(1);
            }

            addClassToCurrentFilter(response.taskType);
        }

        function addClassToCurrentFilter(current) {
            let productLink = document.getElementById('ce-product-link'),
                orderLink = document.getElementById('ce-order-link'),
                errorsLink = document.getElementById('ce-errors-link');

            switch (current) {
                case 'ProductSync':
                    productLink.classList.add('ce-current');
                    orderLink.classList.remove('ce-current');
                    errorsLink.classList.remove('ce-current');
                    break;
                case 'OrderSync':
                    orderLink.classList.add('ce-current');
                    productLink.classList.remove('ce-current');
                    errorsLink.classList.remove('ce-current');
                    break;
                case 'Errors':
                    errorsLink.classList.add('ce-current');
                    productLink.classList.remove('ce-current');
                    orderLink.classList.remove('ce-current');
            }
        }
    }
);