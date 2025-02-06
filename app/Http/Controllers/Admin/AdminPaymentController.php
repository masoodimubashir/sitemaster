<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminPaymentResource;
use App\Models\AdminPayment;
use App\Models\Site;
use App\Models\Supplier;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class AdminPaymentController extends Controller
{
    public function index()
    {

        $sites = Site::where('is_on_going', 1)->latest()->get();
        $suppliers = Supplier::latest()->get();

        $entities = collect([
            ...$sites->map(function ($site) {
                return [
                    'id' => $site->id,
                    'name' => $site->site_name,
                    'type' => 'site',
                ];
            }),
            ...$suppliers->map(function ($supplier) {
                return [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'type' => 'Supplier',
                ];
            })
        ]);

        $payment_banks = AdminPayment::query()
            ->with('entity')
            ->latest()
            ->paginate(10);


        $total_amount = AdminPayment::sum('amount');

        return view('profile.partials.Admin.PaymentSuppliers.manage-payments', compact(
                'entities',
                'payment_banks',
                'total_amount')
        );
    }

    public function storeOrUpdate(Request $request, $id = null)
    {
        try {


            $validated = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:1',
                'entity' => 'required',
                'transaction_type' => 'required|in:1,0',
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Form Fields Are Missing.',
                    'errors' => $validated->errors()->toArray(),
                ], 422);
            }

            $validatedData = $validated->validated();

            [$entity_type, $entity_id] = explode('-', $validatedData['entity']);

            if ($entity_type == 'site') {
                $type = Site::class;
            } else {
                $type = Supplier::class;
            }

            $data = [
                'entity_type' => $type,
                'entity_id' => $entity_id,
                'amount' => $validatedData['amount'],
                'transaction_type' => $validatedData['transaction_type'],
            ];



            if ($id) {

dd($id);
                $payment = AdminPayment::findOrFail($id);

                $payment->update($data);

                return response()->json([
                    'status' => true,
                    'message' => 'Payment updated successfully!',
                ]);

            } else {

                AdminPayment::create($data);

                return response()->json([
                    'status' => true,
                    'message' => 'Payment created successfully!',
                ]);

            }

        } catch (Exception $e) {
            // Catch any exceptions during execution
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {

        try {

            $payment = AdminPayment::find($id);

            if (!$payment) {
                return response()->json([
                    'error' => true,
                    'message' => 'Payment not found',
                ], 404);
            }

            return response()->json(new AdminPaymentResource($payment), Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Something went wrong!',
            ]);
        }

    }


}
