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

    /** @var string[] */
    private $completedStatusTextMap;

    /** @var string */
    private $creditCardPaidStatus;

    /** @var string[] */
    private $glsCompletedStatusMap;

    /**
     * StatusService constructor.
     */
    public function __construct() {
        // Teljesített státusz ID-k
        $this->completedStatusMap = [
            'b3JkZXJTdGF0dXMtb3JkZXJfc3RhdHVzX2lkPTU=', // Teljesítve
            'b3JkZXJTdGF0dXMtb3JkZXJfc3RhdHVzX2lkPTI0', // FOXPOST Teljesítve
        ];

        // Teljesített GLS ID-k
        $this->glsCompletedStatusMap = [
            'b3JkZXJTdGF0dXMtb3JkZXJfc3RhdHVzX2lkPTMz=', // --- GLS teljesítve
            'b3JkZXJTdGF0dXMtb3JkZXJfc3RhdHVzX2lkPTE2', // MyGLS Kézbesítve
            'b3JkZXJTdGF0dXMtb3JkZXJfc3RhdHVzX2lkPTIx', // MyGLS csomagpont kézbesítve
        ];

        // Teljesített státusz szövegek alapján
        $this->completedStatusTextMap = ['Teljesítve'];

        $this->creditCardPaidStatus = 'b3JkZXJTdGF0dXMtb3JkZXJfc3RhdHVzX2lkPTE4'; // BK. Függőben lévő
    }

    /**
     * @param  string  $statusName
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getOrderStatusByName(string $statusName) {
        /** @var OrderStatus|null $status */
        $status = OrderStatus::where('name', '=', $statusName)->first();

        return $status;
    }

    /**
     * @param  string  $statusId
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getOrderStatusByID(string $statusId) {
        /** @var OrderStatus|null $status */
        $status = OrderStatus::where('status_id', '=', $statusId)->first();

        return $status;
    }

    /**
     * @param $localOrderId
     * @return bool
     */
    public function isCompleted($localOrderId): bool {
        $localOrder = Order::find($localOrderId);

        return in_array($localOrder->status_text, $this->completedStatusTextMap);
    }

    /**
     * @return string[]
     */
    public function getCompletedStatusTextMap() {
        return $this->completedStatusTextMap;
    }

    /**
     * @param                    $shoprenterOrder
     * @param  \App\Order        $localOrder
     * @param  \App\OrderStatus  $newStatus
     * @return array
     */
    public function handleNewStatus($shoprenterOrder, Order $localOrder, OrderStatus $newStatus): array {
        Log::info(sprintf('StatusService: Státusz változás frissítése (Megrendelés azonosító: %s, Új státusz: %s [Status id: %s])', $localOrder->id, $newStatus->name, $newStatus->status_id));

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

            // Létrehozzuk a bevételt
            $localOrder->updateIncome(date('Y-m-d'));

            $ws->remove($localOrder->id, $reseller->id); // Töröljük a munkalapról
            Log::info('WorksheetService: - Törölve a munkalapról.');

            // Logolunk kicsit, aztán számlázunk
            Log::info(sprintf('Megrendelés teljesítve (Azonosító: %s)', $localOrder->id));

            if ($newStatus->status_id == 'b3JkZXJTdGF0dXMtb3JkZXJfc3RhdHVzX2lkPTI0') {
                Log::info("FoxPost megrendelés, nem hozunk létre számlát");
            } else {
                if ($localOrder->create_invoice) {
                    Log::info('Számla gyártás megkezdése...');

                    if ($localOrder->reseller->use_tharanis) {
                        $invoiceResponse = $localOrder->createTharanisInvoice();
                    } else {
                        $invoiceResponse = $localOrder->createInvoice();
                    }

                    if (! $invoiceResponse['success']) {
                        $response['message'] = $invoiceResponse['message'];
                    }
                    Log::info('... számla elintézve.');
                } else {
                    Log::info('Számla gyártás kihagyása, a Teljesítéskor nem került bepipálásra számla generálás és küldés.');
                }
            }

            $ks->fulfillOrder($shoprenterOrder); // Klaviyo-ba frissítjük a megrendelést
            Log::info('KlaviyoService: - Megrendelés teljesítése rögzítve.');

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
            //if ($newStatus->status_id == $this->creditCardPaidStatus) {
            //    Log::info('Előlegszámla gyártásának megkezdése...');
            //    $invoiceResponse = $localOrder->createAdvanceInvoice();
            //    if (! $invoiceResponse['success']) {
            //        $response['message'] = $invoiceResponse['message'];
            //    }
            //}
            if ($newStatus->status_id == $this->creditCardPaidStatus) {
                Log::info('Előlegszámla gyártás kihagyása, majd éles számlát kap egyből');
            }

            // Ha GLS Teljesítve a státusz, akkor Klaviyo-ba teljesítjük
            if (in_array($newStatus->status_id, $this->glsCompletedStatusMap)) {
                Log::info('GLS teljesítve az új státusz, továbbküldjük Klavyio irányába.');
                $ks->fulfillOrder($shoprenterOrder); // Klaviyo-ba frissítjük a megrendelést
                Log::info('KlaviyoService: - Megrendelés teljesítése rögzítve.');
            }
            $response['message'] = 'Státusz váltás sikeres';
            Log::info('StatusService: Státusz váltás sikeresen teljesítve');
        }

        return $response;
    }

    /**
     * Visszaadja a státuszhoz tartozó színt név alapján.
     *
     * @param  string  $string
     * @return \Illuminate\Database\Eloquent\HigherOrderBuilderProxy|mixed|string
     */
    public function getColorByStatusName(string $string) {
        $os = OrderStatus::where('name', $string)->first();

        if (! $os) {
            Log::warning(sprintf('Nincs ilyen státusz név (%s)', $string));

            return '#000000';
        } else {
            return $os->color;
        }
    }
}
