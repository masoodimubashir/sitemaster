<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConstructionMaterialBilling;
use App\Models\DailyExpenses;
use App\Models\DailyWager;
use App\Models\SquareFootageBill;
use App\Models\WagerAttendance;
use Illuminate\Http\Request;

class AdminVerificationController extends Controller
{


    public function verifyConstructionMaterials($id)
    {
        try {

            $material = ConstructionMaterialBilling::findOrFail($id);
            $material->verified_by_admin = !$material->verified_by_admin;
            $material->save();

            // return response()->json([
            //     'message' => 'Verification status updated successfully'
            // ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating verification status'
            ], 500);
        }
    }



    public function verifySquareFootage(string $id)
    {

        try {
            $bill = SquareFootageBill::find($id);

            $bill->update([
                'is_verified' => !$bill->varified_by_admin,
            ]);

            // return response()->json([
            //     'message' => 'Verification status updated successfully'
            // ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error updating verification status'
            ], 500);
        }
    }

    public function verifyExpenses(string $id)
    {


        try {
            $expense = DailyExpenses::find($id);

            $expense->update([
                'verified_by_admin' => !$expense->verified_by_admin,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error updating verification status'
            ], 500);
        }
    }

    public function verifyDailyWagers(string $id)
    {

        try {
            $wager = DailyWager::find($id);

            $wager->update([
                'is_verified' => $wager->is_verified ? false : true,
            ]);

            // return response()->json([
            //     'message' => 'Verification status updated successfully'
            // ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error updating verification status'
            ], 500);
        }
    }

    public function verifyAttendance(string $id)
    {
        try {
            $attendance = WagerAttendance::find($id);

            $attendance->update([
                'is_verified' => $attendance->is_verified ? false : true,
            ]);
            // return response()->json([
            //     'message' => 'Verification status updated successfully'
            // ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error updating verification status'
            ], 500);
        }
    }
}
