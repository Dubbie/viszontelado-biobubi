<div class="modal fade" id="deliveryNotificationModal" tabindex="-1" aria-labelledby="deliveryNotificationModal" aria-hidden="true">
    <form action="{{ route('worksheet.orders.notify-delivery') }}" method="POST">
        @csrf
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Értesítő levél</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modal-body-content">
                    <div id="mail-recipients" class="dn-step" data-step="1" style="display: none">
                        {{-- Lista a megrendelésekről, pipával --}}
                        <p class="h5 font-weight-bold mb-4">Kérlek válaszd ki, kik kapjanak értesítőt</p>

                        <div id="notification-worksheets-container"></div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-muted" data-dismiss="modal">Vissza</button>
                            <button type="button" class="btn btn-success dn-stepper dn-step-next" disabled="disabled">Tovább</button>
                        </div>
                    </div>

                    <div id="mail-content" class="dn-step" data-step="2" style="display: none">
                        {{-- Levél tartalma --}}
                        <label for="notification-body" class="h5 font-weight-bold mb-0">Levél tartalma</label>
                        <textarea name="notification-body" id="notification-body" cols="30" rows="10" class="form-control">Azért írok, mert a holnapi napon, kollégám viszi neked a Biobubis megrendelésedet!

9-17 között fog érkezni de előtte mindenképp telefonálni fog!

Ha esetleg bizonytalan az otthonléted, akkor azzal is számolhatsz, hogy le tudjuk tenni az ajtód elé/kapuba vagy kapun belülre a csomagodat, amennyiben ez nálad megoldható. Az sem baj, ha még nem fizetted a csomagot, ebben az esetben egy utólagos átutalásos számlát tudunk küldeni számodra!

Bármilyen kérdéssel fordulhatsz hozzánk itt válaszlevélben!
                        </textarea>

                        <p class="h5 font-weight-bold mt-4 mb-0">Minta levél</p>
                        <p class="mb-4 text-muted">A <b>félkövér</b> adatok behelyettesítésre kerülnek a megrendelésben található adatokkal.</p>

                        <div class="bg-info-pastel p-2 mb-4">
                            <p>Kedves <b>{ Megrendelő neve }</b>!</p>

                            <p id="example-notification" x-bind:innerText="notificationText"></p>

                            <p class="mb-0">Üdvözlettel,<br>Balázs a BioBubitól</p>
                        </div>

                        <p class="font-weight-bold h5 mb-2">Címzettek:</p>

                        <ul id="dn-recipients" class="list-unstyled"></ul>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-muted dn-stepper dn-step-prev">Vissza</button>
                            <button type="submit" class="btn btn-success dn-stepper">Küldés</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

