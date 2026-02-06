<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutHistory;
use App\Models\Contact;
use App\Models\Faq;
use App\Models\SocialLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AboutManagementController extends Controller
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

    public function updateHistory(Request $request): JsonResponse
    {
        $request->validate(['content' => 'required|string|max:10000']);

        AboutHistory::updateOrCreate([], ['content' => $request->content]);

        return $this->success(null, 'History updated');
    }

    // Contacts
    public function storeContact(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type'  => 'required|string|max:50',
            'value' => 'required|string|max:255',
        ]);

        $contact = Contact::create($data);
        return $this->success($contact, 'Created', 201);
    }

    public function destroyContact(Contact $contact): JsonResponse
    {
        $contact->delete();
        return $this->success(null, 'Deleted');
    }

    // Social Links
    public function storeSocialLink(Request $request): JsonResponse
    {
        $data = $request->validate([
            'platform' => 'required|string|max:50',
            'url'      => 'required|url|max:500',
        ]);

        $link = SocialLink::create($data);
        return $this->success($link, 'Created', 201);
    }

    public function destroySocialLink(SocialLink $socialLink): JsonResponse
    {
        $socialLink->delete();
        return $this->success(null, 'Deleted');
    }

    // FAQs
    public function storeFaq(Request $request): JsonResponse
    {
        $data = $request->validate([
            'question' => 'required|string|max:500',
            'answer'   => 'required|string|max:5000',
        ]);

        $faq = Faq::create($data);
        return $this->success($faq, 'Created', 201);
    }

    public function destroyFaq(Faq $faq): JsonResponse
    {
        $faq->delete();
        return $this->success(null, 'Deleted');
    }
}
