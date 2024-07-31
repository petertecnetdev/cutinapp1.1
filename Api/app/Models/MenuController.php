<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Production;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    public function create($production_id)
    {
        $production = Production::findOrFail($production_id);
        return view('menus.create', compact('production'));
    }

    public function store(Request $request, $productionId)
    {
        $production = Production::findOrFail($productionId);

        $menu = new Menu();
        $menu->name = $request->input('name');
        $menu->items = json_encode($request->input('items'));

        $production->menus()->save($menu);

        return redirect()->route('production.show', $productionId);
    }

    public function show($productionId, $menuId)
    {
        $production = Production::findOrFail($productionId);
        $menu = Menu::findOrFail($menuId);

        return view('menus.show', compact('production', 'menu'));
    }

    public function edit($productionId, $menuId)
    {
        $production = Production::findOrFail($productionId);
        $menu = Menu::findOrFail($menuId);

        return view('menus.edit', compact('production', 'menu'));
    }

    public function update(Request $request, $productionId, $menuId)
    {
        $production = Production::findOrFail($productionId);
        $menu = Menu::findOrFail($menuId);

        $menu->name = $request->input('name');
        $menu->items = json_encode($request->input('items'));

        $menu->save();

        return redirect()->route('menu.show', [$productionId, $menuId]);
    }

    public function destroy($productionId, $menuId)
    {
        $production = Production::findOrFail($productionId);
        $menu = Menu::findOrFail($menuId);

        $menu->delete();

        return redirect()->route('production.show', $productionId);
    }
}
