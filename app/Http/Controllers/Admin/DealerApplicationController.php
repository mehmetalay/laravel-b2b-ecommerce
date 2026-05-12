<?php

namespace App\Http\Controllers\Admin;

use App\Application\DealerApplication\Exceptions\DealerApplicationDocumentNotFoundException;
use App\Application\DealerApplication\Services\DealerApplicationAdminService;
use App\Application\DealerApplication\Services\DealerApplicationAuditService;
use App\Http\Controllers\Controller;
use App\Models\DealerApplication;
use Illuminate\Http\Request;
use Throwable;

class DealerApplicationController extends Controller
{
    public function __construct(
        private DealerApplicationAdminService $dealerApplicationAdminService,
        private DealerApplicationAuditService $dealerApplicationAuditService
    ) {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $page = max(1, (int) $request->get('page', 1));
        $perPage = (int) $request->get('per_page', 20);
        $perPage = max(1, min($perPage, 100));

        $items = $this->dealerApplicationAdminService->paginate(
            name: $request->get('name'),
            page: $page,
            perPage: $perPage
        );

        return view('backend.pages.dealers.dealer-applications.index', compact('items'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(DealerApplication $dealer_application)
    {
        $dealer_application = $this->dealerApplicationAdminService->findById((int) $dealer_application->id);

        return view('backend.pages.dealers.dealer-applications.show', compact('dealer_application'));
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy(DealerApplication $dealer_application)
    {
        try {
            $this->dealerApplicationAdminService->delete($dealer_application, auth('admin')->id());

            return response()->json([
                'status' => 'success',
            ]);
        } catch (Throwable $e) {
            $this->dealerApplicationAuditService->error('Dealer application admin-destroy exception', [
                'exception' => $e->getMessage(),
                'dealer_application_id' => (int) $dealer_application->id,
                'admin_id' => auth('admin')->id(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Silme işlemi sırasında bir hata oluştu.',
            ], 400);
        }
    }

    public function download(Request $request)
    {
        try {
            return $this->dealerApplicationAdminService->downloadByPath((string) $request->get('path', ''));
        } catch (DealerApplicationDocumentNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (Throwable $e) {
            $this->dealerApplicationAuditService->error('Dealer application admin-download exception', [
                'exception' => $e->getMessage(),
                'admin_id' => auth('admin')->id(),
            ]);
            abort(404);
        }
    }
}
