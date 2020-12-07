<!-- Modal -->
<div class="modal fade" id="newHqIncome" tabindex="-1" role="dialog" aria-labelledby="newHqIncomeLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ action('RevenueController@storeIncome') }}" method="POST">
                @csrf
                <input type="hidden" name="hqi-name" value="Egyéb">
                <div class="modal-header">
                    <h5 class="modal-title" id="newHqIncomeLabel">Új központi bevétel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="hqi-amount">Bevétel összege</label>
                                <div class="input-group">
                                    <input type="text" name="hqi-amount" id="hqi-amount" class="form-control text-right" placeholder="0" min="1" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">Ft</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="hqi-date">Dátum</label>
                                <input type="text" name="hqi-date" id="hqi-date" class="datepicker-single form-control" value="{{ date('Y/m/d') }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="hqi-comment">Megjegyzés</label>
                        <textarea name="hqi-comment" id="hqi-comment" rows="2" class="form-control" placeholder="Ide tudsz hozzáfűzni dolgokat..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-link" data-dismiss="modal">Vissza</button>
                    <button type="submit" class="btn btn-sm btn-success">Bevétel hozzáadása</button>
                </div>
            </form>
        </div>
    </div>
</div>