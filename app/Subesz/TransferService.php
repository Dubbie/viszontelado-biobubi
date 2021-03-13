<?php

namespace App\Subesz;

use App\MoneyTransfer;
use App\MoneyTransferOrder;
use Exception;
use Illuminate\Support\Facades\Log;

class TransferService
{
    /**
     * @param $resellerId
     * @param $orderIds
     * @param $amount
     * @return array
     */
    public function storeTransfer($resellerId, $orderIds, $amount): array {
        $response = [
            'success'       => false,
            'message'       => '',
            'moneyTransfer' => null,
        ];

        // 1. Létrehozzuk az átutalást
        $mt          = new MoneyTransfer();
        $mt->user_id = $resellerId;
        $mt->amount  = $amount;
        $mt->save();

        // 2. Átutaláshoz tartozó megrendelések elmentése
        $mtoSuccess = [];
        foreach ($orderIds as $orderId) {
            $mto              = new MoneyTransferOrder();
            $mto->transfer_id = $mt->id;
            $mto->order_id    = $orderId;
            $mto->save();

            $mtoSuccess[] = $mto;
        }

        // Hibakezelés
        if (count($mtoSuccess) != count($orderIds)) {
            Log::error('Hiba történt az átutalás elmentésekor');
            Log::error('- Nem jött létre mindegyik megrendelés elem');
            $response['message'] = 'Hiba történt az átutalás elmentésekor';

            foreach ($mtoSuccess as $mto) {
                try {
                    $mto->delete();
                } catch (Exception $e) {
                    Log::error('Hiba az átutaláshoz tartozó megrendelés elem törlésekor.');
                }
            }

            try {
                $mt->delete();
            } catch (Exception $e) {
                Log::error('Hiba az átutalás törlésekor.');
                $response['message'] = 'Hiba az átutalás törlésekor.';
            }
        }

        // Minden jó volt
        $response['success']       = true;
        $response['message']       = 'Átutalás sikeresen rögzítve';
        $response['moneyTransfer'] = $mt;

        return $response;
    }
}