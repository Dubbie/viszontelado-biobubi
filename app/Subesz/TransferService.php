<?php

namespace App\Subesz;

use App\MoneyTransfer;
use App\MoneyTransferOrder;
use App\Order;
use App\User;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class TransferService
{
    /**
     * @param $userId
     * @return \App\MoneyTransfer|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getTransfersQueryByUser($userId) {
        $user = User::find($userId);
        if ($user->admin) {
            return MoneyTransfer::query();
        }

        return $user->moneyTransfers();
    }

    /**
     * @param $resellerId
     * @param $orderIds
     * @param $reducedValues
     * @param $amount
     * @return array
     */
    public function storeTransfer($resellerId, $orderIds, $reducedValues, $amount): array {
        $response = [
            'success'       => false,
            'message'       => '',
            'moneyTransfer' => null,
            'errors'        => 0,
        ];

        // 1. Létrehozzuk az átutalást
        $mt          = new MoneyTransfer();
        $mt->user_id = $resellerId;
        $mt->amount  = $amount;
        $mt->save();

        // 2. Átutaláshoz tartozó megrendelések elmentése
        $mtoSuccess = [];
        foreach ($orderIds as $orderId) {
            // Ellenőrizzük le, hogy van-e már a megrendeléshez elmentve átutalás
            if (! MoneyTransferOrder::where('order_id', $orderId)->first()) {
                $mto                = new MoneyTransferOrder();
                $mto->transfer_id   = $mt->id;
                $mto->order_id      = $orderId;
                $mto->reduced_value = $reducedValues[$orderId];
                $mto->save();

                $mtoSuccess[] = $mto;
            } else {
                $response['errors']++;
                Log::warning(sprintf('Ehez a megrendeléshez már van tartozó átutalás! (Megr.Azonosító: %s)', $orderId));
            }
        }

        if (count($mtoSuccess) > 0) {
            // Minden jó volt
            $response['success']       = true;
            $response['message']       = 'Átutalás sikeresen rögzítve';
            $response['moneyTransfer'] = $mt;
        } else {
            // Nem jött létre egy sem, töröljük az átutalást is
            $response['success'] = false;
            $response['message'] = 'Nem lett rögzítve egy megrendelés sem, mert már léteznek';

            try {
                $mt->delete();
            } catch (Exception $e) {
                Log::error('Hiba történt az átutalás törlésekor');
            }
        }

        return $response;
    }

    /**
     * @param  \Illuminate\Http\UploadedFile  $uploadedFile
     * @return array
     */
    public function getDataFromCsv(UploadedFile $uploadedFile): array {
        $data      = str_getcsv($uploadedFile->get(), "\n");
        $i         = 0;
        $errCount  = 0;
        $headers   = [];
        $transfers = [];
        foreach ($data as &$row) {
            $row = str_getcsv($row, ';');
            if ($i == 0) {
                $headers = $row;
            }
            $i++;
        }
        unset($data[0]); // Kiszedjük a headert az adathalmazból
        foreach ($data as &$row) {
            // Leszedjük a fölösleget a végéről...
            for ($i = 0; $i <= count($row) + 1; $i++) {
                if ($i >= count($headers)) {
                    unset($row[$i]);
                }
            }
            $row = array_combine($headers, $row);

            /**
             * MINTA:
             * "Tranzakció státusza" => "COMPLETED"
             * "Fizetés típusa" => "Bankkártyás fizetés"
             * "SimplePay tranzakció ID" => "186476008"
             * "Kereskedői tranzakció ID" => "="15333""
             * "Tranzakció dátuma" => "2021-07-29 10:20:40"
             * "Teljesítés dátuma" => "2021-07-29 10:22:24"
             * "Devizanem" => "HUF"
             * "Tranzakciós jutalék" => "188,00"
             * "Bankközi jutalék" => "0,00"
             * "Kártyatársasági díj" => "21,00"
             * "Kereskedői díj" => "167,00"
             * "Tranzakció összege" => "8290,00"
             * "Jutalékkal csökkentett összeg" => "8102,00"
             * "Partner" => "Biobubi Franchise Kft."
             * "Fiók név" => "Biobubi Franchise Kft."
             * "Fiók URL" => "https://biobubi.hu"
             * "Vásárló" => "Vásárló neve"
             * "E-mail cím" => "vasarlo@teszt.hu"
             * "" => ""
             */

            // Keressük meg a megfelelő megrendelést
            $innerId = preg_replace('/\D/', '', $row["Kereskedői tranzakció ID"]);
            $order   = Order::where('inner_id', $innerId)->first();
            if (! $order) {
                Log::warning(sprintf('Nincs ilyen megrendelés nálunk (Shoprenter azonosító: %s)', $innerId));
                $errCount++;
                continue;
            }

            $row['localOrder']                        = $order;
            $transfers[strval($order->reseller_id)][] = $row;
        }

        return $transfers;
    }
}