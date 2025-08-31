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
    public function index(Request $request)
    {
        $query = AdminPayment::with('entity'); // Keep eager loading like old index

        // Filter by date range if provided
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Calculate total based on the filtered results
        $total_amount = $query->sum('amount');

        // Clone query for paginated results
        $payments = (clone $query)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $suppliers = Supplier::select('id', 'name')->get();

        return view(
            'profile.partials.Admin.PaymentSuppliers.manage-payments',
            compact('payments', 'suppliers', 'total_amount')
        )->with([
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ]);
    }


    public function storeOrUpdate(Request $request, $id = null)
    {

        try {


            $rules = [
                'amount' => 'required|numeric|min:1',
                'entity' => 'required',
                'transaction_type' => 'required|in:1,0',
            ];

            if ($request->has('product_id')) {
                $rules['product_id'] = 'required|exists:products,id';
            }

            $validated = Validator::make($request->all(), $rules);

            if ($validated->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Form Fields Are Missing.',
                    'errors' => $validated->errors()->toArray(),
                ], 422);
            }

            $validatedData = $validated->validated();
            [$entity_type, $entity_id] = explode('-', $validatedData['entity']);
            $type = $entity_type === 'site' ? Site::class : Supplier::class;

            $data = [
                'entity_type' => $type,
                'entity_id' => $entity_id,
                'amount' => $validatedData['amount'],
                'transaction_type' => $validatedData['transaction_type'],
            ];


            // Include product_id if provided
            if (isset($validatedData['product_id'])) {
                $data['product_id'] = $validatedData['product_id'];
            }

            if ($id) {

                $payment = AdminPayment::findOrFail($id);
                $payment->update($data);
                return response()->json([
                    'status' => true,
                    'message' => 'Payment updated successfully!'
                ]);
            } else {
                AdminPayment::create($data);
                return response()->json([
                    'status' => true,
                    'message' => 'Payment created successfully!'
                ]);
            }
        } catch (Exception $e) {
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
