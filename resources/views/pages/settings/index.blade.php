<?php

use App\Routes\Web;

use function Laravel\Folio\render;

render(function () {
    return redirect(Web::settingsProfile->value);
});
