<?php

namespace App\Modules\Api\Admin\LogInvestigation;

use App\Modules\Api\Support\AdminApiSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

readonly class AdminLogInvestigationController
{
    public function __construct(private LogInvestigator $LogInvestigator) {}

    #[AdminApiSchema(static function (): array {
        return AdminLogInvestigationSchema::schema();
    })]
    public function __invoke(Request $Request): JsonResponse
    {
        $Validator = AdminLogInvestigationParameters::validator($Request);

        if ($Validator->fails()) {
            return api_response()->unprocessableEntity($Validator);
        }

        return api_response()->ok(AdminLogInvestigationResponse::from(
            $this->LogInvestigator->investigate($Validator->validated()),
        ));
    }
}
