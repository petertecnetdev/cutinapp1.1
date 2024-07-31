<?php



namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\{Cart, Item, Event};
class TicketController extends Controller
{  public function __construct()
    {
        $this->middleware('auth');
    }
    // Mostrar a lista de tickets
    public function index()
    {
        // Obtém o usuário autenticado
        $user = auth()->user();
    
        // Obtém todos os carrinhos pagos do usuário
        $carts = Cart::where('user_id', $user->id)
            ->where('is_paid', 1)
            ->get();
    
        // Inicializa um array para armazenar os ingressos e suas quantidades
        $ticketQuantities = [];
    
        // Itera pelos carrinhos e extrai os ingressos do campo items
        foreach ($carts as $cart) {
            $items = $cart->items;
    
            // Itera pelos itens e verifica se o campo "type" é igual a "Ingresso"
            foreach ($items as $item) {
                if ($item['type'] === 'Ingresso') {
                    // Verifica se já existe um ticket para o mesmo ID
                    if (isset($ticketQuantities[$item['id']])) {
                        // Se existir, soma a quantidade atual à quantidade existente
                        $ticketQuantities[$item['id']]['quantity'] += $item['quantity'];
                    } else {
                        // Se não existir, cria um novo ticket com o ID, nome do ingresso e a quantidade atual
                        $ticketQuantities[$item['id']] = [
                            'id' => $item['id'],
                            'name' => $item['name'], // Nome do ingresso
                            'quantity' => $item['quantity'],// Nome do ingresso
                            'event_id' => $item['eventId'],
                        ];
                    }
                }
            }
        }
    
        // Agora temos o array $ticketQuantities com os IDs dos ingressos, seus nomes e suas quantidades somadas
    
        // Inicializa um array para armazenar os tickets finais
        $tickets = [];
    
        // Itera pelo array de quantidades para criar os tickets
        foreach ($ticketQuantities as $ticket) {
            // Verifica se a quantidade é maior que zero
            if ($ticket['quantity'] > 0) {
                // Adiciona o ticket ao array de tickets
                $tickets[] = $ticket;
            }
        }
    
        // Agora, você tem o array $tickets com os ingressos individuais, seus nomes e suas quantidades somadas corretamente
    
        return view('tickets.index', compact('tickets'));
    }
    

    // Mostrar o formulário de criação de ticket
    public function create(Event $event)
{
    return view('tickets.create', compact('event'));
}

    // Armazenar um novo ticket no banco de dados
    public function store(Request $request, Event $event)
{
    // Valide os dados do formulário
    $request->validate([
        'nome' => 'required|string|max:255',
        'quantity' => 'required|integer|min:1',
        'ticket_type' => 'required|string|in:Normal,Cortesia,Estudante',
        'price' => 'required|numeric|min:0',
        'description' => 'required|string',
        'time' => 'nullable|date|after_or_equal:' . $event->start_date . '|before_or_equal:' . $event->end_date,
    ]);

    // Crie um novo ingresso associado ao evento
    $ticket = new Ticket([
        'event_id' => $event->id,
        'name' => $request->input('nome'),
        'quantity' => $request->input('quantity'),
        'ticket_type' => $request->input('ticket_type'),
        'limit_date' => $request->input('limit_date'),
        'price' => $request->input('price'),
        'description' => $request->input('description'),
    ]);

    // Verifique se o ingresso é do tipo "Cortesia" e defina o horário limite de entrada
    if ($request->input('ticket_type') === 'Cortesia' && $request->has('time')) {
        $ticket->time = $request->input('time');
    }

    // Salve o ingresso no banco de dados
    $ticket->save();

    // Redirecione de volta para a página de edição do evento com uma mensagem de sucesso
        return redirect()->route('ticket.create', $event)->with('success', 'Ticket criado com sucesso.');
}

    // Mostrar os detalhes de um ticket específico
    public function show($id)
    {
       
    $tickets = Ticket::where('event_id', $id)->get();
        return view('crud.tickets.show', compact('tickets'));
    }

    // Mostrar o formulário de edição de ticket
    public function edit($id)
    {
        $ticket = Ticket::findOrFail($id);
        return view('tickets.edit', compact('ticket'));
    }

    // Atualizar os dados de um ticket no banco de dados
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            // Outros campos do ticket
        ]);

        $ticket = Ticket::findOrFail($id);
        $ticket->update($data);

        return redirect()->route('tickets.index')->with('success', 'Ticket atualizado com sucesso!');
    }

    // Excluir um ticket do banco de dados
    public function destroy(Request $request,Event $event,  $id)
{
    
        $ticket = Ticket::findOrFail($id);
        $event = Event::findOrFail($ticket->event_id);
        $ticket->delete();

        return redirect()->route('ticket.create', $event)->with('success', 'Ticket excluído com sucesso.');
    
}

    


    


  
  
    public function payment(Request $request)
{
    
    $totalValue = $request->input('totalvalue') * 1;
    $user = Auth::user();
    $ticketsData = $request->input('tickets');

    if (!$ticketsData) {
        return redirect()->back()->with('error', 'Nenhum ingresso selecionado.');
    }

    $cart = Cart::where('user_id', $user->id)->where('is_paid', 0)->first();

    if (!$cart) {
        $cart = new Cart();
        $cart->user_id = $user->id;
        $cart->items = [];
        $cart->is_paid = 0;
    }

    $selectedTickets = json_decode($ticketsData, true);

    $newCartItems = [];
    $itemsForApi = []; // Array to store items for API
    foreach ($selectedTickets as $ticket) {
        $newItem = [
            'id' => $ticket['id'],
            'name' => preg_replace('/\s+/', '-', $ticket['name']),
            'price' => $ticket['price'],
            'quantity' => $ticket['quantity'],
            'type' => 'ingresso',
            'description' => $ticket['description'],
            'event_id' => $ticket['event_id'],
        ];

        if ($newItem['price'] > 0) {
            $itemsForApi[] = [
                'amount' => $newItem['price'],
                'description' => $newItem['name'],
                'quantity' => $newItem['quantity'],
            ];
        }

        $newCartItems[] = $newItem;
    }

    $cart->items = $newCartItems;
    $cart->save();

    // Create an array to store new items to be added to the "items" table
    $newItemsForTable = [];
    foreach ($cart->items as $item) {
        $itemType = strtolower($item['type']);
        $price = floatval($item['price']);

        if ($price === 0) {
            // Item with price 0, don't send to API, but add to newItemsForTable
            continue;
        }

        if ($itemType === 'ingresso') {
            for ($i = 0; $i < $item['quantity']; $i++) {
                $newItemsForTable[] = [
                    'name' => $item['name'],
                    'quantity' => 1,
                    'event_id' => $item['event_id'],
                    'type' => $itemType,
                    'description' => $item['description'],
                    'cart_id' => $cart->id,
                    'user_id' => $user->id,
                ];
            }
        }
    }


    if ($totalValue === 0) {
        // If the total value is 0, mark the cart as paid
        $cart->is_paid = 1;
        $cart->save();

    // Insert new items into the "items" table
    Item::insert($newItemsForTable);
        return redirect()->route('tickets.index')->with('success', 'Pedido realizado com sucesso!');
    }

  

    // Prepare the customer and payment data for the API request
    $customerData = [
        'name' => $user->name,
        'email' => $user->email,
        'type' => 'individual',
        // ... (other customer data)
    ];

    $paymentsData = [
        [
            'payment_method' => 'pix',
            'pix' => [
                'expires_in' => '600',
                // ... (other payment data)
            ]
        ]
    ];

    // Combine all data into the $data array
    $data = [
        'items' => $itemsForApi,
        'customer' => $customerData,
        'payments' => $paymentsData
    ];

    try {
        $client = new \GuzzleHttp\Client();

        $response = $client->request('POST', 'https://api.pagar.me/core/v5/orders', [
            'json' => $data,
            'headers' => [
                'Authorization' => 'Basic ' . env('PAGAR_ME_AUTH_KEY'),
                'Accept' => 'application/json',
            ],
        ]);

        $responseData = json_decode($response->getBody()->getContents(), true);
        $qrCodeUrl = $responseData['charges'][0]['last_transaction']['qr_code_url'];
        $qrCode = $responseData['charges'][0]['last_transaction']['qr_code'];
        $idorder = $responseData['id'];

        // Return view with QR code
        return view('tickets.qrcode', compact('qrCodeUrl', 'qrCode', 'idorder'));
    } catch (\Exception $e) {
        $errorMessage = $e->getMessage();
        return view('error', compact('errorMessage'));
    }
}

    
    public function checkPaymentStatus(Request $request)
    {
        $orderId = $request->route('idorder');
    
        $client = new \GuzzleHttp\Client();
    
        try {
            $response = $client->request('GET', "https://api.pagar.me/core/v5/orders/{$orderId}", [
                'headers' => [
                    'accept' => 'application/json',
                    'Authorization' => 'Basic ' . env('PAGAR_ME_AUTH_KEY'),
                ],
            ]);
    
            $responseData = json_decode($response->getBody()->getContents(), true);
            $status = $responseData['status'];
    
            // Obtém o carrinho do usuário autenticado
            $user = Auth::user();
            $cart = Cart::where('user_id', $user->id)
                ->where('is_paid', 0)
                ->where('idorder', $orderId)
                ->first();
    
            if ($status === 'paid') {
                // Define o carrinho como pago
                $cart->is_paid = 1;
                $cart->save();
    
                // Itera pelos itens do carrinho e salva-os na tabela "items"
                $cartItems = json_decode($cart->items, true);
                foreach ($cartItems as $item) {
                    if ($item['type'] === 'Ingresso') {
                        // Cria múltiplas instâncias de Item, uma para cada ingresso comprado
                        for ($i = 0; $i < $item['quantity']; $i++) {
                            $newItem = new Item([
                                'name' => $item['name'],
                                'quantity' => 1,
                                'event_id' => $item['event_id'],
                                'type' => $item['type'],
                                'description' => $item['description'],
                                'cart_id' => $cart->id,
                                'user_id' => $user->id,
                            ]);
    
                            // Salva o novo item na tabela "items"
                            $newItem->save();
                            
                        }
                    }
                }
    
                // Redireciona para a página de ingressos após o pagamento ser confirmado
                return redirect()->route('tickets.index')->with('success', 'Pagamento realizado com sucesso!');
            } else {
                // Retorna apenas o campo "status"
                return response()->json(['status' => $status]);
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // Tratamento de exceção caso ocorra um erro na requisição
            // Dica: Adicione logs para verificar possíveis erros na requisição
            // Log::error("Erro na requisição: " . $e->getMessage());
    
            return false;
        }
    }
    

public function myOne($id)
{
    // Recupera o ingresso com o ID fornecido do banco de dados
    $ticket = Item::findOrFail($id);
    

    if (!$ticket) {
        return view('erro');
    }
    // Verifica se o ingresso já foi utilizado pelo participante
    $isUsed = $ticket->is_used;

    // Obter o nome da produção associada ao evento do ingresso
    $productionName = $ticket->event->production_name;

    // Criar o conteúdo do QR code como uma string JSON
    $qrCodeContent = 'http://gatenex.com/tickets/detail/'.$id;

    // URL da API do Google Chart para gerar o QR code
    $apiUrl = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" . urlencode($qrCodeContent);

    // Renderizar a view "myOne.blade.php" e passar os dados do ingresso, nome da produção e a URL do QR code como parâmetros
    return view('crud.tickets.myOne', compact('ticket', 'isUsed', 'productionName', 'apiUrl'));
}

public function detail($id)
    {
        // Recupera o ingresso com o ID fornecido do banco de dados
        $ticket = Item::findOrFail($id);

        // Verificar se o usuário autenticado é o Produtor do evento correspondente ao ingresso
        if (Auth::user()->id === $ticket->event->production->user_id) {
            // Obter o usuário associado ao item pelo user_id
            $user = $ticket->user;

            // Renderizar a view de detalhamento do ingresso e passar os dados do ingresso e do usuário como parâmetros
            return view('crud.tickets.ticket_detail', compact('ticket', 'user'));
        } else {
            // Caso o usuário autenticado não seja o Produtor do evento correspondente ao ingresso, redirecionar para uma página de erro ou mensagem
            return view('crud.tickets.unauthorized');
        }
    }

    public function captureTicket($id)
    {
        // Recupera o ingresso com o ID fornecido do banco de dados
        $ticket = Item::findOrFail($id);

        // Marca o ingresso como usado
        $ticket->update(['is_used' => true]);

        // Redireciona de volta para a página de detalhes do ingresso
        return redirect()->route('tickets.detail', ['id' => $ticket->id])->with('success', 'Ingresso validado e participante liberado!');
    }
    public function checkValidation($id)
{
    try {
        // Recupera o ingresso com o ID fornecido do banco de dados
        $ticket = Item::findOrFail($id);

        // Verifica se o ingresso já foi utilizado pelo participante
        $isUsed = $ticket->is_used;

        // Verifica se o ingresso pertence ao usuário autenticado (produtor)
        if ($ticket->user_id !== Auth::id()) {
            return response()->json(['error' => 'Acesso não autorizado.'], 403);
        }

        // Retorna o status de validação do ingresso
        return response()->json(['validated' => $isUsed]);

    } catch (\Exception $e) {
        return response()->json(['error' => 'Erro ao verificar o status de validação do ingresso.'], 500);
    }
    
}
}