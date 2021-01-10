$(() => {
    if (!document.getElementById('marketing-page')) {
        return false;
    }

    const selectReseller = document.getElementById('mr-reseller-id');
    const elBalance = document.getElementById('reseller-balance');
    const chTopup = document.getElementById('mr-topup');
    const elTopupContainer = document.getElementById('topup-container');

    function bindAllElements() {
        $(chTopup).on('change', () => {
            updateBalance();
        });

        $(selectReseller).on('change', () => {
            updateBalance()
        });
    }

    function updateBalance() {
        elBalance.innerText = selectReseller.options[selectReseller.selectedIndex].dataset.balance.toLocaleString() + ' Ft';

        if (chTopup.checked) {
            $(elTopupContainer).slideDown();
        } else {
            $(elTopupContainer).slideUp();
        }
    }

    function init() {
        bindAllElements();
        updateBalance();
    }

    init();
});