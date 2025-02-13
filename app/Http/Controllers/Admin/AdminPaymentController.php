<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminPaymentResource;
use App\Models\AdminPayment;
use App\Models\Payment;
use App\Models\Site;
use App\Models\Supplier;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class AdminPaymentController extends Controller
{
    public function index(Request $request)
    {


        $payments = AdminPayment::with('entity')
            ->paginate(10);



        $total_amount = AdminPayment::sum('amount');


//        $id = $request->input('id');

//        $sites = Site::query()
//            ->when(str_starts_with($id, 'site-') && $id !== 'all', function ($query) use ($id) {
//                $query->where('id', $id);
//            })
//            ->whereHas('adminPayments')
//            ->with(['adminPayments'])
//            ->get();
//
//        $suppliers = Supplier::query()
//            ->when(str_starts_with($id, 'supplier-') && $id !== 'all', function ($query) use ($id) {
//                $query->where('id', $id);
//            })
//            ->whereHas('adminPayments')
//            ->with(['adminPayments'])
//            ->get();
//
//
//
//        $data = collect();
//
//
//        $data = $data->merge($sites->flatMap(function ($site) {
//            $received = $site->adminPayments->where('transaction_type', 0);
//            $sent = $site->adminPayments->where('transaction_type', 1);
//
//            return [
//                [
//                    'id' => $site->id,
//                    'type' => 'Site',
//                    'name' => $site->site_name,
//                    'transaction_type' => 'sent',
//                    'entity_type' => Site::class,
//                    'amount' => $sent->sum('amount'),
////                    'admin_payment_ids' => $site->adminPayments->pluck('id')->unique(),
//                    'created_at' => $site->created_at,
//                ],
//                [
//                    'id' => $site->id,
//                    'type' => 'Site',
//                    'name' => $site->site_name,
//                    'transaction_type' => 'received',
//                    'entity_type' => Site::class,
//                    'amount' => $received->sum('amount'),
////                    'admin_payment_ids' => $site->adminPayments->pluck('id')->unique(),
//                    'created_at' => $site->created_at,
//                ]
//            ];
//        }));
//
//        $data = $data->merge($suppliers->flatMap(function ($supplier) {
//            $received = $supplier->adminPayments->where('transaction_type', 0);
//            $sent = $supplier->adminPayments->where('transaction_type', 1);
//
//            return [
//                [
//                    'id' => $supplier->id,
//                    'type' => 'Supplier',
//                    'name' => $supplier->name,
//                    'transaction_type' => 'sent',
//                    'entity_type' => Supplier::class,
//                    'amount' => $sent->sum('amount'),
////                    'admin_payment_ids' => $supplier->adminPayments->pluck('id')->unique(),
//                    'created_at' => $supplier->created_at,
//                ],
//                [
//                    'id' => $supplier->id,
//                    'type' => 'Supplier',
//                    'name' => $supplier->name,
//                    'transaction_type' => 'received',
//                    'entity_type' => Supplier::class,
//                    'amount' => $received->sum('amount'),
////                    'admin_payment_ids' => $supplier->adminPayments->pluck('id')->unique(),
//                    'created_at' => $supplier->created_at,
//                ]
//            ];
//        }));
//
////        dd($data);
//
//        $total_amount = $data->sum('amount');
//
//        $payments = new LengthAwarePaginator(
//            $data->forPage($request->input('page', 1), 10),
//            $data->count(), 10,
//            $request->input('page', 10), [
//                'path' => $request->url(), 'query' => $request->query()
//            ]
//        );
//

        return view('profile.partials.Admin.PaymentSuppliers.manage-payments', compact(
                'payments',
                'total_amount',
            )
        );
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
