<?php

namespace App\Http\Requests;

// Mêmes règles que pour la création : on hérite pour ne pas dupliquer.
// Si les règles divergent plus tard, surcharge rules() ici.
class UpdateRoadmapRequest extends StoreRoadmapRequest
{
}