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

        $sites = Site::query()->whereHas('adminPayments')->with('adminPayments')->withSum('adminPayments', 'amount')->get();

        $suppliers = Supplier::query()->whereHas('adminPayments')->with('adminPayments')->withSum('adminPayments', 'amount')->get();


        $payemnts = collect();

        $payments = $payemnts->merge($sites->map(function ($site) {
            return [
                'id' => $site->id,
                'name' => $site->site_name,
                'amount' => $site->admin_payments_sum_amount ?? 0,
                'type' => 'site'
            ];
        }));


        $payments = $payments->merge($suppliers->map(function ($supplier) {
            return [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'amount' => $supplier->admin_payments_sum_amount ?? 0,
                'type' => 'supplier'
            ];
        }));


        return view('profile.partials.Admin.PaymentSuppliers.manage-payments', compact('payments'));
    }

    public function storeOrUpdate(Request $request, $id = null)
    {

        try {

            $rules = ['amount' => 'required|numeric|min:1', 'entity' => 'required', 'transaction_type' => 'required|in:1,0',];

            if ($request->has('product_id')) {
                $rules['product_id'] = 'required|exists:products,id';
            }

            $validated = Validator::make($request->all(), $rules);

            if ($validated->fails()) {
                return response()->json(['status' => false, 'message' => 'Form Fields Are Missing.', 'errors' => $validated->errors()->toArray(),], 422);
            }

            $validatedData = $validated->validated();
            [$entity_type, $entity_id] = explode('-', $validatedData['entity']);
            $type = $entity_type === 'site' ? Site::class : Supplier::class;

            $data = ['entity_type' => $type, 'entity_id' => $entity_id, 'amount' => $validatedData['amount'], 'transaction_type' => $validatedData['transaction_type'],];


            // Include product_id if provided
            if (isset($validatedData['product_id'])) {
                $data['product_id'] = $validatedData['product_id'];
            }

            if ($id) {

                $payment = AdminPayment::findOrFail($id);
                $payment->update($data);
                return response()->json(['status' => true, 'message' => 'Payment updated successfully!']);
            } else {
                AdminPayment::create($data);
                return response()->json(['status' => true, 'message' => 'Payment created successfully!']);
            }
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Something went wrong!', 'error' => $e->getMessage(),], 500);
        }
    }

    public function edit($id)
    {

        try {

            $payment = AdminPayment::find($id);

            if (!$payment) {
                return response()->json(['error' => true, 'message' => 'Payment not found',], 404);
            }

            return response()->json(new AdminPaymentResource($payment), Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json(['error' => true, 'message' => 'Something went wrong!',]);
        }

    }


}
