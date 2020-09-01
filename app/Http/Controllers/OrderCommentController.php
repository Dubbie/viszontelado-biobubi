<?php

namespace App\Http\Controllers;

use App\Order;
use App\OrderComment;
use Illuminate\Http\Request;

class OrderCommentController extends Controller
{
    public function store(Request $request) {
        $data = $request->validate([
            'comment-order-id' => 'required',
            'comment-content' => 'required',
        ]);

        $localOrder = Order::find($data['comment-order-id']);

        $comment = new OrderComment();
        $comment->user_id = \Auth::id();
        $comment->order_id = $localOrder->id;
        $comment->content = $data['comment-content'];
        $comment->status_text = $localOrder->status_text;
        $comment->status_color = $localOrder->status_color;

        if (!$comment->save()) {
            \Log::error('Hiba történt a megjegyzés elmentésekor az adatbázisba!');
            \Log::error(var_dump($comment->attributesToArray()));
            return redirect(url()->previous())->with([
                'error' => 'Hiba történt a megjegyzés elmentésekor'
            ]);
        }

        return redirect(url()->previous())->with([
            'success' => 'Megjegyzés sikeresen elmentve'
        ]);
    }

    /**
     * @param $commentId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($commentId) {
        $comment = OrderComment::find($commentId);
        return view('comment.edit')->with([
            'comment' => $comment
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request) {
        $data = $request->validate([
            'comment-id' => 'required',
            'comment-content' => 'required'
        ]);

        $comment = OrderComment::find($data['comment-id']);
        $comment->content = $data['comment-content'];

        if (!$comment->save()) {
            \Log::error('Hiba történt a megjegyzés elmentésekor az adatbázisba!');
            \Log::error(var_dump($data));
            return redirect(action('OrderCommentController@edit', $comment->id))->with([
                'error' => 'Hiba történt a megjegyzés elmentésekor'
            ]);
        }

        return redirect(action('OrderController@show', $comment->order->inner_resource_id))->with([
            'success' => 'Megjegyzés sikeresen elmentve'
        ]);
    }

    /**
     * @param $commentId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($commentId) {
        $comment = OrderComment::find($commentId);

        try {
            $comment->delete();
        } catch (\Exception $e) {
            \Log::error('Hiba történt a megjegyzés törlésekor!');
            \Log::error($e->getMessage());
            return redirect(url()->previous())->with([
                'error' => 'Hiba történt a megjegyzés törlésekor'
            ]);
        }

        return redirect(url()->previous())->with([
            'success' => 'Megjegyzés sikeresen törölve'
        ]);
    }
}
