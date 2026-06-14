<?php
declare(strict_types=1);

class ProductController
{
    private ProductModel $productModel;
    private ProductReviewModel $reviewModel;
    private array $sessionUser;

    public function __construct(PDO $pdo, array $sessionUser)
    {
        $this->productModel = new ProductModel($pdo);
        $this->reviewModel = new ProductReviewModel($pdo);
        $this->sessionUser = $sessionUser;
    }

    // Získa dáta pre produktovú stránku vrátane výpočtu zliav.
    public function getProductPageData(int $productId): array
    {
        $product = $this->productModel->findById($productId);

        // Logika pre výpočet zľavy (ak existuje)
        if (!empty($product) && is_array($product)) {
            $originalPrice = (float) ($product['price'] ?? 0);
            $discount = (float) ($product['discount'] ?? 0);

            if ($discount > 0) {
                // Výpočet: cena - (cena * percento / 100)
                $product['discounted_price'] = $originalPrice - ($originalPrice * ($discount / 100));
                $product['has_discount'] = true;
            } else {
                $product['discounted_price'] = $originalPrice;
                $product['has_discount'] = false;
            }
        }

        return [
            'product'    => $product,
            'reviews'    => $this->reviewModel->getApprovedByProduct($productId),
            'summary'    => $this->reviewModel->getSummary($productId),
            'userReview' => ($this->sessionUser['id'] ?? 0) > 0 
                            ? $this->reviewModel->getUserReview($productId, (int)$this->sessionUser['id']) 
                            : null
        ];
    }

    public function handleReviewSubmission(int $productId, array $postData): void
    {
        $reviewErrors = [];
        
        // 1. Validácia dát
        $rating = filter_var($postData['rating'] ?? 5, FILTER_VALIDATE_INT);
        $title = trim((string) ($postData['title'] ?? ''));
        $content = trim((string) ($postData['content'] ?? ''));
        $userId = (int) ($this->sessionUser['id'] ?? 0);

        if ($productId <= 0) $reviewErrors[] = 'Produkt nebol nájdený.';
        if ($userId <= 0) $reviewErrors[] = 'Na pridanie recenzie sa musíš prihlásiť.';
        if ($rating < 1 || $rating > 5) $reviewErrors[] = 'Hodnotenie musí byť od 1 do 5.';
        if (mb_strlen($title) < 3 || mb_strlen($title) > 150) $reviewErrors[] = 'Nadpis musí mať 3-150 znakov.';
        if (mb_strlen($content) < 5) $reviewErrors[] = 'Recenzia musí mať aspoň 5 znakov.';

        // 2. Ak sú chyby, ulož ich do session a ukonči
        if (!empty($reviewErrors)) {
            $_SESSION['product_review_errors'] = $reviewErrors;
            $_SESSION['product_review_data'] = $postData;
            header('Location: ' . route('/product?id=' . $productId . '#reviews'));
            exit;
        }

        // 3. Uloženie do databázy cez Model
        $saved = $this->reviewModel->saveReview(
            $productId,
            $userId,
            $rating,
            $title,
            $content,
            false, // autoApprove
            null,  // meta
            true   // isVerified
        );

        if ($saved) {
            $_SESSION['product_review_success'] = 'Ďakujeme za vašu recenziu!';
        } else {
            $_SESSION['product_review_errors'] = ['Recenziu sa nepodarilo uložiť.'];
        }

        header('Location: ' . route('/product?id=' . $productId . '#reviews'));
        exit;
    }
}