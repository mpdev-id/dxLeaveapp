<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeEntitlementResource;
use App\Models\EmployeeEntitlement;
use App\Services\EntitlementService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EmployeeEntitlementController extends Controller
{
    protected $entitlementService;

    public function __construct(EntitlementService $entitlementService)
    {
        $this->entitlementService = $entitlementService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $entitlements = $this->entitlementService->getEntitlements();
        return ResponseFormatter::success(
            EmployeeEntitlementResource::collection($entitlements),
            'Employee entitlements retrieved successfully'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $entitlement = $this->entitlementService->createEntitlement($request->all());
            return ResponseFormatter::success(
                new EmployeeEntitlementResource($entitlement),
                'Employee entitlement created successfully'
            );
        } catch (ValidationException $e) {
            return ResponseFormatter::error(
                ['errors' => $e->errors()],
                'Validation failed',
                422
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $entitlement = $this->entitlementService->findEntitlementById($id);
        if (!$entitlement) {
            return ResponseFormatter::error(null, 'Employee entitlement not found', 404);
        }
        return ResponseFormatter::success(
            new EmployeeEntitlementResource($entitlement),
            'Employee entitlement retrieved successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmployeeEntitlement $employeeEntitlement)
    {
        try {
            $updatedEntitlement = $this->entitlementService->updateEntitlement($employeeEntitlement, $request->all());
            return ResponseFormatter::success(
                new EmployeeEntitlementResource($updatedEntitlement),
                'Employee entitlement updated successfully'
            );
        } catch (ValidationException $e) {
            return ResponseFormatter::error(
                ['errors' => $e->errors()],
                'Validation failed',
                422
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeEntitlement $employeeEntitlement)
    {
        $this->entitlementService->deleteEntitlement($employeeEntitlement);
        return ResponseFormatter::success(null, 'Employee entitlement deleted successfully');
    }
}
