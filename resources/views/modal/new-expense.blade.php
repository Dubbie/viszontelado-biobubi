<!-- Modal -->
<div class="modal fade" id="newExpenseModal" tabindex="-1" role="dialog" aria-labelledby="newExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ action('RevenueController@storeExpense') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="newExpenseModalLabel">Új kiadás felvétele</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="e-name">Kiadás megnevezése</label>
                        <select name="e-name" id="e-name" class="custom-select">
                            <option value="Benzin">Benzin</option>
                            <option value="Egyéb">Egyéb</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="e-amount">Kiadás összege</label>
                                <div class="input-group">
                                    <input type="text" name="e-amount" id="e-amount" class="form-control text-right" placeholder="0" min="1" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">Ft</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="e-date">Dátum</label>
                                <input type="text" name="e-date" id="e-date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="e-comment">Megjegyzés</label>
                        <textarea name="e-comment" id="e-comment" rows="2" class="form-control" placeholder="Ide tudsz hozzáfűzni dolgokat..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-link" data-dismiss="modal">Vissza</button>
                    <button type="submit" class="btn btn-sm btn-success">Kiadás hozzáadása</button>
                </div>
            </form>
        </div>
    </div>
</div>