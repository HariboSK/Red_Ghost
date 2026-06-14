<?php
declare(strict_types=1);

class CartService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Automatická synchronizácia cien pri každom načítaní služby
        $this->syncCartPrices();
    }

    /**
     * Pomocná metóda na výpočet ceny so zľavou
     */
    private function calculateDiscountedPrice(float $price, int $discount): float
    {
        if ($discount <= 0) return $price;
        return $price - ($price * $discount / 100);
    }

    // Kompletné dáta pre vykreslenie stránky košíka
    public function getFullCartDetails(): array
    {
        $cartItems = [];
        $subtotal = 0.0;
        $count = 0;

        foreach ($_SESSION['cart'] as $productId => $item) {
            $quantity = max(0, (int) ($item['quantity'] ?? 0));
            if ($quantity <= 0) continue;

            $price = (float) ($item['price'] ?? 0);
            $cartItems[] = [
                'id' => (int) ($item['id'] ?? $productId),
                'name' => (string) ($item['name'] ?? 'Produkt'),
                'price' => $price,
                'original_price' => (float) ($item['original_price'] ?? $price),
                'quantity' => $quantity,
                'image' => $this->normalizeImagePath((string) ($item['image'] ?? '')),
            ];

            $count += $quantity;
            $subtotal += $price * $quantity;
        }

        $shipping = $count > 0 ? 3.9 : 0.0;
        $discount = abs((float) ($_SESSION['applied_discount_amount'] ?? 0));
        
        // Spracovanie flash zľavy
        if (isset($_SESSION['discount_flash']) && is_array($_SESSION['discount_flash'])) {
            $discount = abs((float) ($_SESSION['discount_flash']['amount'] ?? 0));
            unset($_SESSION['discount_flash']);
        }

        $subtotalAfterDiscount = max(0, $subtotal - $discount);
        $total = $subtotalAfterDiscount + $shipping;

        return [
            'items' => $cartItems,
            'summary' => [
                'count' => $count,
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'discount' => $discount,
                'total' => $total,
            ]
        ];
    }

    // Rýchly sumár pre API (napr. počítadlo v hlavičke)
    public function getSummary(): array
    {
        $count = 0;
        $total = 0.0;

        foreach ($_SESSION['cart'] as $item) {
            $qty = (int) ($item['quantity'] ?? 0);
            $count += $qty;
            $total += $qty * (float) ($item['price'] ?? 0);
        }

        return [
            'count' => $count,
            'total' => round($total, 2),
        ];
    }

    public function getItemsList(): array
    {
        // Vráti aktuálny stav košíka (ceny už boli synchronizované v konštruktore)
        return array_values($_SESSION['cart']);
    }

    public function clear(): void
    {
        $_SESSION['cart'] = [];
    }

    /**
     * Synchronizuje ceny v session s aktuálnymi cenami a zľavami v DB
     */
    private function syncCartPrices(): void
    {
        foreach ($_SESSION['cart'] as $productId => $item) {
            $dbProduct = $this->getProductById((int) $productId);
            
            // Ak produkt v DB neexistuje, odstránime ho z košíka
            if ($dbProduct === null) {
                unset($_SESSION['cart'][$productId]);
                continue;
            }

            // Aplikujeme výpočet zľavy
            $originalPrice = $dbProduct['price'];
            $finalPrice = $this->calculateDiscountedPrice($originalPrice, $dbProduct['discount']);

            // Aktualizácia dát v session
            $_SESSION['cart'][$productId]['name'] = $dbProduct['name'];
            $_SESSION['cart'][$productId]['price'] = $finalPrice;
            $_SESSION['cart'][$productId]['original_price'] = $originalPrice;
            $_SESSION['cart'][$productId]['image'] = $this->normalizeImagePath((string) ($dbProduct['image'] ?? ''));
        }
    }

    private function getProductById(int $productId): ?array
    {
        // Pridaný stĺpec 'discount' do SELECT
        $stmt = $this->db->prepare('SELECT id_product AS id, name, price, image, stock, discount FROM product WHERE id_product = :id LIMIT 1');
        $stmt->execute(['id' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            return null;
        }

        return [
            'id' => (int) $product['id'],
            'name' => (string) $product['name'],
            'price' => (float) $product['price'],
            'discount' => (int) ($product['discount'] ?? 0),
            'stock' => (float) $product['stock'],
            'image' => (string) ($product['image'] ?? ''),
        ];
    }

    private function normalizeImagePath(string $image): string
    {
        $image = trim($image);
        if ($image === '') {
            return '/assets/images/omacka3.webp';
        }
        if (preg_match('~^(https?:)?//~i', $image) === 1 || strpos($image, '/') === 0) {
            return preg_replace('~\.(jpe?g)$~i', '.webp', $image);
        }
        return preg_replace('~\.(jpe?g)$~i', '.webp', '/assets/images/' . ltrim($image, '/'));
    }
}