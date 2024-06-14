<?php

namespace Reelz222z\CryptoExchange;

class TransactionHistory
{
    private string $filePath;
    private array $transactions;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
        $this->loadTransactions();
    }

    private function loadTransactions(): void
    {
        if (file_exists($this->filePath)) {
            $this->transactions = json_decode(file_get_contents($this->filePath), true) ?? [];
        } else {
            $this->transactions = [];
        }
    }

    public function getTransactions(): array
    {
        return $this->transactions;
    }

    public function addTransaction(string $username, string $date, string $type, string $symbol, float $amount, float $price, float $total): void
    {
        $this->transactions[] = [
            'username' => $username,
            'date' => $date,
            'type' => $type,
            'symbol' => $symbol,
            'amount' => $amount,
            'price' => $price,
            'total' => $total
        ];
        $this->saveTransactions();
    }

    public function saveTransactions(): void
    {
        file_put_contents($this->filePath, json_encode($this->transactions, JSON_PRETTY_PRINT));
    }
}
