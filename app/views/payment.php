<?php
$bodyClass = 'payment-page';
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/app/core/session_helper.php';

SessionHelper::bootstrap();

$pageTitle = 'E-shop - Platba';
include __DIR__ . '/partials/header-shop.php';
?>

<main class="payment-page-shell">
    <section class="payment-layout">
        <aside class="payment-rail">
            <span class="payment-kicker">Krok 2 z 2</span>
            <h1>Dokonči objednávku</h1>
            <p>Tu si môžeš doplniť vlastnú logiku. Stránka je pripravená ako čistý template s prehľadným rozložením.</p>

            <div class="payment-stepper" aria-label="Kroky objednávky">
                <button type="button" class="payment-step is-done" data-step-target="address">
                    <span class="step-dot"></span>
                    <div>
                        <strong>Adresa</strong>
                        <small>Dodacie údaje</small>
                    </div>
                </button>
                <button type="button" class="payment-step is-active" data-step-target="payment" aria-current="step">
                    <span class="step-dot"></span>
                    <div>
                        <strong>Platba</strong>
                        <small>Výber spôsobu platby</small>
                    </div>
                </button>
                <button type="button" class="payment-step" data-step-target="review">
                    <span class="step-dot"></span>
                    <div>
                        <strong>Dokončenie</strong>
                        <small>Potvrdenie objednávky</small>
                    </div>
                </button>
            </div>
        </aside>

        <div class="payment-content">
        <form class="payment-form" action="<?php echo route('/api/checkout.php'); ?>" method="POST" novalidate>
            <input type="hidden" name="return_to" value="<?php echo route('/payment'); ?>">
            <article class="payment-stage is-visible" data-step-panel="address">
                <div class="payment-panel">
                <div class="panel-head">
                    <h2>Dodacie údaje</h2>
                    <p>Adresu si vieš upraviť podľa potreby.</p>
                </div>

                <div class="form-grid form-grid--two">
                    <label>
                        Meno a priezvisko
                        <input type="text" name="customer_name" placeholder="Zadaj meno">
                    </label>
                    <label>
                        Email
                        <input type="email" name="customer_email" placeholder="meno@domena.sk">
                    </label>
                </div>

                <div class="form-grid form-grid--two">
                    <label>
                        Telefón
                        <input type="tel" name="customer_phone" placeholder="+421 900 000 000">
                    </label>
                    <label>
                        Mesto
                        <input type="text" name="city" placeholder="Bratislava">
                    </label>
                </div>

                <div class="form-grid form-grid--two">
                    <label>
                        Ulica a číslo
                        <input type="text" name="street" placeholder="Hlavná 12">
                    </label>
                    <label>
                        PSČ
                        <input type="text" name="zip" placeholder="811 01">
                    </label>
                </div>
                <div class="payment-stage-actions">
                    <button type="button" class="stage-next-btn" data-step-next="payment">Pokračovať na platbu</button>
                </div>
                </div>
            </article>

            <article class="payment-stage" data-step-panel="payment">
                <div class="payment-panel">
                <div class="panel-head">
                    <h2>Spôsob platby</h2>
                    <p>Po výbere sa zobrazí príslušný formulár.</p>
                </div>

                <div class="payment-methods" role="radiogroup" aria-label="Spôsob platby">
                    <label class="payment-method-card is-active">
                        <input type="radio" name="payment_method" value="card" checked>
                        <span class="method-badge">Najrýchlejšie</span>
                        <strong>Kartou online</strong>
                        <small>Visa, Mastercard, Apple Pay / Google Pay</small>
                    </label>

                    <label class="payment-method-card">
                        <input type="radio" name="payment_method" value="cash">
                        <span class="method-badge">Pri doručení</span>
                        <strong>Dobierka</strong>
                        <small>Zaplatíš kuriérovi pri prevzatí zásielky.</small>
                    </label>

                    <label class="payment-method-card">
                        <input type="radio" name="payment_method" value="transfer">
                        <span class="method-badge">Pre firmy</span>
                        <strong>Bankový prevod</strong>
                        <small>Po objednávke dostaneš podklady na úhradu.</small>
                    </label>
                </div>

                <div class="payment-method-form is-visible" data-payment-panel="card">
                    <div class="payment-card-widget">
                        <div class="payment-card-visual">
                            <span class="card-glow card-glow--one"></span>
                            <span class="card-glow card-glow--two"></span>
                            <div class="card-chip"></div>
                            <div class="card-brand">RED GHOST PAY</div>
                            <div class="card-digits card-digits--ghost">•••• •••• •••• 4281</div>
                            <div class="card-form-overlay">
                                <label class="card-field card-field--number">
                                    Číslo karty
                                    <input type="text" name="card_number" inputmode="numeric" placeholder="1234 5678 9012 3456">
                                </label>

                                <div class="card-field-row">
                                    <label class="card-field card-field--expiry">
                                        Platnosť
                                        <input type="text" name="card_expiry" inputmode="numeric" placeholder="09/28">
                                    </label>

                                    <label class="card-field card-field--cvv">
                                        CVV
                                        <input type="password" name="card_cvv" inputmode="numeric" placeholder="123">
                                    </label>
                                </div>

                                <label class="card-field card-field--holder">
                                    Meno na karte
                                    <input type="text" name="card_name" placeholder="Ján Novák">
                                </label>
                            </div>
                        </div>

                        <label class="card-extra-field">
                            Kód pre overenie
                            <input type="text" name="card_otp" placeholder="SMS / app">
                        </label>
                    </div>
                </div>

                <div class="payment-method-form" data-payment-panel="cash">
                    <div class="payment-delivery-options" role="radiogroup" aria-label="Doručenie na dobierku">
                        <label class="delivery-option-card is-active">
                            <input type="radio" name="cash_delivery" value="alzabox" checked>
                            <strong>Alzabox</strong>
                            <small>Vyzdvihnutie v boxe podľa najbližšej dostupnosti.</small>
                        </label>

                        <label class="delivery-option-card">
                            <input type="radio" name="cash_delivery" value="courier">
                            <strong>Kuriér</strong>
                            <small>Doručenie priamo na adresu, ktorú doplníš nižšie.</small>
                        </label>

                        <label class="delivery-option-card">
                            <input type="radio" name="cash_delivery" value="post">
                            <strong>Na poštu</strong>
                            <small>Prevzatie na pošte alebo výdajnom mieste.</small>
                        </label>
                    </div>

                    <div class="payment-delivery-details is-visible" data-delivery-panel="alzabox">
                        <div class="form-grid form-grid--two">
                            <label>
                                Názov Alzaboxu
                                <input type="text" name="alzabox_name" placeholder="Alzabox Bratislava - Centrum">
                            </label>
                            <label>
                                Kód boxu
                                <input type="text" name="alzabox_code" placeholder="ABX-1024">
                            </label>
                        </div>
                    </div>

                    <div class="payment-delivery-details" data-delivery-panel="courier">
                        <div class="form-grid form-grid--two">
                            <label>
                                Ulica a číslo
                                <input type="text" name="courier_street" placeholder="Hlavná 12">
                            </label>
                            <label>
                                Mesto
                                <input type="text" name="courier_city" placeholder="Bratislava">
                            </label>
                        </div>
                        <div class="form-grid form-grid--two">
                            <label>
                                PSČ
                                <input type="text" name="courier_zip" placeholder="811 01">
                            </label>
                            <label>
                                Poznámka pre kuriéra
                                <input type="text" name="courier_note" placeholder="Zvonček 2. poschodie">
                            </label>
                        </div>
                    </div>

                    <div class="payment-delivery-details" data-delivery-panel="post">
                        <div class="form-grid form-grid--two">
                            <label>
                                Názov pošty / výdajného miesta
                                <input type="text" name="post_name" placeholder="Slovenská pošta - Hlavná">
                            </label>
                            <label>
                                Mesto
                                <input type="text" name="post_city" placeholder="Bratislava">
                            </label>
                        </div>
                        <div class="form-grid form-grid--two">
                            <label>
                                Ulica a číslo
                                <input type="text" name="post_street" placeholder="Poštová 1">
                            </label>
                            <label>
                                PSČ
                                <input type="text" name="post_zip" placeholder="811 01">
                            </label>
                        </div>
                    </div>

                    <p class="method-note">Dobierku si vieš neskôr napojiť na vlastnú logiku. Tieto polia sú zatiaľ len template.</p>
                </div>

                <div class="payment-method-form" data-payment-panel="transfer">
                    <div class="form-grid form-grid--two">
                        <label>
                            IBAN
                            <input type="text" name="iban" placeholder="SK12 0000 0000 0000 0000 0000">
                        </label>
                        <label>
                            Variabilný symbol
                            <input type="text" name="variable_symbol" placeholder="20260001">
                        </label>
                    </div>
                    <p class="method-note">Po zadaní údajov môžeš neskôr napojiť vlastnú serverovú logiku bez zásahu do dizajnu.</p>
                </div>

                <div class="payment-stage-actions payment-stage-actions--split">
                    <button type="button" class="stage-back-btn" data-step-prev="address">Späť</button>
                    <button type="button" class="stage-next-btn" data-step-next="review">Pokračovať na dokončenie</button>
                </div>
                </div>
            </article>

            <article class="payment-stage" data-step-panel="review">
                <div class="payment-panel">
                <div class="panel-head">
                    <h2>Kontrola objednávky</h2>
                    <p>Tu si môžeš doplniť finálne potvrdenie pred odoslaním.</p>
                </div>

                <div class="review-grid">
                    <div class="review-box">
                        <span>Krok</span>
                        <strong>3 / 3</strong>
                    </div>
                    <div class="review-box">
                        <span>Platba</span>
                        <strong>Kartou online</strong>
                    </div>
                    <div class="review-box review-box--wide">
                        <span>Poznámka</span>
                        <strong>Sem si doplníš vlastnú serverovú logiku alebo potvrdenie objednávky.</strong>
                    </div>
                </div>

                <div class="payment-stage-actions payment-stage-actions--split">
                    <button type="button" class="stage-back-btn" data-step-prev="payment">Späť</button>
                    <button type="submit" class="payment-submit-btn">Dokončiť objednávku</button>
                </div>
                </div>
            </article>
        </form>

        <aside class="payment-summary">
            <div class="summary-card">
                <div class="panel-head">
                    <h2>Súhrn objednávky</h2>
                    <p>Tu si môžeš doplniť vlastný prepočet objednávky.</p>
                </div>

                <div class="summary-items">
                    <article class="summary-item">
                        <img src="/assets/images/omacka3.webp" alt="Produkt">
                        <div>
                            <h3>Názov produktu</h3>
                            <p>1 x 0,00 EUR</p>
                        </div>
                    </article>
                    <div class="summary-empty">Toto je len vizuálny placeholder.</div>
                </div>

                <div class="summary-breakdown">
                    <div><span>Medzisúčet</span><strong>0,00 EUR</strong></div>
                    <div><span>Doprava</span><strong>0,00 EUR</strong></div>
                    <div><span>Zľava</span><strong>-0,00 EUR</strong></div>
                    <div class="is-total"><span>Spolu</span><strong>0,00 EUR</strong></div>
                </div>
            </div>
        </aside>
        </div>
    </section>
</main>

<script src="<?php echo asset('js/payment.js'); ?>"></script>

<?php include __DIR__ . '/partials/footer-shop.php'; ?>