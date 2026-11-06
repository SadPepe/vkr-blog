<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

/**
 * Статические юридические страницы сайта.
 *
 * Тексты — черновик под 152-ФЗ; реквизиты оператора берутся из config/legal.php.
 */
class LegalController extends Controller
{
    public function privacy(): Response
    {
        return Inertia::render('Legal/Privacy');
    }

    public function terms(): Response
    {
        return Inertia::render('Legal/Terms');
    }

    public function consent(): Response
    {
        return Inertia::render('Legal/Consent');
    }

    public function distributionConsent(): Response
    {
        return Inertia::render('Legal/DistributionConsent');
    }

    public function copyright(): Response
    {
        return Inertia::render('Legal/Copyright');
    }
}
