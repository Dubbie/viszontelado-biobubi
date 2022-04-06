<?php

namespace App\Http\Controllers;

use App\CustomerComment;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Log;

class CustomerCommentController extends Controller
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request) {
        $data = $request->validate([
            'comment-customer-id' => 'required',
            'comment-content'     => 'required',
        ]);

        $cc              = new CustomerComment();
        $cc->user_id     = Auth::user()->id;
        $cc->customer_id = intval($data['comment-customer-id']);
        $cc->content     = $data['comment-content'];
        $cc->save();

        return redirect(action([CustomerController::class, 'show'], ['customerId' => $cc->customer_id]))->with([
            'success' => 'Komment sikeresen rögzítve!',
        ]);
    }

    /**
     * @param $commentId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function edit($commentId) {
        $comment = Auth::user()->customerComments()->find($commentId);
        if (Auth::user()->admin) {
            $comment = CustomerComment::find($commentId);
        }

        if (! $comment) {
            return redirect(url()->previous())->with([
                'error' => 'Nem található ilyen azonosítójú komment.',
            ]);
        }

        return view('customer-comment.edit')->with([
            'comment' => $comment,
        ]);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request) {
        $data = $request->validate([
            'comment-id'      => 'required',
            'comment-content' => 'required',
        ]);

        $comment          = CustomerComment::find($data['comment-id']);
        $comment->content = $data['comment-content'];

        if (! $comment->save()) {
            Log::error('Hiba történt az ügyfél megjegyzés elmentésekor az adatbázisba!');
            Log::error(var_dump($data));

            return redirect(action('CustomerCommentController@edit', $comment->id))->with([
                'error' => 'Hiba történt a megjegyzés elmentésekor',
            ]);
        }

        return redirect(action([
            CustomerController::class,
            'show',
        ], ['customerId' => $comment->customer_id]))->with([
            'success' => 'Megjegyzés sikeresen elmentve',
        ]);
    }

    public function destroy($commentId) {
        $comment = Auth::user()->customerComments()->find($commentId);
        if (Auth::user()->admin) {
            $comment = CustomerComment::find($commentId);
        }

        try {
            $comment->delete();
        } catch (Exception $e) {
            Log::error('Hiba történt a megjegyzés törlésekor!');
            Log::error($e->getMessage());

            return redirect(url()->previous())->with([
                'error' => 'Hiba történt a megjegyzés törlésekor',
            ]);
        }

        return redirect(url()->previous())->with([
            'success' => 'Megjegyzés sikeresen törölve',
        ]);
    }
}
