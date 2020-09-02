<?php

namespace App\Http\Controllers;

use App\Order;
use App\OrderTodo;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrderTodoController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index() {
        $todos = \Auth::user()->todos()->orderBy('completed_at')->orderBy('deadline')->get();
        return view('todo.index')->with([
            'todos' => $todos
        ]);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create() {
        return view('todo.create');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request) {
        $data = $request->validate([
            'todo-order-id' => 'nullable',
            'todo-content' => 'required',
            'todo-deadline-date' => 'required_with:todo-order-id',
            'todo-deadline-time' => 'required_with:todo-order-id',
        ]);

        // Dátum lekezelés
        $format = 'Y-m-d H:i';
        if (count(explode(':', $data['todo-deadline-time'])) == 3) {
            $format .= ':s';
        }
        $datetime = sprintf('%s %s', $data['todo-deadline-date'], $data['todo-deadline-time']);

        $todo = new OrderTodo();
        $todo->user_id = \Auth::id();
        $todo->content = $data['todo-content'];
        $todo->deadline = Carbon::createFromFormat($format, $datetime);

        if (array_key_exists('todo-order-id', $data)) {
            $localOrder = Order::find($data['todo-order-id']);
            $todo->order_id = $localOrder->id;
            $todo->status_text = $localOrder->status_text;
            $todo->status_color = $localOrder->status_color;
        }

        // Lekezeljük a visszadobást
        $redirectTo = url()->previous() == action('OrderTodoController@create') ? action('OrderTodoController@index') : url()->previous();
        if (!$todo->save()) {
            \Log::error('Hiba történt a teendő elmentésekor az adatbázisba!');
            \Log::error(var_dump($todo->attributesToArray()));
            return redirect(url()->previous())->with([
                'error' => 'Hiba történt a teendő elmentésekor'
            ]);
        }

        return redirect($redirectTo)->with([
            'success' => 'Teendő sikeresen elmentve'
        ]);
    }

    /**
     * @param $todoId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function toggle($todoId) {
        /** @var OrderTodo $todo */
        $todo = \Auth::user()->todos()->find($todoId);
        $todo->completed_at = $todo->completed_at == null ? Carbon::now()->format('Y-m-d H:i:s') : null;
        if (!$todo->save()) {
            return redirect(url()->previous())->with([
                'error' => 'Hiba történt a teendő elmentésekor'
            ]);
        }

        return redirect(url()->previous())->with([
            'success' => 'Teendő sikeresen elmentve'
        ]);
    }

    /**
     * @param $todoId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($todoId) {
        $todo = OrderTodo::find($todoId);
        return view('todo.edit')->with([
            'todo' => $todo
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request) {
        $data = $request->validate([
            'todo-id' => 'required',
            'todo-content' => 'required',
            'todo-deadline-date' => 'required',
            'todo-deadline-time' => 'required',
        ]);

        // Dátum lekezelés
        $format = 'Y-m-d H:i';
        if (count(explode(':', $data['todo-deadline-time'])) == 3) {
            $format .= ':s';
        }
        $datetime = sprintf('%s %s', $data['todo-deadline-date'], $data['todo-deadline-time']);

        /** @var OrderTodo $todo */
        $todo = \Auth::user()->todos()->find($data['todo-id']);
        $todo->content = $data['todo-content'];
        $todo->deadline = Carbon::createFromFormat($format, $datetime);

        if (!$todo->save()) {
            \Log::error('Hiba történt a teendő elmentésekor az adatbázisba!');
            \Log::error(var_dump($data));
            return redirect(url()->previous())->with([
                'error' => 'Hiba történt a teendő elmentésekor'
            ]);
        }

        return redirect(action('OrderTodoController@index'))->with([
            'success' => 'Teendő sikeresen elmentve'
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
