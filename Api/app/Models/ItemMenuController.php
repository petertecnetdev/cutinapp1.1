<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Models\{ItemMenu, Production, Event};
use Illuminate\Support\Str;

class ItemMenuController extends Controller
{
    // Index - Lista todos os itens de menu
    public function index($entityName, $entitySlug)
{
    // Primeiro, determinamos o tipo de entidade com base no $entityName
    $entityType = ($entityName === 'production') ? Production::class : Event::class;

    // Em seguida, encontramos a entidade com base na slug
    $entity = $entityType::where('slug', $entitySlug)->firstOrFail();

    return view('item_menus.index', compact('entity', 'entityName', 'entitySlug'));
}

    

    // Create - Exibe o formulário de criação de um novo item de menu
    public function create($entityName, $entitySlug)
    {
        return view('item_menus.create', compact('entityName', 'entitySlug'));
    }

    // Store - Salva um novo item de menu no banco de dados
    public function store(Request $request, $entityName, $entitySlug)
{
   
    // Primeiro, determinamos o tipo de entidade com base no $entityName
    $entityType = ($entityName === 'production') ? Production::class : Event::class;

    // Em seguida, encontramos a entidade com base na slug
    $entity = $entityType::where('slug', $entitySlug)->firstOrFail();

    $itemMenu = new ItemMenu;
    $itemMenu->name = $request->input('name');
    $itemMenu->description = $request->input('description');
    $itemMenu->category = $request->input('category');
    $itemMenu->stock = $request->input('stock');
    $itemMenu->price = $request->input('price');
    $itemMenu->entity_id = $entity->id;
    $itemMenu->entity_name = $entityName;

    // Verifica se uma imagem foi enviada
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageFileName = 'item-image-' . Str::slug($itemMenu->title) . '-' . $itemMenu->id . '-' . now()->timestamp . '.' . $image->getClientOriginalExtension();
        $imagePath = public_path('img/itemmenu/' . Str::slug($itemMenu->name) . '-' . $itemMenu->id);

        if (!File::isDirectory($imagePath)) {
            File::makeDirectory($imagePath, 0777, true);
        }

        $image = Image::make($image);
        $image->fit(80, 80);
        $image->save($imagePath . '/' . $imageFileName);

        $itemMenu->image = 'img/itemmenu/' . Str::slug($itemMenu->name) . '-' . $itemMenu->id . '/' . $imageFileName;
    }

    $itemMenu->save();

    return redirect()->route('item_menu.create', ['entityName' => $entityName, 'entitySlug' => $entitySlug])
                    ->with('success', 'Item adicionado com sucesso!');
}


    // Show - Exibe os detalhes de um item de menu específico
    public function show($id)
    {
        $itemMenu = ItemMenu::findOrFail($id);
        return view('item_menus.show', compact('itemMenu'));
    }

    // Edit - Exibe o formulário de edição de um item de menu específico
    public function edit($entityName, $entitySlug)
    {
        // Primeiro, determinamos o tipo de entidade com base no $entityName
        $entityType = ($entityName === 'production') ? Production::class : Event::class;
    
        // Em seguida, encontramos a entidade com base na slug
        $entity = $entityType::where('slug', $entitySlug)->firstOrFail();
    
        // Agora, obtemos os itens do menu associados a essa entidade
        $itemMenus = $entity->itemMenus;
    
        return view('menus.edit', compact('entity', 'itemMenus', 'entityName', 'entitySlug'));
    }
    

    // Update - Atualiza um item de menu no banco de dados
    public function update(Request $request, $id)
    {
        $itemMenu = ItemMenu::findOrFail($id);
        $itemMenu->name = $request->input('name');
        $itemMenu->description = $request->input('description');
        $itemMenu->category = $request->input('category');
        $itemMenu->stock = $request->input('stock');
        $itemMenu->price = $request->input('price');
        $itemMenu->entity_id = $request->input('entity_id');
        $itemMenu->entity_name = $request->input('entity_name');

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageFileName = 'item_menu-' . Str::slug($itemMenu->name) . '-' . $itemMenu->id . '-' . now()->timestamp . '.' . $image->getClientOriginalExtension();
            $imagePath = public_path('img/item_menu/' . Str::slug($itemMenu->name) . '-' . $itemMenu->id);

            if (!File::isDirectory($imagePath)) {
                File::makeDirectory($imagePath, 0777, true);
            }

            $image = Image::make($image);
            $image->fit(250, 250);
            $image->save($imagePath . '/' . $imageFileName);

            $itemMenu->image = 'img/item_menu/' . Str::slug($itemMenu->name) . '-' . $itemMenu->id . '/' . $imageFileName;
        }

        $itemMenu->save();

        return redirect()->route('item_menus.index')->with('success', 'Item de Menu atualizado com sucesso!');
    }

    // Destroy - Exclui um item de menu do banco de dados
    public function destroy($id)
    {
        $itemMenu = ItemMenu::findOrFail($id);
        if ($itemMenu->image) {
            File::delete($itemMenu->image);
        }
        $itemMenu->delete();

        return redirect()->route('item_menus.index')->with('success', 'Item de Menu excluído com sucesso!');
    }
}