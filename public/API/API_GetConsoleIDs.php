<?php

use App\Models\System;

/*
 *  API_GetConsoleIDs - returns mapping of known consoles
 *    a : active - 1 for only active systems, 0 for all (default: 0)
 *    g : only game systems - 1 for only game systems, 0 for all system types (Events, Hubs, etc) (default: 0)
 *
 *  array
 *   object    [value]
 *    int       ID                  unique identifier of the console
 *    string    Name                name of the console
 *    string    IconURL             system icon URL
 *    bool      Active              indicates if the system is active in RA
 *    bool      IsGameSystem        indicates if the system is a game system (not Events, Hubs, etc.)
 */

$onlyActive = (bool) request()->query('a', '0');
$onlyGameConsoles = (bool) request()->query('g', '0');

$systems = System::query();
if ($onlyGameConsoles) {
    $systems = $systems->gameSystems();
}
if ($onlyActive) {
    $systems = $systems->active();
}

$response = $systems->get()->map(fn ($system) => [
    'ID' => $system->ID,
    'Name' => $system->Name,
    'IconURL' => $system->icon_url,
    'Active' => boolval($system->active),
    'IsGameSystem' => System::isGameSystem($system->ID),
])
    ->values();

return response()->json($response);
