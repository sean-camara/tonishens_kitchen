<?php

namespace App\Http\Controllers;

use App\Models\AboutHistory;
use App\Models\Contact;
use App\Models\Faq;
use App\Models\SocialLink;
use Illuminate\Http\JsonResponse;

class AboutController extends Controller
{
    public function index(): JsonResponse
    {
        return $this->success([
            'history'      => AboutHistory::latest()->first()?->content,
            'contacts'     => Contact::orderBy('sort_order')->get(),
            'social_links' => SocialLink::orderBy('sort_order')->get(),
            'faqs'         => Faq::orderBy('sort_order')->get(),
        ]);
    }
}
