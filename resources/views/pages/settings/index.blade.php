<?php

use App\Routes\Auth;

use function Laravel\Folio\render;

render(function () {
    return redirect(Auth::settingsProfile->value);
});
