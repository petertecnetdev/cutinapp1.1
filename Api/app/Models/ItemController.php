<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    // Método para exibir um item específico
    public function index()
{
    // Obtém o usuário autenticado
    $user = auth()->user();

    // Obtém todos os itens associados a esse usuário
    $items = $user->items;

    return view('items.index', compact('items'));
}

    public function show($id)
    {
        $item = Item::findOrFail($id);
        $qrCodeContent = 'http://gatenex.com/item/use/'.$id;

        // URL da API do Google Chart para gerar o QR code
        $apiUrl = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" . urlencode($qrCodeContent);
        return view('items.show', compact('item', 'apiUrl'));
    }

    public function use($id)
    {
        // Recupera o ingresso com o ID fornecido do banco de dados
        $item = Item::findOrFail($id);

        // Verificar se o usuário autenticado é o Produtor do evento correspondente ao ingresso
        if (Auth::user()->id === $item->event->production->user_id) {
            // Obter o usuário associado ao item pelo user_id
            $user = $item->user;

            // Renderizar a view de detalhamento do ingresso e passar os dados do ingresso e do usuário como parâmetros
            return view('items.detail', compact('item', 'user'));
        } else {
            // Caso o usuário autenticado não seja o Produtor do evento correspondente ao ingresso, redirecionar para uma página de erro ou mensagem
            return view('items.unauthorized');
        }
    }

    public function capture($id)
    {
        // Recupera o item com o ID fornecido do banco de dados
        $item = Item::findOrFail($id);

        // Marca o item como usado
        $item->update(['is_used' => 1]);

        // Redireciona de volta para a página de detalhes do item
        return redirect()->route('item.use', ['id' => $item->id])->with('success', 'Item validado ');
    }

    public function checkItem($id)
{
    $item = Item::findOrFail($id);

    return response()->json(['is_used' => $item->is_used]);
}
}
