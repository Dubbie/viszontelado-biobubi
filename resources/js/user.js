$(() => {
    const btnTestBillingoApi = document.getElementById('btn-billingo-api-test');
    if (!btnTestBillingoApi) {
        return;
    }

    const form = document.getElementById('user-form');
    const inputBillingoPublicKey = document.getElementById('u-billingo-public-key');
    const inputBillingoPrivateKey = document.getElementById('u-billingo-private-key');
    const inputBillingoBlockUid = document.getElementById('u-block-uid');
    const billingoResults = document.getElementById('billingo-test-results');

    /**
     * Visszaállítja alaphelyzetbe a Billingo bemeneteket.
     */
    function resetBillingoInputs() {
        inputBillingoPublicKey.classList.remove('is-invalid', 'is-valid');
        inputBillingoPrivateKey.classList.remove('is-invalid', 'is-valid');
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
            inputBillingoPrivateKey.value.length > 0 &&
            inputBillingoPublicKey.value.length > 0) {
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
            // Validálási hiba lekezelése
            if (json.errors) {
                for (let [inputId, errorList] of Object.entries(json.errors)) {
                    const input = document.getElementById(inputId);
                    const feedbacks = $(input).closest('.form-group').find('.invalid-feedback')[0];
                    input.classList.add('is-invalid');

                    // Kitöröljük a régi üzeneteket
                    while (feedbacks.lastChild) {
                        feedbacks.removeChild(feedbacks.lastChild);
                    }

                    // Végigmegyünk az üzeneteken és hozzáadjuk a listához
                    for (const msg of errorList) {
                        const el = document.createElement('p');
                        el.classList.add('mb-0');
                        el.innerText = msg;

                        feedbacks.appendChild(el);
                    }
                }

                return;
            }

            // Nem történt validálási hiba
            if (!json.success) {
                if (json.correctInputs.length === 0) {
                    inputBillingoPrivateKey.classList.add('is-invalid');
                    inputBillingoPublicKey.classList.add('is-invalid');
                } else {
                    inputBillingoBlockUid.classList.add('is-invalid');
                }

                billingoResults.classList.add('alert-danger');
            } else {
                billingoResults.classList.add('alert-success');
            }

            // Helyes inputok
            for (const inputId of json.correctInputs) {
                document.getElementById(inputId).classList.add('is-valid');
            }

            // Üzeneteket hozzáadjuk
            for (const msg of json.messages) {
                const p = document.createElement('p');
                p.classList.add('mb-0');
                p.innerText = msg;

                billingoResults.appendChild(p);
            }

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