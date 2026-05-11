<?php

namespace App\Http\Controllers;

use App\Application\DealerApplication\Exceptions\DealerApplicationValidationException;
use App\Application\DealerApplication\Services\{DealerApplicationAuditService, DealerApplicationPublicService};
use Illuminate\Http\Request;
use Throwable;

class DealershipApplicationController extends Controller
{
    public function __construct(
        private DealerApplicationPublicService $dealerApplicationPublicService,
        private DealerApplicationAuditService $dealerApplicationAuditService
    ) {}

    public function index()
    {
        return view('dealer-applications.create');
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        try {
            $documents = $request->hasFile('documents')
                ? (array) $request->file('documents')
                : [];
            $payload = $request->only([
                'company_name',
                'tax_office',
                'tax_number',
                'city',
                'district',
                'address',
                'authorized_name_surname',
                'identity_number',
                'phone_number',
                'mobile_phone_number',
                'fax_number',
                'email_address',
                'web_address',
            ]);

            $this->dealerApplicationPublicService->submit(
                payload: $payload,
                documents: $documents,
                ipAddress: (string) $request->ip()
            );

            return response()->json(['success' => true]);
        } catch (DealerApplicationValidationException $e) {
            return response()->json([
                'warning' => $e->getMessage(),
            ]);
        } catch (Throwable $e) {
            $this->dealerApplicationAuditService->error('Dealer application public-store exception', [
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => trans('translations.istek_sirasinda_bir_hata_olustu_lutfen_daha_sonra_tekrar_deneyiniz'),
            ], 400);
        }
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
