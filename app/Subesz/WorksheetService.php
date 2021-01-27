<?php


namespace App\Subesz;


use App\Worksheet;
use Exception;
use Log;

class WorksheetService
{
    /**
     * @param $orderId
     * @param $userId
     * @return bool
     */
    public function remove($orderId, $userId): bool
    {
        /** @var Worksheet $wse */
        $wse = Worksheet::where([
            ['order_id', '=', $orderId],
            ['user_id', '=', $userId],
        ])->first();

        if (!$wse) {
            Log::info('Nem található a munkalap bejegyzés a felhasználóhoz, ezért nem töröljük.');
            return true;
        }

        try {
            $wse->delete();
        } catch (Exception $e) {
            Log::error('Hiba történt a munkalap bejegyzés törlésekor.');
        }

        return true;
    }
}
