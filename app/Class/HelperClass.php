<?php

namespace App\Class;


use App\Models\SiteTotalAmount;
use App\Models\SupplierTotalAmount;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class HelperClass
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
            Log::warning("Unable to update supplier total amount: {$e->getMessage()}");
            throw $e;
        } catch (\Exception $e) {
            Log::error("Failed to update supplier total amount: {$e->getMessage()}");
            throw new \Exception('Unable to update supplier total amount. Please try again.');
        }
    }

    /**
     * Set (Create or Add) Site Total Amount
     */
    public function setSiteTotalAmount(int $site_id, float $amount)
    {
        try {
            // Check if a record already exists for the site
            $siteTotal = SiteTotalAmount::where('site_id', $site_id)->first();

            if ($siteTotal) {
                // If it exists, update its amount instead of creating a new record
                $this->updateSiteTotalAmount($site_id, $amount);
            } else {
                // Create a new site total amount record
                SiteTotalAmount::create([
                    'site_id' => $site_id,
                    'amount' => $amount,
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Failed to set site total amount: {$e->getMessage()}");
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
    public function updateSiteTotalAmount(int $site_id, float $amount)
    {
        try {
            $siteTotal = SiteTotalAmount::where('site_id', $site_id)->first();

            if (!$siteTotal) {
                throw new ModelNotFoundException("Site total amount not found for site_id: $site_id.");
            }

            // Update the site's total amount
            $siteTotal->update(['amount' => $amount]);
        } catch (ModelNotFoundException $e) {
            Log::warning("Unable to update site total amount: {$e->getMessage()}");
            throw $e;
        } catch (\Exception $e) {
            Log::error("Failed to update site total amount: {$e->getMessage()}");
            throw new \Exception('Unable to update site total amount. Please try again.');
        }
    }

}
