<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $page = $request->input('page');

        $response = Http::get('https://reqres.in/api/users?page=' . $page);

        $users = $response->object();

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|Response|View
     */
    public function create()
    {
        //
        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        //
        $this->validate($request, [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
        ]);

        $response = Http::post('https://reqres.in/api/users', [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
        ]);

        if ($response->successful()) {
            $user = $response->object();
            return redirect()->route('users.index')->with('success', 'User ' . $user->id . ' created');
        }

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return Application|Factory|Response|View
     */
    public function show($id)
    {
        //
        $response = Http::get('https://reqres.in/api/users/' . $id);

        $user = $response->object()->data;

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $id
     * @return void
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $id
     * @return void
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Int $id
     * @return RedirectResponse
     */
    public function destroy(Int $id)
    {
        $response = Http::delete('https://reqres.in/api/users/' . $id);

        if ($response->successful())
            return redirect()->back()
                ->with('success', 'User #' . $id . ' has been successfully deleted');

        return redirect()->back()->with('alert', 'Can\'t delete #' . $id);
    }
}
