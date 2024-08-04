<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Ticket;
use App\Models\Event;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    protected function getValidationMessages()
    {
        return [
            'event_id.required' => 'O campo ID do evento é obrigatório.',
            'event_id.integer' => 'O campo ID do evento deve ser um número inteiro.',
            'ticket_type.required' => 'O campo tipo de ingresso é obrigatório.',
            'ticket_type.string' => 'O campo tipo de ingresso deve ser uma string.',
            'price.required' => 'O campo preço é obrigatório.',
            'price.numeric' => 'O campo preço deve ser um número.',
            'quantity.required' => 'O campo quantidade é obrigatório.',
            'quantity.integer' => 'O campo quantidade deve ser um número inteiro.',
            'status.required' => 'O campo status é obrigatório.',
            'status.boolean' => 'O campo status deve ser um valor booleano.',
        ];
    }

    public function list($eventId)
    {
        try {
            $event = Event::findOrFail($eventId);
            if ($event->production->user_id !== auth()->user()->id) {
                if (!auth()->user()->hasPermission('ticket_list')) {
                    Log::error('Usuário não tem permissão para listar ingressos deste evento.');
                    return response()->json(['error' => 'Você não tem permissão para listar ingressos deste evento.'], 403);
                }
            }
            $tickets = Ticket::where('event_id', $eventId)->get();
            return response()->json(['tickets' => $tickets], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao listar ingressos: ' . $e->getMessage());
            return response()->json(['error' => 'Ocorreu um erro ao listar os ingressos.'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'event_id' => 'required|integer',
                'ticket_type' => 'required|string',
                'price' => 'required|numeric',
                'quantity' => 'required|integer',
                'status' => 'required|boolean',
            ], $this->getValidationMessages());

            if ($validator->fails()) {
                return response()->json(['message' => 'Erro de validação.', 'errors' => $validator->errors()], 422);
            }

            $event = Event::findOrFail($request->input('event_id'));
            if ($event->production->user_id !== auth()->user()->id) {
                if (!auth()->user()->hasPermission('ticket_create')) {
                    Log::error('Usuário não tem permissão para criar ingressos neste evento.');
                    return response()->json(['error' => 'Você não tem permissão para criar ingressos neste evento.'], 403);
                }
            }

            $ticket = new Ticket();
            $ticket->event_id = $request->input('event_id');
            $ticket->ticket_type = $request->input('ticket_type');
            $ticket->price = $request->input('price');
            $ticket->quantity = $request->input('quantity');
            $ticket->status = $request->input('status');

            $ticket->save();

            return response()->json(['message' => 'Ingresso criado com sucesso!', 'ticket' => $ticket], 201);
        } catch (\Exception $e) {
            Log::error('Erro ao criar ingresso: ' . $e->getMessage());
            return response()->json(['error' => 'Ocorreu um erro ao criar o ingresso.'], 500);
        }
    }

    public function show($id)
    {
        try {
            $ticket = Ticket::findOrFail($id);
            $event = $ticket->event;
            if ($event->production->user_id !== auth()->user()->id) {
                if (!auth()->user()->hasPermission('ticket_view')) {
                    Log::error('Usuário não tem permissão para visualizar este ingresso.');
                    return response()->json(['error' => 'Você não tem permissão para visualizar este ingresso.'], 403);
                }
            }
            return response()->json(['ticket' => $ticket], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao mostrar ingresso: ' . $e->getMessage());
            return response()->json(['error' => 'Ocorreu um erro ao mostrar o ingresso.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'event_id' => 'required|integer',
                'ticket_type' => 'required|string',
                'price' => 'required|numeric',
                'quantity' => 'required|integer',
                'status' => 'required|boolean',
            ], $this->getValidationMessages());

            if ($validator->fails()) {
                return response()->json(['message' => 'Erro de validação.', 'errors' => $validator->errors()], 422);
            }

            $ticket = Ticket::findOrFail($id);
            $event = $ticket->event;
            if ($event->production->user_id !== auth()->user()->id) {
                if (!auth()->user()->hasPermission('ticket_update')) {
                    Log::error('Usuário não tem permissão para atualizar este ingresso.');
                    return response()->json(['error' => 'Você não tem permissão para atualizar este ingresso.'], 403);
                }
            }

            $ticket->event_id = $request->input('event_id');
            $ticket->ticket_type = $request->input('ticket_type');
            $ticket->price = $request->input('price');
            $ticket->quantity = $request->input('quantity');
            $ticket->status = $request->input('status');

            $ticket->save();

            return response()->json(['message' => 'Ingresso atualizado com sucesso!', 'ticket' => $ticket], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar ingresso: ' . $e->getMessage());
            return response()->json(['error' => 'Ocorreu um erro ao atualizar o ingresso.'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $ticket = Ticket::findOrFail($id);
            $event = $ticket->event;
            if ($event->production->user_id !== auth()->user()->id) {
                if (!auth()->user()->hasPermission('ticket_delete')) {
                    Log::error('Usuário não tem permissão para deletar este ingresso.');
                    return response()->json(['error' => 'Você não tem permissão para deletar este ingresso.'], 403);
                }
            }
            $ticket->delete();
            return response()->json(['message' => 'Ingresso deletado com sucesso!'], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao deletar ingresso: ' . $e->getMessage());
            return response()->json(['error' => 'Ocorreu um erro ao deletar o ingresso.'], 500);
        }
    }
}
