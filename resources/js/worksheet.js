$(() => {
    const worksheetModal = document.getElementById('worksheetModal');
    const notificationModal = document.getElementById('deliveryNotificationModal');
    if (!worksheetModal) {
        return;
    }

    let step = 1;
    let selectedOrders = [];
    const groups = document.querySelectorAll('.dn-step');
    const notificationWorksheetsContainer = document.getElementById('notification-worksheets-container');
    const dnStepPrev = document.querySelectorAll('.dn-step-prev');
    const dnStepNext = document.querySelectorAll('.dn-step-next');
    const txtNotificationBody = document.getElementById('notification-body');
    const elExampleNotification = document.getElementById('example-notification');
    const elRecipients = document.getElementById('dn-recipients');

    function showCorrectStep() {
        for (const el of groups) {
            if (parseInt(el.dataset.step) === step) {
                $(el).show();
            } else {
                $(el).hide();
            }
        }
    }

    function refreshWorksheetOrders() {
        const loadingP = document.createElement('p');
        loadingP.innerText = "Loading...";
        notificationWorksheetsContainer.appendChild(loadingP);

        axios.get('/munkalap/megrendelesek/html').then(response => {
            notificationWorksheetsContainer.innerHTML = response.data;
        });
    }

    function handleBinding() {
        $(notificationModal).on('show.bs.modal', () => {
            console.log('Notification is showing');
            showCorrectStep();
            refreshWorksheetOrders();
            updateButtons();
            copyNotificationToExample();
            $(worksheetModal).modal('hide');
        });

        $(notificationModal).on('hide.bs.modal', () => {
            console.log('Notification is hiding');
            $(worksheetModal).modal('show');
        });

        $(dnStepPrev).on('click', e => {
            e.preventDefault();
            step--;
            showCorrectStep();
        });

        $(dnStepNext).on('click', e => {
            e.preventDefault();
            step++;
            showCorrectStep();
        });

        $(document).on('change', '.ch-notification-order-select', (e) => {
            const orderId = e.currentTarget.dataset.orderId;
            const container = e.currentTarget.closest('.dno-row');
            const orderEntry = {
                'id': orderId,
                'name': container.querySelector('.dno-name').innerText,
                'email': container.querySelector('.dno-email').innerText,
                'address': container.querySelector('.dno-address').innerText,
            };

            if (e.currentTarget.checked && !selectedOrders.includes(orderEntry)) {
                selectedOrders.push(orderEntry);
            } else if (!e.currentTarget.checked && selectedOrders.indexOf(orderEntry) !== -1) {
                selectedOrders.splice(selectedOrders.indexOf(orderEntry), 1);
            }

            console.log(selectedOrders);
            updateButtons();
            updateMailRecipients();
        });

        $(txtNotificationBody).on('keyup', () => {
            copyNotificationToExample();
        });
    }

    function updateButtons() {
        const checked = document.querySelectorAll('.ch-notification-order-select:checked').length;
        const currentNextButton = document.querySelector('.dn-step[data-step="' + step + '"] .dn-step-next');
        currentNextButton.disabled = checked === 0;
    }

    function copyNotificationToExample() {
        elExampleNotification.innerText = txtNotificationBody.value;
    }

    function updateMailRecipients() {
        while (elRecipients.lastChild) {
            elRecipients.removeChild(elRecipients.lastChild);
        }

        for (const entry of selectedOrders) {
            const elLi = document.createElement('li');

            const nameP = document.createElement('p');
            nameP.innerText = entry.name;
            nameP.classList.add('mb-0', 'font-weight-bold');

            const mailP = document.createElement('p');
            mailP.innerText = entry.email;
            mailP.classList.add('mb-0', 'text-muted');

            const addressP = document.createElement('p');
            addressP.innerText = entry.address;
            addressP.classList.add('mb-0', 'text-muted');

            elLi.appendChild(nameP);
            elLi.appendChild(mailP);
            elLi.appendChild(addressP);

            elRecipients.appendChild(elLi);
        }
    }

    function init() {
        console.log('Worksheet JavaScript module loaded');
        handleBinding();
    }

    init();
});
