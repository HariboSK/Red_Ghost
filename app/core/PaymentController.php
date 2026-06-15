<?php
declare(strict_types=1);

class PaymentController
{
    //Získa dáta pre zobrazenie košíka a platby.
    public function getCheckoutData(): array
    {
        $cartItems = [];
        $cartSummary = [
            'count' => 0,
            'subtotal' => 0.0,
            'shipping' => 0.0,
            'discount' => 0.0,
            'total' => 0.0,
        ];

        // Načítanie položiek zo SESSION
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $productId => $item) {
                $quantity = max(0, (int) ($item['quantity'] ?? 0));
                if ($quantity <= 0) continue;

                $productId = (int) ($item['id'] ?? $productId);
                $price = (float) ($item['price'] ?? 0);
                
                $cartItems[] = [
                    'id' => $productId,
                    'name' => (string) ($item['name'] ?? 'Produkt'),
                    'price' => $price,
                    'quantity' => $quantity,
                    'image' => $this->normalizeImagePath((string) ($item['image'] ?? '')),
                ];

                $cartSummary['count'] += $quantity;
                $cartSummary['subtotal'] += $price * $quantity;
            }
        }

        // Výpočet súhrnu
        $cartSummary['shipping'] = $cartSummary['count'] > 0 ? 3.9 : 0.0;
        $cartSummary['discount'] = abs((float) ($_SESSION['applied_discount_amount'] ?? 0));
        $subtotalAfterDiscount = max(0, $cartSummary['subtotal'] - $cartSummary['discount']);
        $cartSummary['total'] = $subtotalAfterDiscount + $cartSummary['shipping'];

        return [
            'items' => $cartItems,
            'summary' => $cartSummary
        ];
    }

    private function normalizeImagePath(string $image): string
    {
        $image = trim($image);
        if ($image === '') return '/assets/images/omacka3.webp';
        
        if (preg_match('~^(https?:)?//~i', $image) === 1 || strpos($image, '/') === 0) {
            return preg_replace('~\.(jpe?g)$~i', '.webp', $image);
        }
        return preg_replace('~\.(jpe?g)$~i', '.webp', '/assets/images/' . ltrim($image, '/'));
    }
}