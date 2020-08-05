$(() => {
    const btnTestBillingoApi = document.getElementById('btn-billingo-api-test');
    if (!btnTestBillingoApi) {
        return;
    }

    const form = document.getElementById('user-form');
    const inputBillingoApiKey = document.getElementById('u-billingo-api-key');
    const inputBillingoBlockUid = document.getElementById('u-block-uid');
    const billingoResults = document.getElementById('billingo-test-results');

    /**
     * Visszaállítja alaphelyzetbe a Billingo bemeneteket.
     */
    function resetBillingoInputs() {
        inputBillingoApiKey.classList.remove('is-invalid', 'is-valid');
        inputBillingoBlockUid.classList.remove('is-invalid', 'is-valid');

        billingoResults.classList.remove('alert-success', 'alert-danger');
        $(billingoResults).slideUp();
        while (billingoResults.lastChild) {
            billingoResults.removeChild(billingoResults.lastChild);
        }
    }

    /**
     * Átalakítja a gomb szövegét a feldolgozás alatti szövegre.
     *
     * @param btn
     * @param loading
     */
    function buttonLoading(btn, loading = true) {
        const $btnLoader = $(btn).find('.loading');
        const $btnText = $(btn).find('.text');

        if (loading) {
            disableButton(btn);
            $btnText.hide();
            $btnLoader.show();
        } else {
            disableButton(btn, false);
            $btnLoader.hide();
            $btnText.show();
        }
    }

    /**
     * Leellenőrzi, hogy minden adat megvan-e adva, ha igen akkor bekapcsolja a gombot.
     */
    function checkBillingoInputs() {
        if (inputBillingoBlockUid.value.length > 0 &&
            inputBillingoApiKey.value.length > 0) {
            disableButton(btnTestBillingoApi, false);
        } else {
            disableButton(btnTestBillingoApi);
        }
    }

    /**
     * Inaktívvá tesz egy gombot a paramétertől függően.
     *
     * @param btn
     * @param disable
     */
    function disableButton(btn, disable = true) {
        if (disable) {
            btn.disabled = true;
            btn.classList.add('disabled');
        } else {
            btn.disabled = false;
            btn.classList.remove('disabled');
        }
    }

    /**
     * Ha módosítja a mezőket, akkor reseteljünk.
     */
    $('#u-billingo-public-key, #u-billingo-private-key, #u-block-uid').on('keyup', () => {
        resetBillingoInputs();

        // Kapcsoljuk be a gombot ha minden adatot megadott a jómadár
        checkBillingoInputs();
    });

    /**
     * Bebindeljük amit kell.
     */
    $(btnTestBillingoApi).on('click', () => {
        const formData = new FormData(form);
        formData.delete('_method');

        resetBillingoInputs();

        buttonLoading(btnTestBillingoApi);
        fetch('/api/billingo/test', {
            method: 'POST',
            body: formData,
        }).then(response => response.json()).then(json => {
            const p = document.createElement('p');
            p.classList.add('alert', 'alert-info', 'mb-0');
            p.innerText = json.success ? 'Csatlakozás sikeres' : 'Csatlakozás sikertelen';

            billingoResults.appendChild(p);

            $(billingoResults).slideDown();
        }).finally(() => {
            buttonLoading(btnTestBillingoApi, false);
        });
    });

    /**
     * Form küldéskor betöltő gomb.
     */
    $(form).on('submit', () => {
        const btnSubmitForm = $(form).find('button[type="submit"]')[0];
        buttonLoading(btnSubmitForm);
    });

    // Alapból tiltsuk le a gombunkat
    checkBillingoInputs();
});