<?php

namespace App\Class;


use App\Models\SiteTotalAmount;
use App\Models\SupplierTotalAmount;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

trait HelperClass
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Set (Create or Add) Supplier Total Amount
     */
    public function setSupplierTotalAmount(int $supplier_id, float $amount)
    {
        try {

            $supplierTotal = SupplierTotalAmount::where('supplier_id', $supplier_id)->first();

            if ($supplierTotal) {

                $this->updateSupplierTotalAmount($supplier_id, $amount);
            } else {

                SupplierTotalAmount::create([
                    'supplier_id' => $supplier_id,
                    'amount' => $amount,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to set supplier total amount: {$e->getMessage()}");
            throw new \Exception('Unable to set supplier total amount. Please try again.');
        }
    }

    /**
     * Get Supplier Total Amount
     */
    public function getSupplierTotalAmount(int $supplier_id)
    {
        try {
            // Attempt to find a record for the supplier
            $supplierTotal = SupplierTotalAmount::where('supplier_id', $supplier_id)->first();

            if (!$supplierTotal) {
                throw new ModelNotFoundException("Supplier total amount not found for supplier_id: $supplier_id.");
            }

            return $supplierTotal;
        } catch (ModelNotFoundException $e) {
            Log::warning("Supplier total amount not found: {$e->getMessage()}");
            return null; // Return null if no record is found
        } catch (\Exception $e) {
            Log::error("Failed to get supplier total amount: {$e->getMessage()}");
            throw new \Exception('Unable to retrieve supplier total amount. Please try again.');
        }
    }

    /**
     * Update Supplier Total Amount
     */
    public function updateSupplierTotalAmount(int $supplier_id, float $amount)
    {
        try {


            $supplierTotal = SupplierTotalAmount::where('supplier_id', $supplier_id)->first();

            if (!$supplierTotal) {
                throw new ModelNotFoundException("Supplier total amount not found for supplier_id: $supplier_id.");
            }

            $supplierTotal->update(['amount' => $amount]);
        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Exception('Unable to update supplier total amount. Please try again.');
        }
    }

    /**
     * Set (Create or Add) Site Total Amount
     */
    public function setSiteTotalAmount(int $id, float $amount)
    {
        try {


            $siteTotal = SiteTotalAmount::where('phase_id', $id)->first();

            if ($siteTotal) {
    
                $siteTotal->update([
                    'total_amount' => $amount
                ]);
                
            } else {

                $total = SiteTotalAmount::create([
                    'phase_id' => $id,
                    'total_amount' => $amount,
                ]);
            }
        } catch (\Exception $e) {
            throw new \Exception('Unable to set site total amount. Please try again.');
        }
    }

    /**
     * Get Site Total Amount
     */
    public function getSiteTotalAmount(int $site_id)
    {
        try {
            // Attempt to find a record for the site
            $siteTotal = SiteTotalAmount::where('site_id', $site_id)->first();

            if (!$siteTotal) {
                throw new ModelNotFoundException("Site total amount not found for site_id: $site_id.");
            }

            return $siteTotal;
        } catch (ModelNotFoundException $e) {
            Log::warning("Site total amount not found: {$e->getMessage()}");
            return null; // Return null if no record is found
        } catch (\Exception $e) {
            \Log::error("Failed to get site total amount: {$e->getMessage()}");
            throw new \Exception('Unable to retrieve site total amount. Please try again.');
        }
    }

    /**
     * Update Site Total Amount
     */
    public function updateSiteTotalAmount(int $phase_id, float $new_amount)
    {
        try {

            $siteTotal = SiteTotalAmount::where('phase_id', $phase_id)->first();

            if (!$siteTotal) {
                throw new ModelNotFoundException("Site total amount not found for phase: $phase_id.");
            }

            $amount = $siteTotal->total_amount + $new_amount;

            $siteTotal->update([
                'total_amount' => $amount
            ]);

        } catch (ModelNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Exception('Unable to update site total amount. Please try again.');
        }
    }


    public function adjustBalance($new, $old){

        $new_amount = 0;



        if($new > $old){
            $new_amount = $new - $old;
        }
        if($new < $old){
            $new_amount = $new - $old;
        }

        return $new_amount;

    }
}
