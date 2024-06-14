<?php

namespace Reelz222z\CryptoExchange;

class User
{
    private string $name;
    private Wallet $wallet;
    private array $portfolio;

    public function __construct(string $name, Wallet $wallet, array $portfolio = [])
    {
        $this->name = $name;
        $this->wallet = $wallet;
        $this->portfolio = $portfolio;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    public function getPortfolio(): array
    {
        return $this->portfolio;
    }

    public function buyCryptocurrency(Cryptocurrency $crypto, float $amount): void
    {
        $this->wallet->deduct($crypto->getQuote()->getPrice() * $amount);
        $symbol = $crypto->getSymbol();
        if (!isset($this->portfolio[$symbol])) {
            $this->portfolio[$symbol] = [];
        }
        $this->portfolio[$symbol][] = [
            'date' => date('Y-m-d H:i:s'),
            'symbol' => $crypto->getSymbol(),
            'amount' => $amount,
            'price' => $crypto->getQuote()->getPrice(),
            'total' => $crypto->getQuote()->getPrice() * $amount,
            'last_updated' => $crypto->getQuote()->getLastUpdated(),
        ];
    }

    public function sellCryptocurrency(Cryptocurrency $crypto, float $amount): void
    {
        $symbol = $crypto->getSymbol();
        $totalAmount = 0;

        foreach ($this->portfolio[$symbol] as $key => $item) {
            $totalAmount += $item['amount'];
        }

        if ($totalAmount < $amount) {
            throw new \Exception('Insufficient cryptocurrency to sell');
        }

        foreach ($this->portfolio[$symbol] as $key => &$item) {
            if ($amount <= 0) break;

            if ($item['amount'] <= $amount) {
                $amount -= $item['amount'];
                unset($this->portfolio[$symbol][$key]);
            } else {
                $item['amount'] -= $amount;
                $amount = 0;
            }
        }

        $this->portfolio[$symbol] = array_values($this->portfolio[$symbol]);
        $this->wallet->add($crypto->getQuote()->getPrice() * $amount);
    }

    public static function loadUsers(string $filePath): array
    {
        $data = json_decode(file_get_contents($filePath), true);
        $users = [];
        foreach ($data as $userData) {
            $wallet = new Wallet($userData['wallet']);
            $portfolio = $userData['portfolio'] ?? [];
            $users[] = new self($userData['name'], $wallet, $portfolio);
        }
        return $users;
    }

    public static function saveUsers(string $filePath, array $users): void
    {
        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'name' => $user->getName(),
                'wallet' => $user->getWallet()->getBalance(),
                'portfolio' => $user->getPortfolio()
            ];
        }
        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
    }
}
