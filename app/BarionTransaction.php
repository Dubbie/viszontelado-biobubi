<?php

namespace App;

use Illuminate\Support\Carbon;

class BarionTransaction
{
	// 1: Tranzakció időpontja
	public ?Carbon $transactionDate;
	// 2: Tranzakció típusa
	public ?string $transactionType;
	// 3: Vásárló / Kedvezmtesyzezett
	public ?string $customer;
	// 4: Tranzakzion osszege
	public ?float $transactionAmount;
	// 5: Tranzakzion utani egyenleg
	public ?float $transactionBalance;
	// 6: Deviza
	public ?string $transactionCurrencyIso;
	// 7: Fizetés elfogadóhely általi azonosítója
	public ?string $paymentAcceptanceId;
	// 8: Tranzakció elfogadóhely általái azonosítója
	public ?string $transactionAcceptanceId;
	// 9: Fizetés Barion azonosító
	public ?string $paymentBarionId;
	// 10: Tranzakció Barion azonosító
	public ?string $transactionBarionId;
	// 11: Megrendelés elfogadóhely általi azonosítója
	public ?string $orderAcceptanceId;
	// 14: Megjegyzés
	public ?string $comment;
}
