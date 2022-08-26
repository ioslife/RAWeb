<?php

use App\Legacy\Models\User;
use Illuminate\Support\Facades\Validator;

if (!authenticateFromCookie($user, $permissions, $userDetail)) {
    return back()->withErrors(__('legacy.error.permissions'));
}

/** @var User $user */
$user = request()->user();

$input = Validator::validate(request()->post(), [
    'achievement' => 'required|integer|exists:mysql_legacy.Achievements,ID',
    'mode' => 'required|boolean',
    'issue' => 'required|integer|min:1|max:2',
    'description' => 'required|string|max:2000',
    'emulator' => 'required|string',
    'emulator_version' => 'required|string',
    'core' => 'sometimes|nullable|string',
    'hash' => 'sometimes|nullable|string',
]);

$achievementId = (int) $input['achievement'];

$note = $input['description'];
if (!empty($input['hash'])) {
    $note .= "\nRetroAchievements Hash: " . $input['hash'];
}
if (!empty($input['emulator'])) {
    $note .= "\nEmulator: " . $input['emulator'];
    if (!empty($input['core']) && ($input['emulator'] === 'RetroArch' || $input['emulator'] === 'RALibRetro')) {
        $note .= " (" . $input['core'] . ")";
    }
}
if (!empty($input['emulator_version'])) {
    $note .= "\nEmulator Version: " . $input['emulator_version'];
}

if (submitNewTickets($user, $achievementId, (int) $input['issue'], (int) $input['mode'], $note)) {
    return redirect(route('achievement.show', $achievementId))->with('success', __('legacy.success.submit'));
}

return back()->withErrors(__('legacy.error.error'));
