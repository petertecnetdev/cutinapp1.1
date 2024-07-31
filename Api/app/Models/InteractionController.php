<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Interaction;

class InteractionController extends Controller
{
    public function store(Request $request)
    {
        $entityId = $request->input('entity_id');
        $entityType = $request->input('entity_type');
        $interactionType = $request->input('interaction_type');
        $userId = $request->input('user_id');

        // Verifique se o usuário já possui uma interação desse tipo com a entidade
        $existingInteraction = Interaction::where([
            'user_id' => $userId,
            'entity_id' => $entityId,
            'interaction_type' => $interactionType,
            'entity_type' => $entityType
        ])->first();

        if ($existingInteraction) {
            $existingInteraction->delete();
        } else {
            // Crie uma nova interação
            $interaction = new Interaction();
            $interaction->user_id = $userId;
            $interaction->interaction_type = $interactionType;
            $interaction->entity_id = $entityId;
            $interaction->entity_type = $entityType;
            $interaction->save();
        }

        // Recupere as interações atualizadas
        $updatedInteractions = $this->getUpdatedInteractions($entityId, $entityType);

        return response()->json([
            'interactions' => $updatedInteractions['interactions'],
            'likeCount' => $updatedInteractions['likeCount'],
            'confirmCount' => $updatedInteractions['confirmCount'],
            'favoriteCount' => $updatedInteractions['favoriteCount'],
            // ... inclua outras contagens
        ]);
    }

    public function destroy(Request $request, $id)
    {
        // Remova a interação existente
        Interaction::findOrFail($id)->delete();

        $entityId = $request->input('entity_id');
        $entityType = $request->input('entity_type');

        // Recupere as interações atualizadas
        $updatedInteractions = $this->getUpdatedInteractions($entityId, $entityType);

        return response()->json([
            'interactions' => $updatedInteractions['interactions'],
            'likeCount' => $updatedInteractions['likeCount'],
            'confirmCount' => $updatedInteractions['confirmCount'],
            'favoriteCount' => $updatedInteractions['favoriteCount'],
            // ... inclua outras contagens
        ]);
    }

    protected function getUpdatedInteractions($entityId, $entityType)
    {
        $interactions = Interaction::where('entity_id', $entityId)
            ->where('entity_type', $entityType)
            ->get();

            $likeCount = $interactions->where('interaction_type', 'like')->count();
            $confirmCount = $interactions->where('interaction_type', 'confirm')->count();
            $favoriteCount = $interactions->where('interaction_type', 'favorite')->count();
        // ... calcule contagens para outros tipos de interação

        return [
            'interactions' => $interactions,
            'likeCount' => $likeCount,
            'confirmCount' => $confirmCount,
            'favoriteCount' => $favoriteCount,
            // ... inclua outras contagens
        ];
    }
}
