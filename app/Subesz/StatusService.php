<?php

namespace App\Subesz;

use App\Delivery;
use App\Order;
use App\OrderStatus;
use Exception;
use Illuminate\Support\Facades\Log;

class StatusService
{
    /** @var string[] */
    private $completedStatusMap;

    /** @var string */
    private $creditCardPaidStatus;

    /**
     * StatusService constructor.
     */
    public function __construct()
    {
        // Teljesített státusz ID-k
        $this->completedStatusMap = [
            'b3JkZXJTdGF0dXMtb3JkZXJfc3RhdHVzX2lkPTU=', // Teljesítve
            'b3JkZXJTdGF0dXMtb3JkZXJfc3RhdHVzX2lkPTI0', // FOXPOST Teljesítve
        ];

        $this->creditCardPaidStatus = 'b3JkZXJTdGF0dXMtb3JkZXJfc3RhdHVzX2lkPTE4'; // BK. Függőben lévő
    }

    /**
     * @param  string  $statusName
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getOrderStatusByName(string $statusName)
    {
        /** @var OrderStatus|null $status */
        $status = OrderStatus::where('name', '=', $statusName)->first();

        return $status;
    }

    /**
     * @param  string  $statusId
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getOrderStatusByID(string $statusId)
    {
        /** @var OrderStatus|null $status */
        $status = OrderStatus::where('status_id', '=', $statusId)->first();

        return $status;
    }

    /**
     * @param                    $shoprenterOrder
     * @param  \App\Order        $localOrder
     * @param  \App\OrderStatus  $newStatus
     * @return array
     */
    public function handleNewStatus($shoprenterOrder, Order $localOrder, OrderStatus $newStatus): array
    {
        Log::info('StatusService: Státusz változás frissítése');
        // Szervizek
        $ks = resolve('App\Subesz\KlaviyoService');
        $ws = resolve('App\Subesz\WorksheetService');
        $ss = resolve('App\Subesz\StockService');

        $reseller = $localOrder->reseller;
        $response = [
            'success' => false,
            'message' => 'Inicializálva',
        ];

        // Ha Teljesítve státuszba került (Több is lehet), akkor rögzítjük az adatokat
        if (in_array($newStatus->status_id, $this->completedStatusMap)) {
            // Létrehozzuk a Kiszállítást
            $delivery           = new Delivery();
            $delivery->user_id  = $reseller->id;
            $delivery->order_id = $localOrder->id;
            $delivery->save();

            $ks->fulfillOrder($shoprenterOrder); // Klaviyo-ba frissítjük a megrendelést
            Log::info('KlaviyoService: - Megrendelés teljesítése rögzítve.');
            $ws->remove($localOrder->id, $reseller->id); // Töröljük a munkalapról
            Log::info('WorksheetService: - Törölve a munkalapról.');
            $ss->subtractStockFromOrder($localOrder->id); // Levonjuk a készletet
            Log::info('StockService: - Készlet levonva.');

            // Logolunk kicsit, aztán számlázunk
            Log::info(sprintf('Megrendelés teljesítve (Azonosító: %s)', $localOrder->id));
            Log::info('Számla gyártás megkezdése...');
            $invoiceResponse = $localOrder->createInvoice();
            if (! $invoiceResponse['success']) {
                $response['message'] = $invoiceResponse['message'];
            }
            Log::info('... számla elintézve.');

            $response['success'] = true;
            $response['message'] = 'Megrendelés sikeresen teljesítve (Minden folyamat lefutott)';
            Log::info('Megrendelés sikeresen teljesítve (Minden folyamat lefutott)');
        } else {
            $response['message'] = 'Sikeres állapot váltás.';
            /** @var Delivery $delivery */
            $delivery = Delivery::where('order_id', $localOrder->id)->first();
            if ($delivery) {
                try {
                    $delivery->delete();
                    $response['success'] = true;
                    $response['message'] = 'Sikeres állapot váltás, kiszállítási adatok törölve';
                    Log::info('Sikeres állapot váltás, kiszállítási adatok törölve');
                } catch (Exception $e) {
                    $response['message'] = 'Hiba a kiszállítás törlésekor';
                    Log::error('Hiba a kiszállítás törlésekor');
                }
            }

            // Ha bankkártyás fizetés, akkor a számlát elküldjük
            if ($newStatus->status_id == $this->creditCardPaidStatus) {
                $invoiceResponse = $localOrder->createInvoice();
                if (! $invoiceResponse['success']) {
                    $response['message'] = $invoiceResponse['message'];
                }
            }

            $response['success'] = true;
            $response['message'] = 'Státusz váltás sikeres';
            Log::info('StatusService: Státusz váltás sikeresen teljesítve');
        }

        return $response;
    }
}
