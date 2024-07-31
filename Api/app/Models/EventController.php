<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\{Profile,Production, Blog,Interaction};
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index()
    {
        $currentDate = now(); // Obtém a data atual
        $events = Event::where('start_date', '>', $currentDate)
        ->orderBy('start_date', 'asc')
        ->get(); // Eventos que ainda vão acontecer, ordenados por data
        return view('events.index', compact('events'));
    }
    
    public function passed()
    {
        
        $currentDate = now(); // Obtém a data atual
        $events = Event::where('start_date', '<', $currentDate)
        
        ->orderBy('start_date', 'desc')
        ->get(); // Apenas os primeiros 3 eventos que ainda vão acontecer
        return view('events.passed', compact('events'));
    }
    public function create()
    {
        $user = auth()->user();
        $numberOfProductions = $user->productions()->count();
    
        if ($numberOfProductions > 0) {
            return view('events.create');
        } else {
            return redirect()->route('production.create')->with('message', 'Para criar um evento, você precisa cadastrar uma produção primeiro.');
        }
    }


    public function my()
    {
        $user = Auth::user();
    
        $events = $user->events;
    
        return view('events.index', compact('events'));
    }
    
    




    public function store(Request $request)
    {
        
    $event = new Event();
        
        $event->title = $request->input('title');
        $event->production_id = $request->input('production_id');
        $event->establishment_type = $request->input('establishment_type');
        $event->description = $request->input('description');
        $event->segments = $request->input('segments');
        $event->city = $request->input('city');
        $event->uf = $request->input('uf');
        $event->location = $request->input('location');
        $event->cep = $request->input('cep');
        $event->address = $request->input('address');
        $event->is_featured = $request->input('is_featured');
        $event->is_published = $request->input('is_published');
        $event->is_approved = $request->input('is_approved');
        $event->is_cancelled = $request->input('is_cancelled');
        $event->start_date = $request->input('start_date');
        $event->end_date = $request->input('end_date');
        // Salvar a imagem
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageFileName = 'event-image-' . Str::slug($event->title) . '-' . $event->id . '-' . now()->timestamp . '.' . $image->getClientOriginalExtension();
            $imagePath = public_path('img/event/' . Str::slug($event->title) . '-' . $event->id);

            if (!File::isDirectory($imagePath)) {
                File::makeDirectory($imagePath, 0777, true);
            }

            $image = Image::make($image);
            $image->fit(850, 500);
            $image->save($imagePath . '/' . $imageFileName);

            $event->image = 'img/event/' . Str::slug($event->title) . '-' . $event->id . '/' . $imageFileName;
        }
       
        $slug = Str::slug($request->input('title'));

    $count = Event::where('slug', $slug)->count();
    if ($count > 0) {
        $slug = $slug . '-' . ($count + 1);
    }
    $event->slug =$slug;
        // Salvar o evento
        $event->save();

        // Repita a lógica para os demais relacionamentos e atributos

        // Redirecionar para a página de visualização do evento
        return redirect()->route('ticket.create', $event->id)->with('success', 'Evento criado com sucesso. Agora você pode cdastrar um ou mais ingressos.');
    }

    public function show($slug)
{
    $event = Event::where('slug', $slug)->firstOrFail();
    $blogs = Blog::all();
    
    $events = Event::orderBy('start_date', 'desc')
    ->where('production_id', $event->production_id) // Filtrando pela mesma produção
    ->get();
    $userHasLikedPost = false; // Default value

    // Check if the user is authenticated
    if (Auth::check()) {
        
        $user = Auth::user();
        
        $interaction = new Interaction();
        $interaction->user_id =  $user->id;
        $interaction->interaction_type = 'view';
        $interaction->entity_id = $event->id;
        $interaction->entity_type = 'event';
        $interaction->save();

        $interactionLike = Interaction::where([
            'user_id' => $user->id,
            'entity_id' => $event->id,
            'entity_type' => 'event',
            'interaction_type' => 'like'
        ])->first();
        $interactionConfirm = Interaction::where([
            'user_id' => $user->id,
            'entity_id' => $event->id,
            'entity_type' => 'event',
            'interaction_type' => 'confirm'
        ])->first();
      
        if ($interactionConfirm) {
            $userHdesconfirmedPost = true;
        }else{
            
            $userHdesconfirmedPost = false;
        }
        if ($interactionLike) {
            $userHasLikedPost = true;
        }else{
            
            $userHasLikedPost = false;
        }
  
      
    }
    if (!Auth::check()) {
        $userHdesconfirmedPost = false;
        
        $userHasLikedPost = false;
    }
    return view('events.show', [
        'blogs' => $blogs,
        'events' => $events,
        'event' => $event,
        'liked' => $userHasLikedPost,
        'confirmed' => $userHdesconfirmedPost,
    ]);
}

    public function edit($slug)
    {
        $event = event::where('slug', $slug)->firstOrFail();
        return view('events.edit', compact('event'));
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);
    
        $event->title = $request->input('title');
        $event->production_id = $request->input('production_id');
        $event->establishment_type = $request->input('establishment_type');
        $event->description = $request->input('description');
        $event->segments = $request->input('segments');
        $event->city = $request->input('city');
        $event->location = $request->input('location');
        $event->uf = $request->input('uf');
        $event->cep = $request->input('cep');
        $event->address = $request->input('address');
        $event->is_featured = $request->input('is_featured');
        $event->is_published = $request->input('is_published');
        $event->is_approved = $request->input('is_approved');
        $event->is_cancelled = $request->input('is_cancelled');
        $event->start_date = $request->input('start_date');
        $event->end_date = $request->input('end_date');
        
        // Handle image update
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageFileName = 'event-image-' . Str::slug($event->title) . '-' . $event->id . '-' . now()->timestamp . '.' . $image->getClientOriginalExtension();
            $imagePath = public_path('img/event/' . Str::slug($event->title) . '-' . $event->id);
    
            if (!File::isDirectory($imagePath)) {
                File::makeDirectory($imagePath, 0777, true);
            }
    
            $image = Image::make($image);
            $image->fit(850, 500);
            $image->save($imagePath . '/' . $imageFileName);
    
            $event->image = 'img/event/' . Str::slug($event->title) . '-' . $event->id . '/' . $imageFileName;
        }
        if ($request->input('title')){
            $slug = Str::slug($request->input('title'));

            $count = Event::where('slug', $slug)->count();
            if ($count > 0) {
                $slug = $slug . '-' . ($count + 1);
            }
            $event->slug =$slug;

        }
       
    
        // Save the changes
        $event->save();
    
        // Redirect to the event's view page
        return redirect()->route('event.show', $event->slug)->with('success', 'Evento atualizado com sucesso!');
    }
    

    public function destroy($id)
    {
        $event = Event::findOrFail($id);

        // Lógica para deletar imagens, se necessário

        $event->delete();

        return redirect()->route('event.index')->with('success', 'Evento cancelado com sucesso!');
    }
    public function getMoreEvents($skip) {
        $currentDate = now();
    
        $remainingEvents = Event::where('start_date', '>', $currentDate)->count();
        $limit = min($remainingEvents, 3);
    
        $events = Event::with('interactions') // Carrega as interações junto com os eventos
                        ->where('start_date', '>', $currentDate)
                        ->skip($skip)
                        ->take($limit)
                        ->get();
    
        return response()->json($events);
    }
    public function promoters($id)
    {
        $event = Event::findOrFail($id);
        $promoters = Profile::where('type', 'promoter')->get();
        return view('events.promoters', compact('event', 'promoters'));
    }

    public function addPromoter(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $profileId = $request->input('profile_id');

        // Lógica para associar promoter ao evento

        return redirect()->route('event.promoters', $event->id)->with('success', 'Promotor adicionado com sucesso!');
    }

    public function removePromoter(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $profileId = $request->input('profile_id');

        // Lógica para remover promoter do evento

        return redirect()->route('event.promoters', $event->id)->with('success', 'Promotor removido com sucesso!');
    }

    // Outros métodos e lógicas a serem adicionados aqui...

}
