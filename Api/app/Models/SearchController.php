<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Event, Production};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $cityUf = $request->input('city_uf');
        $date = $request->input('date');
        $segment = $request->input('segments');
        $search = $request->input('search');
        $searchproduction = $request->input('searchproduction');
        
        $productions = Production::orderBy('created_at', 'desc')->get();
        $citiesUf = Event::select(DB::raw("CONCAT(city, '-', uf) AS city_uf"))
        ->pluck('city_uf')
        ->unique();
    
        $events = Event::query();
    
        if ($date) {
            $events = $events->whereDate('start_date', $date);
        }
    
        if ($segment) {
            $events = $events->where(function($q) use ($segment) {
                $q->whereJsonContains('segments', $segment)
                  ->orWhere('segments', 'like', '%' . $segment . '%');
            });
        }
        
    
        if ($cityUf) {
            $cityUfParts = explode('-', $cityUf);
        
            if (count($cityUfParts) == 2) {
                list($city, $uf) = $cityUfParts;
        
                $events = $events->where('city', $city)->where('uf', $uf);
            }
        }
    
        // Adicione a pesquisa pelo nome do evento
        if ($search) {
            $events = $events->where('title', 'like', '%'.$search.'%');
        }
    
        // Adicione a pesquisa pela produção pelo ID
        if ($searchproduction) {
            $events = $events->where('production_id', $searchproduction);
        }
        
        $events = $events->orderBy('start_date', 'desc')->get();
        
        $oldInput = $request->all();
    
        return view('search.index', compact('events', 'citiesUf', 'oldInput', 'productions'));
    }
    
    

}
