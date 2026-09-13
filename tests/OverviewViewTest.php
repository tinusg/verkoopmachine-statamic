<?php

namespace Tinusg\VerkoopmachineStatamic\Tests;

class OverviewViewTest extends TestCase
{
    public function test_it_displays_a_compact_dutch_overview(): void
    {
        $html = view('verkoopmachine-statamic::overview', [
            'available' => true,
            'error' => null,
            'client' => [
                'name' => 'Veehandel CSL',
            ],
            'groups' => [
                [
                    'key' => 'vee',
                    'label' => 'Vee',
                    'total' => 1,
                    'items' => [
                        [
                            'title' => '200 stuks Fleckvieh',
                            'summary' => '200 stuks Fleckvieh',
                            'image_url' => null,
                            'placeholder_image_url' => 'https://www.koemarkt.test/vee-ras/fleckvieh/koe',
                            'placeholder_overlay_url' => null,
                            'photo_count' => 3,
                            'url' => 'https://www.koemarkt.test/vee/fleckvieh',
                            'display' => [
                                'transaction' => 'Aangeboden',
                                'category' => 'Melkvee',
                                'animal_type' => 'Volwassen dier',
                                'breed' => 'Fleckvieh',
                                'region' => 'Drenthe',
                                'quantity' => 200,
                                'price' => ['label' => '€ 123,00'],
                            ],
                        ],
                    ],
                ],
                [
                    'key' => 'rundveeveilingen',
                    'label' => 'Rundveeveilingen',
                    'total' => 1,
                    'items' => [
                        [
                            'title' => 'Fleckviehveiling Munster',
                            'summary' => 'Fleckviehveiling Munster',
                            'image_url' => null,
                            'url' => 'https://www.koemarkt.test/rundveeveiling/munster',
                            'display' => [
                                'starts_on' => '2026-09-19',
                                'ends_on' => '2026-09-20',
                                'location' => 'Veehalle',
                                'city' => 'Munster',
                                'country' => 'Duitsland',
                            ],
                        ],
                    ],
                ],
            ],
        ])->render();

        $this->assertStringContainsString('1 advertentie', $html);
        $this->assertStringContainsString('vm-card--vee', $html);
        $this->assertStringContainsString('vm-card__media', $html);
        $this->assertStringContainsString('vm-card__image--placeholder', $html);
        $this->assertStringContainsString('https://www.koemarkt.test/vee-ras/fleckvieh/koe', $html);
        $this->assertStringContainsString('aria-label="3 foto&#039;s"', $html);
        $this->assertStringContainsString('200 stuks', $html);
        $this->assertStringContainsString('Veehandel CSL', $html);
        $this->assertStringContainsString('Melkvee · Fleckvieh', $html);
        $this->assertStringContainsString('€ 123,00', $html);
        $this->assertStringContainsString('1 veiling', $html);
        $this->assertStringContainsString('vm-card--auction', $html);
        $this->assertStringContainsString('datetime="2026-09-19"', $html);
        $this->assertStringContainsString('datetime="2026-09-20"', $html);
        $this->assertStringContainsString('Veehalle, Munster', $html);
        $this->assertStringContainsString('Duitsland', $html);

        $this->assertStringNotContainsString('Transaction', $html);
        $this->assertStringNotContainsString('Animal Type', $html);
        $this->assertStringNotContainsString('Volwassen dier', $html);
        $this->assertSame(1, substr_count($html, '200 stuks Fleckvieh'));
        $this->assertSame(1, substr_count($html, 'Fleckviehveiling Munster'));
    }
}
